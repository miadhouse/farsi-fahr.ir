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
if (!isset($_POST['question_id']) || !isset($_POST['is_correct']) || !isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'پارامترهای لازم ارسال نشده‌اند']);
    exit;
}

$questionId = (int) $_POST['question_id'];
$isCorrect = (bool) $_POST['is_correct'];
$userId = $_SESSION['user_id'];

try {
    // بررسی وجود رکورد قبلی
    $stmt = $pdo->prepare("SELECT correct, incorrect FROM user_question_stats WHERE user_id = ? AND question_id = ?");
    $stmt->execute([$userId, $questionId]);
    $existing = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existing) {
        // آپدیت رکورد موجود
        $currentCorrect = (int) $existing['correct'];
        $currentIncorrect = (int) $existing['incorrect'];

        if ($isCorrect) {
            // پاسخ صحیح
            $newCorrect = min(2, $currentCorrect + 1); // حداکثر 2
            $newIncorrect = max(0, $currentIncorrect - 1); // حداقل 0
        } else {
            // پاسخ نادرست
            $newCorrect = max(0, $currentCorrect - 1); // حداقل 0
            $newIncorrect = min(2, $currentIncorrect + 1); // حداکثر 2
        }

        $stmt = $pdo->prepare("UPDATE user_question_stats SET correct = ?, incorrect = ? WHERE user_id = ? AND question_id = ?");
        $stmt->execute([$newCorrect, $newIncorrect, $userId, $questionId]);

    } else {
        // ایجاد رکورد جدید
        if ($isCorrect) {
            $newCorrect = 1;
            $newIncorrect = 0;
        } else {
            $newCorrect = 0;
            $newIncorrect = 1;
        }

        $stmt = $pdo->prepare("INSERT INTO user_question_stats (user_id, question_id, correct, incorrect) VALUES (?, ?, ?, ?)");
        $stmt->execute([$userId, $questionId, $newCorrect, $newIncorrect]);
    }

    // بازگردانی وضعیت جدید
    $stmt = $pdo->prepare("SELECT correct, incorrect FROM user_question_stats WHERE user_id = ? AND question_id = ?");
    $stmt->execute([$userId, $questionId]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    // تعیین رنگ بر اساس وضعیت
    $color = getQuestionColor($result['correct'], $result['incorrect']);

    echo json_encode([
        'success' => true,
        'correct' => (int) $result['correct'],
        'incorrect' => (int) $result['incorrect'],
        'color' => $color
    ]);

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