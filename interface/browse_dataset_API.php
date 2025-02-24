<?php
include_once('conn.php');

$page = isset($_GET['page']) ? $_GET['page'] : 1;
$limit = isset($_GET['limit']) ? $_GET['limit'] : 10;

$offset = ($page - 1) * $limit;

$searchColumn = isset($_GET['search_column'])&& $_GET['search_column'] != '' ? $_GET['search_column'] : null;
$searchValue = isset($_GET['search_value'])&& $_GET['search_column'] != '' ? $_GET['search_value'] : null;

$sortField = isset($_GET['field'])? $_GET['field'] : null; // 默认排序字段
$sortOrder = isset($_GET['order'])? $_GET['order'] : null; // 默认排序顺序

if ($searchColumn !== null && $searchValue !== null && $sortField !== null && $sortOrder !== null) {
    $sqlData = "SELECT * FROM `dataset_info` WHERE `$searchColumn` = '$searchValue' ORDER BY `$sortField` $sortOrder LIMIT $limit OFFSET $offset";
    $sqlCount = "SELECT COUNT(*) AS total FROM `dataset_info` WHERE `$searchColumn` = '$searchValue'";
} else if($searchColumn !== null && $searchValue !== null){
    $sqlData = "SELECT * FROM `dataset_info` WHERE `$searchColumn` = '$searchValue' LIMIT $limit OFFSET $offset";
    $sqlCount = "SELECT COUNT(*) AS total FROM `dataset_info` WHERE `$searchColumn` = '$searchValue'";
}else if($sortField !== null && $sortOrder !== null){
    $sqlData = "SELECT * FROM `dataset_info` ORDER BY `$sortField` $sortOrder LIMIT $limit OFFSET $offset";
    $sqlCount = "SELECT COUNT(*) AS total FROM `dataset_info`";
}else{
    $sqlData = "SELECT * FROM `dataset_info` LIMIT $limit OFFSET $offset";
    $sqlCount = "SELECT COUNT(*) AS total FROM `dataset_info`";
}


$resultData = $conn->query($sqlData);
$resultCount = $conn->query($sqlCount);

$response = [
    "code" => 0,
    "msg" => "",
    "count" => 0,
    "data" => [],
];

if ($resultCount->num_rows > 0) {
    $row = $resultCount->fetch_assoc();
    $response["count"] = $row["total"];
}

if ($resultData->num_rows > 0) {
    while ($row = $resultData->fetch_assoc()) {
        $response["data"][] = $row;
    }
}

$conn->close();

header("Content-type: application/json");
echo json_encode($response);
?>
