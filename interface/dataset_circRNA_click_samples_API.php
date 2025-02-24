<?php
include 'conn.php';

if (isset($_GET['Dataset']) && isset($_GET['DetectCount']) && isset($_GET['BloodCircR_ID'])) {
    $Dataset = $_GET['Dataset'];
    $DetectCount = $_GET['DetectCount'];
    $BloodCircR_ID = $_GET['BloodCircR_ID'];

    // 创建 SQL 查询语句
    $sql = "SELECT * FROM dataset_detect WHERE Dataset = ? AND DetectCount = ? AND BloodCircR_ID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $Dataset, $DetectCount, $BloodCircR_ID); // 使用 "sss" 以允许字符串和数字
    $stmt->execute();
    $result = $stmt->get_result();

    $data = array();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }

        // 返回数据数组为 JSON
        echo json_encode($data);
    } else {
        echo "No data found for Dataset: $Dataset and DetectCount: $DetectCount and BloodCircR_ID: $BloodCircR_ID";
    }

    $stmt->close();
} else {
    echo "Dataset, DetectCount, or BloodCircR_ID parameter is missing";
}

?>
