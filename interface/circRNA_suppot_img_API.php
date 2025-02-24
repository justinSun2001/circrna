<?php
include 'conn.php';

if (isset($_GET['BloodCircR_ID'])) {
    $BloodCircR_ID = $_GET['BloodCircR_ID'];
    $Sample = isset($_GET['Sample']) ? $_GET['Sample'] : null;

    // 创建 SQL 查询语句
    if ($Sample) {
        $sql = "SELECT DatasetID, Dataset, path,Sample FROM support WHERE BloodCircR_ID = ? AND Sample = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $BloodCircR_ID, $Sample);
    } else {
        $sql = "SELECT DatasetID, Dataset, path,Sample FROM support WHERE BloodCircR_ID = ? LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $BloodCircR_ID);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $DatasetID = $row['DatasetID']; 
        $Sample = $row['Sample']; // 更新 Sample 的值
        $path = $row['Dataset'].'/'.$row['path'];
        echo json_encode(['DatasetID' => $DatasetID, 'Sample' => $Sample, 'path' => $path]);
    } else {
        echo "No data found for BloodCircR_ID: $BloodCircR_ID and Sample: $Sample";
    }

    $stmt->close();
} else {
    echo "BloodCircR_ID parameter is missing";
}

$conn->close();
?>
