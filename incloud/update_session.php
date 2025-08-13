<?php
require_once(__DIR__ . '/../config/config.php');

if (isset($_POST['current_question_id'])) {
    $_SESSION['current_question_id'] = $_POST['current_question_id'];
    echo json_encode(['status' => 'success', 'message' => 'Session updated successfully']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Question ID not provided']);
}
?>