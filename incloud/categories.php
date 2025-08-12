<?php
require_once __DIR__ . '/../config/config.php';

// Function to get categories by type and parent
function getCategories($pdo, $category_type, $parent_id = null) {
$sql = "SELECT * FROM categories 
        WHERE category_type = ? AND parent_id ";
$params = [$category_type];

if ($parent_id === null) {
    $sql .= "IS NULL";
} else {
    $sql .= "= ?";
    $params[] = $parent_id;
}

$sql .= " ORDER BY 
    CAST(SUBSTRING_INDEX(index_code, '.', 1) AS UNSIGNED),
    CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(index_code, '.', 2), '.', -1) AS UNSIGNED),
    CAST(SUBSTRING_INDEX(index_code, '.', -1) AS UNSIGNED)";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Function to get subcategories by parent ID
function getSubcategories($pdo, $parent_id) {
    $sql = "SELECT * FROM categories 
            WHERE parent_id = ? 
            ORDER BY 
                CAST(SUBSTRING_INDEX(index_code, '.', 1) AS UNSIGNED),
                CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(index_code, '.', 2), '.', -1) AS UNSIGNED),
                CAST(SUBSTRING_INDEX(index_code, '.', -1) AS UNSIGNED)";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$parent_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


// دسته‌بندی داده‌ها به صورت درختی
