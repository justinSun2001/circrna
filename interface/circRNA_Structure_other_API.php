<?php
include 'conn.php';

if (isset($_GET['BloodCircR_ID'])) {
    $BloodCircR_ID = $_GET['BloodCircR_ID'];

    // 查询满足条件的数据
    $sql = "SELECT m6A, RP_RP, TIS, SeqComp FROM structure_info WHERE `BloodCircR_ID` = ? AND m6A IS NOT NULL AND RP_RP IS NOT NULL AND TIS IS NOT NULL AND SeqComp IS NOT NULL";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $BloodCircR_ID);
    $stmt->execute();
    $result = $stmt->get_result();

    $data = array();
    $newData = array();

    $exonData = array(); // 新的数组用于存储'exon'列数据
    $sequenceData = array(); // 用于存储'Sequence'列数据

    $rowNumber = 1;
    while ($row = $result->fetch_assoc()) {
        $m6A     = $row['m6A'];
        $RP_RP   = $row['RP_RP'];
        $TIS     = $row['TIS'];
        $SeqComp = $row['SeqComp'];
        $data[] = array("m6A" => $m6A, "RP_RP" => $RP_RP, "TIS" => $TIS, "SeqComp" => $SeqComp);


    }

    $response = array(
        'data' => $data
    );

    // 返回数据数组为 JSON
    echo json_encode($response);

    $stmt->close();
} else {
    echo "BloodCircR_ID parameter is missing";
}

$conn->close();
?>
