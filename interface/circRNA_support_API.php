<?php
include_once('conn.php');

$page = isset($_GET['page']) ? $_GET['page'] : 1;
$limit = isset($_GET['limit']) ? $_GET['limit'] : 10;

$offset = ($page - 1) * $limit;


$BloodCircRID = isset($_GET['BloodCircR_ID']) && $_GET['BloodCircR_ID'] != '' ? $_GET['BloodCircR_ID'] : null;
$id = isset($_GET['DatasetID']) && $_GET['DatasetID'] != '' ? $_GET['DatasetID'] : null;

$sortField = isset($_GET['field'])&& $_GET['field'] != '' ? $_GET['field'] : null; // 默认排序字段
$sortOrder = isset($_GET['order'])&& $_GET['order'] != '' ? $_GET['order'] : null; // 默认排序顺序

if ($BloodCircRID !== null) {
    $sqlData = "SELECT * FROM `support` WHERE `BloodCircR_ID` = '$BloodCircRID' ";
    $sqlCount = "SELECT COUNT(*) AS total FROM `support` WHERE `BloodCircR_ID` = '$BloodCircRID'";
} else {
    $sqlData = "SELECT * FROM `support` ";
    $sqlCount = "SELECT COUNT(*) AS total FROM `support` ";
}

// Add Dataset filter if provided
if ($id !== null) {
    $sqlData .= "AND `DatasetID` = '$id' ";
    $sqlCount .= "AND `DatasetID` = '$id' ";
}

$sqlData .= "ORDER BY `$sortField` $sortOrder LIMIT $limit OFFSET $offset";
$resultData = $conn->query($sqlData);
$resultCount = $conn->query($sqlCount);

//去重后的 Dataset 数组
$distinctIDSql = "SELECT DISTINCT `DatasetID` FROM `support` WHERE `BloodCircR_ID` = '$BloodCircRID'";
$distinctIDResult = $conn->query($distinctIDSql);

$distinctIDs = [];
if ($distinctIDResult->num_rows > 0) {
    while ($row = $distinctIDResult->fetch_assoc()) {
        $distinctIDs[] = $row['DatasetID'];
    }
}

$response = [
    "code" => 0,
    "msg" => "",
    "count" => 0,
    "data" => [],
    "distinctIDs" => $distinctIDs,
    'DatasetID' => $id

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
