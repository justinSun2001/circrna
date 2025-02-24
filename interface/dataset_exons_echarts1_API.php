<?php
include 'conn.php';

if (isset($_GET['Dataset'])) {
    $Dataset = $_GET['Dataset'];

    // 创建 SQL 查询语句
    $sql = "SELECT exons_num, COUNT(DISTINCT BloodCircleR_ID) as unique_count
            FROM exon_and_express
            WHERE Dataset = ?
            GROUP BY exons_num";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $Dataset);
    $stmt->execute();
    $result = $stmt->get_result();

    $xAxis = array();
    $yAxis = array();

    while ($row = $result->fetch_assoc()) {
        $xAxis[] = $row['exons_num'];
        $yAxis[] = $row['unique_count'];
    }

    $response = array(
        'xAxis' => $xAxis,
        'yAxis' => $yAxis
    );

    // 返回数据数组为 JSON
    echo json_encode($response);
    
    $stmt->close();
} else {
    echo "Dataset parameter is missing";
}

$conn->close();
?>
