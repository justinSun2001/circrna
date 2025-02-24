<?php
include 'conn.php';

if (isset($_GET['Dataset'])) {
    $Dataset = $_GET['Dataset'];

    $sql = "SELECT express_pro2, exons_num FROM exon_and_express WHERE Dataset = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $Dataset);
    $stmt->execute();
    $result = $stmt->get_result();

    $data = array();

    while ($row = $result->fetch_assoc()) {
        $express_pro2 = floatval($row['express_pro2']);
        $exons_num = floatval($row['exons_num']);

        $data[] = [$express_pro2, $exons_num];
    }

    echo json_encode($data);

    $stmt->close();
} else {
    echo "Dataset parameter is missing";
}

$conn->close();
?>
