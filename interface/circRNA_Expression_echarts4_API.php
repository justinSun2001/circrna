<?php
include 'conn.php';

// 检查是否收到 'BloodCircR_ID' 参数
if (isset($_GET['BloodCircR_ID'])) {
    $BloodCircR_ID = $_GET['BloodCircR_ID'];

    // 查询不同的疾病
    $sqlDistinct = "SELECT DISTINCT disease FROM host_gene WHERE host_gene IN (SELECT host_gene FROM circRNA_info WHERE BloodCircR_ID = ?)";
    $stmtDistinct = $conn->prepare($sqlDistinct);
    $stmtDistinct->bind_param("s", $BloodCircR_ID);
    $stmtDistinct->execute();
    $resultDistinct = $stmtDistinct->get_result();

    $datasets = array();
    $boxplotData = array();
    $outlierData = array();
    $tpmValues = array(); // 用于存储每个疾病的 TPM 值

    while ($rowDistinct = $resultDistinct->fetch_assoc()) {
        $disease = $rowDistinct['disease'];
        $datasets[] = $disease;

        // 查询每个疾病的 TPM 数据
        $sqlData = "SELECT TPM FROM host_gene WHERE host_gene IN (SELECT host_gene FROM circRNA_info WHERE BloodCircR_ID = ?) AND disease = ?";
        $stmtData = $conn->prepare($sqlData);
        $stmtData->bind_param("ss", $BloodCircR_ID, $disease);
        $stmtData->execute();
        $resultData = $stmtData->get_result();

        $expressionData = array();
        $currentOutliers = array();
        $currentTPMValues = array(); // 存储当前疾病的 TPM 值

        while ($rowData = $resultData->fetch_assoc()) {
            $tpm = $rowData['TPM'];
            if ($tpm !== null && $tpm !== 'N/A') {
                $tpmFloat = floatval($tpm);
                $expressionData[] = $tpmFloat; // 确保数据为浮点数
                $currentTPMValues[] = $tpmFloat; // 存储 TPM 值
            }
        }

        // 计算箱线图统计量
        if (count($expressionData) > 0) {
            sort($expressionData); // 确保数据已排序

            $q1 = percentile($expressionData, 25);
            $median = median($expressionData);
            $q3 = percentile($expressionData, 75);
            $iqr = $q3 - $q1;

            // 计算离群值边界
            $lowerBound = $q1 - 1.5 * $iqr;
            $upperBound = $q3 + 1.5 * $iqr;

            $min = min($expressionData); // 数据中的实际最小值
            $max = max($expressionData); // 数据中的实际最大值

            foreach ($expressionData as $value) {
                if ($value < $lowerBound || $value > $upperBound) {
                    $currentOutliers[] = $value;
                }
            }

            // 计算下须和上须
            $lowerWhisker = max($min, $lowerBound);
            $upperWhisker = min($max, $upperBound);

            // 添加计算的箱线图数据，并格式化为四位小数
            $boxplotData[] = array(
                number_format($lowerWhisker, 4),
                number_format($q1, 4),
                number_format($median, 4),
                number_format($q3, 4),
                number_format($upperWhisker, 4)
            );
        } else {
            // 如果没有有效的 TPM 值，添加 null
            $boxplotData[] = array(null, null, null, null, null);
        }

        // 添加离群值和 TPM 值
        $outlierData[] = $currentOutliers;
        $tpmValues[] = $currentTPMValues;

        $stmtData->close();
    }

    // 准备响应
    $response = array(
        "disease" => $datasets,
        "boxplotData" => $boxplotData,
        "outlierData" => $outlierData,
        "tpmValues" => $tpmValues
    );

    echo json_encode($response);

    $stmtDistinct->close();
} else {
    echo json_encode(array("error" => "BloodCircR_ID 参数缺失"));
}

$conn->close();

// 辅助函数：计算百分位数
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

// 辅助函数：计算中位数
function median($data) {
    sort($data);
    $count = count($data);
    $middle = floor(($count - 1) / 2);
    if ($count % 2) {
        return $data[$middle];
    } else {
        return ($data[$middle] + $data[$middle + 1]) / 2;
    }
}
?>
