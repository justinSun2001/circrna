<?php
include 'conn.php';

// 检查是否收到 'BloodCircR_ID' 参数
if (isset($_GET['BloodCircR_ID'])) {
    $BloodCircR_ID = $_GET['BloodCircR_ID'];

    // 查询不同并去重的 Dataset
    $sqlDistinct = "SELECT DISTINCT Dataset FROM express_info WHERE BloodCircR_ID = ?";
    $stmtDistinct = $conn->prepare($sqlDistinct);
    $stmtDistinct->bind_param("s", $BloodCircR_ID);
    $stmtDistinct->execute();
    $resultDistinct = $stmtDistinct->get_result();

    $datasets = array();
    $boxplotData = array();
    $scatterData = array();

    while ($rowDistinct = $resultDistinct->fetch_assoc()) {
        $dataset = $rowDistinct['Dataset'];
        $datasets[] = $dataset;

        // 查询每个 Dataset 对应的 expression 和 Sample 数据
        $sqlData = "SELECT expression, Sample FROM express_info WHERE BloodCircR_ID = ? AND Dataset = ?";
        $stmtData = $conn->prepare($sqlData);
        $stmtData->bind_param("ss", $BloodCircR_ID, $dataset);
        $stmtData->execute();
        $resultData = $stmtData->get_result();

        $expressionData = array();

        while ($rowData = $resultData->fetch_assoc()) {
            $expressionData[] = $rowData['expression'];

            // 直接将 expression 数值作为字符串添加到数组
            $scatterData[] = array($dataset,  $rowData['expression']);
        }

        // 计算盒须图的统计量，并限制小数点后两位
        $min = number_format(min($expressionData), 3);
        $q1 = number_format(percentile($expressionData, 25), 3);
        $median = number_format(median($expressionData), 3);
        $q3 = number_format(percentile($expressionData, 75), 3);
        $max = number_format(max($expressionData), 3);

        $boxplotData[] = array($min, $q1, $median, $q3, $max);

        $stmtData->close();
    }

    $response = array(
        "datasets" => $datasets,
        "boxplotData" => $boxplotData,
        "scatterData" => $scatterData,
    );

    echo json_encode($response);

    $stmtDistinct->close();
} else {
    echo "BloodCircR_ID parameter is missing";
}

$conn->close();

// Helper function to calculate percentile
function percentile($data, $percent) {
    sort($data);
    $index = ($percent / 100) * count($data) - 1;
    return $data[ceil($index)];
}

// Helper function to calculate median
function median($data) {
    sort($data);
    $count = count($data);
    $middle = floor(($count - 1) / 2);
    return ($data[$middle] + $data[$middle + 1 - $count % 2]) / 2;
}
?>
