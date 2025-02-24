<?php
include 'conn.php';

if (isset($_GET['BloodCircR_ID'])) {
    $BloodCircR_ID = $_GET['BloodCircR_ID'];

    // 查询满足条件的数据
    $sql = "SELECT exon_length, exon_seq, exon, Sequence FROM structure_info WHERE `BloodCircR_ID` = ? AND exon IS NOT NULL AND exon_length IS NOT NULL";
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
        $exonLength = $row['exon_length'];
        $exonSeq = $row['exon_seq'];
        $exonName = "exon" . $rowNumber;
        $data[] = array("value" => $exonLength, "name" => $exonName);

        // 添加'exon'列数据到新数组
        $exonData[] = array("exonvalue" => $row['exon'], "name" => $exonName);

        // 设置'Sequence'列数据
        $sequenceData[] = $row['Sequence'];
        $IRES_Data[] = $row['IRES'];

        $newData[] = array("seq" => $exonSeq, "name" => $exonName);

        $rowNumber++;
    }

    $response = array(
        'data' => $data,
        'newData' => $newData,
        'exonData' => $exonData,
        'Sequence' => $sequenceData,
        'IRES_Data' => $IRES_Data
    );

    // 返回数据数组为 JSON
    echo json_encode($response);

    $stmt->close();
} else {
    echo "BloodCircR_ID parameter is missing";
}

$conn->close();
?>
