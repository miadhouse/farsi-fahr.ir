<?php
require_once __DIR__ . '/../../incloud/questions.php';
if (isset($_GET['subcategory_id']) && $_GET['subcategory_id'] !=null) {
$questions = getQuestions($pdo, $cat2id =$_GET['subcategory_id']);

} elseif (isset($_GET['category_id'])) {
        $questions = getRootCategoryQuestions($pdo, $cat2id = $_GET['category_id']);
} else {
    $error = 1;
}
?>
<h1><?= count($questions) ?></h1>
<?php foreach ($questions as $question): ?>
    <div class="form-check form-check-primary mt-3">
        <input class="form-check-input" type="checkbox" value="" id="customCheckPrimary<?=$question['id']?>" checked>
        <label class="form-check-label" for="customCheckPrimary<?=$question['id']?>"><?=$question['text']?></label>
    </div>
<?php endforeach; ?>