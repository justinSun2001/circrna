<?php
include_once('conn.php');

$page = isset($_GET['page']) ? $_GET['page'] : 1;
$limit = isset($_GET['limit']) ? $_GET['limit'] : 10;
$offset = ($page - 1) * $limit;

$searchColumn = isset($_GET['search_column']) && $_GET['search_column'] != '' ? $_GET['search_column'] : null;
$searchValue = isset($_GET['search_value']) && $_GET['search_value'] != '' ? $_GET['search_value'] : null;

$sortField = isset($_GET['field']) ? $_GET['field'] : null;
$sortOrder = isset($_GET['order']) ? $_GET['order'] : null;

$dataset = isset($_GET['Dataset']) ? $_GET['Dataset'] : null;

$response = [
    "code" => 0,
    "msg" => "",
    "count" => 0,
    "data" => [],
];

if ($dataset !== null) {
    // 获取 new_table 表中相应的 Dataset 的所有数据
    $sqlData = "SELECT * FROM `new_table` WHERE `Dataset` = '$dataset'";
    if ($searchColumn !== null && $searchValue !== null) {
        $sqlData .= " AND `$searchColumn` = '$searchValue'";
    }
    if ($sortField !== null && $sortOrder !== null) {
        $sqlData .= " ORDER BY `$sortField` $sortOrder";
    }
    $sqlData .= " LIMIT $limit OFFSET $offset";

    $sqlCount = "SELECT COUNT(*) AS total FROM `new_table` WHERE `Dataset` = '$dataset'";
    if ($searchColumn !== null && $searchValue !== null) {
        $sqlCount .= " AND `$searchColumn` = '$searchValue'";
    }

    $resultData = $conn->query($sqlData);
    $resultCount = $conn->query($sqlCount);

    if ($resultCount->num_rows > 0) {
        $row = $resultCount->fetch_assoc();
        $response["count"] = $row["total"];
    }

    if ($resultData->num_rows > 0) {
        while ($row = $resultData->fetch_assoc()) {
            $response["data"][] = $row;
        }
    }
}

$conn->close();

header("Content-type: application/json");
echo json_encode($response);
?>
