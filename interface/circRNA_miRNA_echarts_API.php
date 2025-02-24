<?php
include 'conn.php';

if (isset($_GET['BloodCircR_ID']) && isset($_GET['choose'])) {
    $BloodCircR_ID = $_GET['BloodCircR_ID'];
    $choose = $_GET['choose'];

    // 查询符合条件的行，选择所有列
    $sql = "SELECT * FROM mirna_info WHERE BloodCircR_ID = ? AND site_type = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $BloodCircR_ID, $choose);
    $stmt->execute();
    $result = $stmt->get_result();

    $data[] = array("name" => $BloodCircR_ID, "symbolSize" => 80);
    $links = array();

    // 用于存储已经添加的名字
    $uniqueNames = array();

    // 为每个 miRNA 创建一个节点
    while ($row = $result->fetch_assoc()) {
        $miRNA = $row['miRNA'];

        // 检查是否已经添加过相同的名字
        if (!in_array($miRNA, $uniqueNames)) {
            $uniqueNames[] = $miRNA;

            // 添加节点
            $data[] = array("name" => $miRNA, "symbolSize" => $symbolSize);

            // 创建链接
            $links[] = array("source" => $BloodCircR_ID, "target" => $miRNA);
        }
    }

    $response = array(
        'data' => $data,
        'links' => $links
    );

    // 返回数据数组为 JSON
    echo json_encode($response);

    $stmt->close();
} else {
    echo "BloodCircR_ID or choose parameter is missing";
}

$conn->close();
?>
