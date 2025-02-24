<?php
include 'conn.php';

// 检查是否收到 'BloodCircR_ID' 参数
if (isset($_GET['BloodCircR_ID'])) {
    $BloodCircR_ID = $_GET['BloodCircR_ID'];

    $sql = "SELECT dataset, detected_ratio, undetected_ratio FROM detected_info WHERE BloodCircR_ID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $BloodCircR_ID);
    $stmt->execute();
    $result = $stmt->get_result();

    $datasets = array();
    $detected_ratios = array();
    $undetected_ratios = array();

    while ($row = $result->fetch_assoc()) {
        $datasets[] = $row['dataset'];
        $detected_ratios[] = $row['detected_ratio'];
        $undetected_ratios[] = $row['undetected_ratio'];
    }

    $response = array(
        "datasets" => $datasets,
        "detected_ratios" => $detected_ratios,
        "undetected_ratios" => $undetected_ratios
    );

    echo json_encode($response);
    
    $stmt->close();
} else {
    echo "BloodCircR_ID parameter is missing";
}

$conn->close();
?>
