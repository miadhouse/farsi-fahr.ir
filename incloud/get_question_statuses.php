<?php
require_once __DIR__ . '/../config/config.php';


header('Content-Type: application/json');
// بررسی CSRF token
if (!isset($_POST['csrf_token']) || empty($_POST['csrf_token'])) {
    echo json_encode([
        'success' => false,
        'message' => 'توکن امنیتی ارسال نشده است'
    ]);
    exit;
}
if (!isset($_POST['question_ids']) || !isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'پارامترهای لازم ارسال نشده‌اند']);
    exit;
}

$questionIds = json_decode($_POST['question_ids']);
$userId = $_SESSION['user_id'];

if (!is_array($questionIds) || empty($questionIds)) {
    echo json_encode(['success' => false, 'error' => 'لیست سوالات معتبر نیست']);
    exit;
}

try {
    // ایجاد placeholder برای IN clause
    $placeholders = str_repeat('?,', count($questionIds) - 1) . '?';
    $params = array_merge([$userId], $questionIds);

    $stmt = $pdo->prepare("
        SELECT question_id, correct, incorrect 
        FROM user_question_stats 
        WHERE user_id = ? AND question_id IN ($placeholders)
    ");
    $stmt->execute($params);

    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // تبدیل به آرایه associative با question_id به عنوان key
    $statuses = [];
    foreach ($results as $row) {
        $color = getQuestionColor($row['correct'], $row['incorrect']);
        $statuses[$row['question_id']] = [
            'correct' => (int) $row['correct'],
            'incorrect' => (int) $row['incorrect'],
            'color' => $color
        ];
    }

    // برای سوالاتی که رکورد ندارند، مقدار پیش‌فرض تعیین می‌کنیم
    foreach ($questionIds as $questionId) {
        if (!isset($statuses[$questionId])) {
            $statuses[$questionId] = [
                'correct' => 0,
                'incorrect' => 0,
                'color' => 'gray'
            ];
        }
    }

    echo json_encode(['success' => true, 'data' => $statuses]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'خطا در دیتابیس: ' . $e->getMessage()]);
}

function getQuestionColor($correct, $incorrect)
{
    $correct = (int) $correct;
    $incorrect = (int) $incorrect;

    if ($correct == 0 && $incorrect == 0) {
        return 'gray'; // بی رنگ - پاسخ نداده
    } elseif ($correct == 2 && $incorrect == 0) {
        return 'green'; // دو بار صحیح
    } elseif ($correct == 1 && $incorrect == 0) {
        return 'blue'; // یک بار صحیح
    } elseif ($correct == 1 && $incorrect == 1) {
        return 'yellow'; // یک بار صحیح، یک بار غلط
    } elseif ($correct == 0 && $incorrect == 2) {
        return 'red'; // دو بار غلط
    } else {
        // سایر حالات
        if ($correct > $incorrect) {
            return 'blue';
        } elseif ($incorrect > $correct) {
            return 'red';
        } else {
            return 'yellow';
        }
    }
}
?>