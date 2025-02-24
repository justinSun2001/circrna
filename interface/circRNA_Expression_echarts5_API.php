<?php
include 'conn.php';

// 检查是否收到 'BloodCircR_ID' 参数
if (isset($_GET['BloodCircR_ID'])) {
    $BloodCircR_ID = $_GET['BloodCircR_ID'];

    // 查询不同并去重的 Dataset
    $sqlDistinct = "SELECT DISTINCT disease FROM junction WHERE BSJ_ID IN (SELECT BSJ_ID FROM circRNA_info WHERE BloodCircR_ID = ?)";
    $stmtDistinct = $conn->prepare($sqlDistinct);
    $stmtDistinct->bind_param("s", $BloodCircR_ID);
    $stmtDistinct->execute();
    $resultDistinct = $stmtDistinct->get_result();

    $datasets = array();
    $boxplotData = array();
    $scatterData = array(); // 收集散点图数据

    while ($rowDistinct = $resultDistinct->fetch_assoc()) {
        $disease = $rowDistinct['disease'];
        $datasets[] = $disease;

        // 查询每个 Dataset 对应的 expression 和 Sample 数据
        $sqlData = "SELECT junction_ratio FROM junction WHERE BSJ_ID IN (SELECT BSJ_ID FROM circRNA_info WHERE BloodCircR_ID = ?) AND disease = ?";
        $stmtData = $conn->prepare($sqlData);
        $stmtData->bind_param("ss", $BloodCircR_ID, $disease);
        $stmtData->execute();
        $resultData = $stmtData->get_result();

        $expressionData = array();

        while ($rowData = $resultData->fetch_assoc()) {
            $junction_ratio = $rowData['junction_ratio'];
            if ($junction_ratio !== 'N/A' && $junction_ratio !== null) {
                $expressionData[] = floatval($junction_ratio);
                // 直接将 expression 数值作为字符串添加到数组
                $scatterData[] = array($disease, floatval($junction_ratio));
            }
        }

        if (count($expressionData) > 0) {
            // 计算盒须图的统计量，并限制小数点后四位
            sort($expressionData);
            $min = number_format(min($expressionData), 4);
            $q1 = number_format(percentile($expressionData, 25), 4);
            $median = number_format(median($expressionData), 4);
            $q3 = number_format(percentile($expressionData, 75), 4);
            $max = number_format(max($expressionData), 4);

            $boxplotData[] = array($min, $q1, $median, $q3, $max);
        } else {
            // 如果没有有效的数据，返回 null
            $boxplotData[] = array(null, null, null, null, null);
        }

        $stmtData->close();
    }

    $response = array(
        "disease" => $datasets,
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
// Helper function to calculate percentile
// Helper function to calculate percentile
function percentile($data, $percent) {
    sort($data);
    $count = count($data);
    $index = ($percent / 100) * ($count - 1);
    $floorIndex = floor($index);
    $ceilIndex = ceil($index);
    $fraction = $index - $floorIndex;
    
    if ($ceilIndex < $count) {
        return $data[$floorIndex] + $fraction * ($data[$ceilIndex] - $data[$floorIndex]);
    } else {
        return $data[$floorIndex];
    }
}


// Helper function to calculate median
function median($data) {
    sort($data);
    $count = count($data);
    $middle = floor(($count - 1) / 2);
    return ($count % 2) ? $data[$middle] : ($data[$middle] + $data[$middle + 1]) / 2;
}
?>
