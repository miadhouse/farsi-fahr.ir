<?php
require_once __DIR__ . '/../config/config.php';

function getQuestions($pdo, $cat2id=null)
{
  if ($cat2id !== null) {
    $pattern = "%," . $cat2id . ",%";

    $stmt = $pdo->prepare("
        SELECT *
        FROM questions
        WHERE category_id LIKE :pattern
          AND (
              classes IS NULL
              OR classes = ''
              OR classes LIKE '%,6,%'
          )
          AND NOT (basic = 0 AND basic_mofa = 1)
    ");

    $stmt->bindValue(':pattern', $pattern, PDO::PARAM_STR);
    $stmt->execute();

    $questions = $stmt->fetchAll();

    echo "<pre>";
    print_r($questions);
    echo "</pre>";
} else {
    echo "لطفاً category_id را وارد کنید.";
}

}

function getRootCategoryQuestions($pdo,$rootCatId=null){
   $stmt = $pdo->prepare("
        SELECT id FROM categories 
        WHERE parent_id = ?
    ");
    $stmt->execute($rootCatId);

    if ($stmt->rowCount() > 0) {
      foreach($stmt as $cat){
        
      }
    }
}

