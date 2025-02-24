<?php
include_once('conn.php'); // 包含数据库连接文件，假设您已设置好数据库连接

// 获取GET请求中的BloodCircR_ID
if (isset($_GET['BloodCircR_ID'])) {
    $BloodCircR_ID = $_GET['BloodCircR_ID'];

    // 列名和对应的name
    $columnNames = array(
        'm6A_sites' => 'm6A_sites',
        'Ribosome_polysome_profiling' => 'Ribosome_polysome_profiling',
        'Translation_initiation_site' => 'Translation_initiation_site',
        'ORF' => 'ORF',
        'IRES_sequence' => 'IRES_sequence',
        'SeqComp' => 'SeqComp',
        'MS_evidence' => 'MS_evidence'
    );

    // 查询数据库以获取对应列的值
    $values = array();
    $evidencesScore = 0; // 初始化evidences score

    foreach ($columnNames as $columnName => $name) {
        $sql = "SELECT $columnName FROM evidence_info WHERE BloodCircR_ID = '$BloodCircR_ID'";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $value = $row[$columnName];

            // 将值添加到$values数组
            $values[] = $value;
        }
    }

    // 获取evidences score
    $evidencesScoreSql = "SELECT evidences_score FROM evidence_info WHERE BloodCircR_ID = '$BloodCircR_ID'";
    $evidencesScoreResult = $conn->query($evidencesScoreSql);

    if ($evidencesScoreResult->num_rows > 0) {
        $row = $evidencesScoreResult->fetch_assoc();
        $evidencesScore = $row['evidences_score'];
    }

    $response = array(
        'data' => array(
            array(
                'value' => $values,
                'name' => "evidences score: $evidencesScore"
            )
        )
    );

    // 设置响应头部为JSON格式
    header('Content-Type: application/json');

    // 返回JSON数据
    echo json_encode($response);
} else {
    $response = array(
        'code' => 1,
        'msg' => '未提供有效的BloodCircR_ID',
        'data' => array()
    );

    // 设置响应头部为JSON格式
    header('Content-Type: application/json');

    // 返回JSON数据
    echo json_encode($response);
}

// 关闭数据库连接
$conn->close();
?>
