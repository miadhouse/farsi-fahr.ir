<?php
require_once __DIR__ . '/questions.php';
require_once __DIR__ . '/../config/config.php';

header('Content-Type: application/json; charset=utf-8');

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'فقط درخواست POST مجاز است'
    ]);
    exit;
}

if (!isset($_POST['question_id']) || empty($_POST['question_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'شناسه سوال ارسال نشده است'
    ]);
    exit;
}

$questionId = intval($_POST['question_id']);

try {
 
    $stmt = $pdo->prepare("
        SELECT 
           *
        FROM questions 
        WHERE id = :question_id limit 10
    ");

    $stmt->bindValue(':question_id', (int) $questionId, PDO::PARAM_INT);
    $stmt->execute();

    $question = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$question) {
        echo json_encode([
            'success' => false,
            'message' => $questionId
        ]);
        exit;
    }

    if (!empty($question['image'])) {
        $question['image'] = 'assets/images/' . $question['image'];
    }

    if (!empty($question['video'])) {

        $question['video'] = 'assets/videos/' . $question['video'];
    }



    echo json_encode([
        'success' => true,
        'question' => $question,
        'message' => 'سوال با موفقیت بارگذاری شد'
    ]);

} catch (PDOException $e) {
    error_log("Database error in get_question.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} catch (Exception $e) {
    error_log("General error in get_question.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'خطای سیستمی رخ داده است'
    ]);
}
?>