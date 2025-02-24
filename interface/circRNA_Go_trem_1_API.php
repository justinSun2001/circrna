<?php
include 'conn.php';

if (isset($_GET['BloodCircR_ID'])) {
    $BloodCircR_ID = $_GET['BloodCircR_ID'];
    $choose = $_GET['choose'];

    // 创建 SQL 查询语句
    $sql = "SELECT * FROM ".$choose." WHERE BloodCircR_ID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $BloodCircR_ID);
    $stmt->execute();
    $result = $stmt->get_result();

    $data = array();
    $tooltipData = array();

    while ($row = $result->fetch_assoc()) {
        $Ydata[] = $row['Term'];
        $Sdata[] = $row['Combined Score'];

        // 添加 tooltip 数据
        $tooltipData[] = array(
            'Term' => $row['Term'],
            'P_value' => $row['P-value'],
            'Adjusted_P_value' => $row['Adjusted P-value'],
            'Odds_Ratio' => $row['Odds Ratio'],
            'Combined_Score' => $row['Combined Score']
        );
    }

    $response = array(
        'yAxisData' => $Ydata,
        'seriesData' => $Sdata,
        'tooltipData' => $tooltipData
    );

    // 返回数据数组为 JSON
    echo json_encode($response);
    
    $stmt->close();
} else {
    echo "BloodCircR_ID parameter is missing";
}

$conn->close();
?>
