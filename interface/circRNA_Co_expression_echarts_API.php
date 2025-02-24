<?php
include 'conn.php';

if (isset($_GET['BloodCircR_ID'])) {
    $BloodCircR_ID = $_GET['BloodCircR_ID'];

    $sql = "SELECT BloodCircR_ID,gene,corr FROM network WHERE BloodCircR_ID = ?";


    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $BloodCircR_ID);
    $stmt->execute();
    $result = $stmt->get_result();

    $data[] = array("name" => $BloodCircR_ID ,"symbolSize" => 80);
    $links = array();

    // 为每个 gene 创建一个节点
    while ($row = $result->fetch_assoc()) {
        $gene = $row['gene'];

        $columnValue = floatval($row['corr']);
        $symbolSize = 50;
		$itemStyle = array("color" => "rgba(30 , ". 150 * $columnValue * $columnValue . ", ". 255 * $columnValue * $columnValue . ", 0.8)");
        $data[] = array("name" => $gene, "symbolSize" => $symbolSize, "itemStyle" => $itemStyle);

        // 创建链接
        $links[] = array("source" => $BloodCircR_ID, "target" => $gene);
    }

    $response = array(
        'data' => $data,
        'links' => $links
    );

    // 返回数据数组为 JSON
    echo json_encode($response);
    
    $stmt->close();
} else {
    echo "BloodCircR_ID parameter is missing";
}

$conn->close();
?>
