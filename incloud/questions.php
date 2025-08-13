<?php
//incloud/questions.php
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

    $questions = $stmt->fetchAll(PDO::FETCH_ASSOC);

return $questions;
} else {
    echo "لطفاً category_id را وارد کنید.";
}

}

function getRootCategoryQuestions($pdo, $rootCatId = null) {
    $stmt = $pdo->prepare("
        SELECT id FROM categories 
        WHERE parent_id = :rootCatId
    ");
    $stmt->bindValue(':rootCatId', $rootCatId, PDO::PARAM_INT);
    $stmt->execute();

    $categoryIds = $stmt->fetchAll(PDO::FETCH_COLUMN);
    $questionArr = [];

    if (count($categoryIds) > 0) {
        foreach ($categoryIds as $catId) {
            $pattern = "%," . $catId . ",%";

            $stmt2 = $pdo->prepare("
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

            $stmt2->bindValue(':pattern', $pattern, PDO::PARAM_STR);
            $stmt2->execute();

            // Append all questions from this category to the array
            $results = $stmt2->fetchAll();
            if ($results) {
                // Merge arrays instead of nesting
                $questionArr = array_merge($questionArr, $results);
            }
        }
    }
    return $questionArr;
}
