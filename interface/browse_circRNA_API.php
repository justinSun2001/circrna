<?php
include_once('conn.php');

$page = isset($_GET['page']) ? $_GET['page'] : 1;
$limit = isset($_GET['limit']) ? $_GET['limit'] : 10;

$offset = ($page - 1) * $limit;

$searchColumn = isset($_GET['search_column']) && $_GET['search_column'] != '' ? $_GET['search_column'] : null;
$searchValue = isset($_GET['search_value']) && $_GET['search_value'] != '' ? $_GET['search_value'] : null;

$sortField = isset($_GET['field']) ? $_GET['field'] : null; // 默认排序字段
$sortOrder = isset($_GET['order']) ? $_GET['order'] : null; // 默认排序顺序

// // 如果有搜索条件和排序条件
// if ($searchColumn !== null && $searchValue !== null && $sortField !== null && $sortOrder !== null) {
//     $sqlData = "SELECT * FROM `circRNA_info` WHERE `$searchColumn` LIKE '%$searchValue%' ORDER BY `$sortField` $sortOrder LIMIT $limit OFFSET $offset";
//     $sqlCount = "SELECT COUNT(*) AS total FROM `circRNA_info` WHERE `$searchColumn` LIKE '%$searchValue%'";
// } 
// // 如果只有搜索条件
// else if ($searchColumn !== null && $searchValue !== null) {
//     $sqlData = "SELECT * FROM `circRNA_info` WHERE `$searchColumn` LIKE '%$searchValue%' LIMIT $limit OFFSET $offset";
//     $sqlCount = "SELECT COUNT(*) AS total FROM `circRNA_info` WHERE `$searchColumn` LIKE '%$searchValue%'";
// }
// // 如果只有排序条件
// else if ($sortField !== null && $sortOrder !== null) {
//     $sqlData = "SELECT * FROM `circRNA_info` ORDER BY `$sortField` $sortOrder LIMIT $limit OFFSET $offset";
//     $sqlCount = "SELECT COUNT(*) AS total FROM `circRNA_info`";
// }
// // 如果没有搜索和排序条件
// else {
//     $sqlData = "SELECT * FROM `circRNA_info` LIMIT $limit OFFSET $offset";
//     $sqlCount = "SELECT COUNT(*) AS total FROM `circRNA_info`";
// }

// 构建排序部分的 SQL
$sortSql = '';
if ($sortField!== null && $sortOrder!== null) {
    $sortSql = "ORDER BY `$sortField` $sortOrder";
}

// 构建搜索条件部分的 SQL
$searchSql = '';
if ($searchColumn!== null && $searchValue!== null) {
    $searchSql = "WHERE `$searchColumn` = '$searchValue'";
}

// 组合 SQL 查询语句
$sqlData = "SELECT * FROM `circRNA_info` $searchSql $sortSql LIMIT $limit OFFSET $offset";
$sqlCount = "SELECT COUNT(*) AS total FROM `circRNA_info` $searchSql";

$resultData = $conn->query($sqlData);
$resultCount = $conn->query($sqlCount);

$response = [
    "code" => 0,
    "msg" => "",
    "count" => 0,
    "data" => [],
    "msg"  => "Success"
];

if ($resultCount->num_rows > 0) {
    $row = $resultCount->fetch_assoc();
    $response["count"] = $row["total"];
} else {
    $response["code"] = 1;
    $response["msg"] = "Error: Failed to fetch count data.";
}

if ($resultData->num_rows > 0) {
    while ($row = $resultData->fetch_assoc()) {
        $response["data"][] = $row;
    }
} else {
    $response["code"] = 1;
    $response["msg"] = "Error: Failed to fetch data.";
}

$conn->close();

header("Content-type: application/json");
echo json_encode($response);
?>
