<?php
require_once __DIR__ . '/../config/config.php';

header('Content-Type: application/json; charset=utf-8');
// بررسی CSRF token
if (!isset($_POST['csrf_token']) || empty($_POST['csrf_token'])) {
    echo json_encode([
        'success' => false,
        'message' => 'توکن امنیتی ارسال نشده است'
    ]);
    exit;
}
// بررسی ورود کاربر
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'کاربر وارد نشده است']);
    exit;
}

$user_id = $_SESSION['user_id'];

// بررسی پارامترهای ورودی
if (
    !isset($_POST['word']) || !isset($_POST['translation']) ||
    empty(trim($_POST['word'])) || empty(trim($_POST['translation']))
) {
    echo json_encode(['success' => false, 'error' => 'کلمه و ترجمه الزامی است']);
    exit;
}

$word = trim($_POST['word']);
$translation = trim($_POST['translation']);
$question_id = isset($_POST['question_id']) && !empty($_POST['question_id']) ? (int) $_POST['question_id'] : null;
$category_id = isset($_POST['category_id']) && !empty($_POST['category_id']) ? (int) $_POST['category_id'] : null;

// بررسی اینکه فقط یک کلمه باشد
$words = explode(' ', $word);
if (count($words) > 1) {
    echo json_encode(['success' => false, 'error' => 'فقط یک کلمه قابل ذخیره است']);
    exit;
}

// بررسی طول کلمه
if (strlen($word) < 2 || strlen($word) > 50) {
    echo json_encode(['success' => false, 'error' => 'طول کلمه باید بین 2 تا 50 کاراکتر باشد']);
    exit;
}

// بررسی اینکه کلمه شامل حروف باشد
if (!preg_match('/[a-zA-ZäöüßÄÖÜ]/', $word)) {
    echo json_encode(['success' => false, 'error' => 'کلمه باید شامل حروف باشد']);
    exit;
}

try {
    $pdo->beginTransaction();

    // مرحله 1: پیدا کردن word_id از جدول vocabulary_words
    // چون کلمه الان باید از قبل در جدول vocabulary_words وجود داشته باشد
    $stmt = $pdo->prepare("SELECT id FROM vocabulary_words WHERE word = ? AND translation = ?");
    $stmt->execute([$word, $translation]);
    $word_row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$word_row) {
        // اگر کلمه در جدول vocabulary_words وجود نداشت، آن را اضافه کن
        // (این حالت نباید اتفاق بیفتد اگر سیستم درست کار کند)
        $stmt = $pdo->prepare("INSERT INTO vocabulary_words (word, translation) VALUES (?, ?)");
        $stmt->execute([$word, $translation]);
        $word_id = $pdo->lastInsertId();
    } else {
        $word_id = $word_row['id'];
    }

    // مرحله 2: بررسی اینکه آیا این کلمه در کلکشن کاربر موجود است یا نه
    $stmt = $pdo->prepare("
        SELECT id FROM user_vocabulary 
        WHERE user_id = ? AND word_id = ? AND (question_id = ? OR question_id IS NULL)
    ");
    $stmt->execute([$user_id, $word_id, $question_id]);
    $user_vocab_row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user_vocab_row) {
        // این کلمه قبلاً در کلکشن کاربر وجود دارد
        echo json_encode(['success' => false, 'error' => 'این کلمه در کلکشن واژگان شما موجود است']);
        $pdo->rollBack();
        exit;
    }

    // مرحله 3: افزودن کلمه به کلکشن واژگان کاربر
    $stmt = $pdo->prepare("
        INSERT INTO user_vocabulary (word_id, user_id, question_id, category_id) 
        VALUES (?, ?, ?, ?)
    ");
    $stmt->execute([$word_id, $user_id, $question_id, $category_id]);

    $pdo->commit();

    echo json_encode([
        'success' => true,
        'message' => 'کلمه به کلکشن واژگان شما افزوده شد',
        'word_id' => $word_id,
        'user_vocab_id' => $pdo->lastInsertId()
    ]);

} catch (PDOException $e) {
    $pdo->rollBack();
    error_log("Database error in save_vocabulary.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'خطا در اضافه کردن کلمه به کلکشن']);
}
?>