<?php
require_once __DIR__ . '/../../incloud/questions.php';
if ($_GET['subcategory_id']) {
    $error = 0;
    $subcategory_id = $_GET['subcategory_id'];
} elseif ($_GET['category_id']) {
    $error = 0;
    $category_id = $_GET['category_id'];
} else {
    $error = 1;
}
if ($error === 0) {
    if($subcategory_id){
$questions = getQuestions($pdo, $cat2id = $_GET['id'], $cat3Id = null);
    }else if($category_id){
        $questions = getQuestions($pdo, $cat2id = $_GET['id'], $cat3Id = null);

    }
}
?>
<?php foreach ($questions as $question): ?>
    <div class="form-check form-check-primary mt-3">
        <input class="form-check-input" type="checkbox" value="" id="customCheckPrimary" checked>
        <label class="form-check-label" for="customCheckPrimary">اولیه</label>
    </div>
<?php endforeach; ?>