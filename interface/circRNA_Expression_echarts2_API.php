<?php
include 'conn.php';

// 检查是否收到 'BloodCircR_ID' 和 'Dataset' 参数
if (isset($_GET['BloodCircR_ID']) && isset($_GET['Dataset'])) {
    $BloodCircR_ID = $_GET['BloodCircR_ID'];
    $Dataset = $_GET['Dataset'];

    // 查询指定 BloodCircR_ID 和 Dataset 对应的 fever_exp 和 control_exp 列
    $sql = "SELECT fever_exp, control_exp FROM express_info WHERE BloodCircR_ID = ? AND Dataset = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $BloodCircR_ID, $Dataset);
    $stmt->execute();
    $result = $stmt->get_result();

    $fever_exp = array();
    $control_exp = array();

    while ($row = $result->fetch_assoc()) {
        $fever_exp[] = floatval($row['fever_exp']);
        $control_exp[] = floatval($row['control_exp']);
    }

    $response = array(
        "data" => array($control_exp,$fever_exp)
    );

    echo json_encode($response);

    $stmt->close();
} else {
    echo "BloodCircR_ID or Dataset parameter is missing";
}

$conn->close();
?>
