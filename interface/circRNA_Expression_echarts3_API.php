<?php
include 'conn.php';

// Check if 'BloodCircR_ID' parameter is received
if (isset($_GET['BloodCircR_ID'])) {
    $BloodCircR_ID = $_GET['BloodCircR_ID'];

    // Query distinct diseases
    $sqlDistinct = "SELECT DISTINCT disease FROM circrna_disease WHERE BloodCircR_ID = ?";
    $stmtDistinct = $conn->prepare($sqlDistinct);
    $stmtDistinct->bind_param("s", $BloodCircR_ID);
    $stmtDistinct->execute();
    $resultDistinct = $stmtDistinct->get_result();

    $datasets = array();
    $boxplotData = array();
    $outlierData = array();

    while ($rowDistinct = $resultDistinct->fetch_assoc()) {
        $disease = $rowDistinct['disease'];
        $datasets[] = $disease;

        // Query TPM data for each disease
        $sqlData = "SELECT TPM FROM circrna_disease WHERE BloodCircR_ID = ? AND disease = ?";
        $stmtData = $conn->prepare($sqlData);
        $stmtData->bind_param("ss", $BloodCircR_ID, $disease);
        $stmtData->execute();
        $resultData = $stmtData->get_result();

        $expressionData = array();

        // Fetch TPM values and skip N/A or null values
        while ($rowData = $resultData->fetch_assoc()) {
            $tpm = $rowData['TPM'];
            if ($tpm !== null && $tpm !== 'N/A') {
                $expressionData[] = floatval($tpm); // Ensure data is float
            }
        }

        // If there are valid TPM values, calculate the boxplot data
        if (count($expressionData) > 0) {
            sort($expressionData); // Ensure the data is sorted

            $q1 = number_format(percentile($expressionData, 25), 4);
            $median = number_format(median($expressionData), 4);
            $q3 = number_format(percentile($expressionData, 75), 4);
            $iqr = $q3 - $q1;
            $min = number_format(max(min($expressionData), $q1 - 1.5 * $iqr), 4);
            $max = number_format(min(max($expressionData), $q3 + 1.5 * $iqr), 4);

            // Collect outliers
            foreach ($expressionData as $value) {
                if ($value < $min || $value > $max) {
                    $outlierData[] = array($disease, number_format($value, 4));
                }
            }

            // Append calculated boxplot data
            $boxplotData[] = array($min, $q1, $median, $q3, $max);
        } else {
            // If no valid TPM values, append nulls
            $boxplotData[] = array(null, null, null, null, null);
        }

        $stmtData->close();
    }

    // Prepare response
    $response = array(
        "disease" => $datasets,
        "boxplotData" => $boxplotData,
        "outlierData" => $outlierData
    );

    echo json_encode($response);

    $stmtDistinct->close();
} else {
    echo json_encode(array("error" => "BloodCircR_ID parameter is missing"));
}

$conn->close();

// Helper function to calculate percentile
function percentile($data, $percent) {
    $count = count($data);
    $index = ($percent / 100) * ($count - 1);
    if (is_float($index)) {
        $floorIndex = floor($index);
        $ceilIndex = ceil($index);
        $fraction = $index - $floorIndex;
        return $data[$floorIndex] + $fraction * ($data[$ceilIndex] - $data[$floorIndex]);
    } else {
        return $data[$index];
    }
}

// Helper function to calculate median
function median($data) {
    $count = count($data);
    $middle = floor(($count - 1) / 2);
    if ($count % 2) {
        return $data[$middle];
    } else {
        return ($data[$middle] + $data[$middle + 1]) / 2;
    }
}
?>
