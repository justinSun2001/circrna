<?php
include 'conn.php';

if (isset($_GET['Dataset'])) {
    $Dataset = $_GET['Dataset'];

    // 创建 SQL 查询语句
    $sql = "SELECT `break_`, isocirc, full_, only_ FROM dataset_state WHERE Dataset = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $Dataset);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $data[] = array('value' => $row['break_'], 'name' => 'break');
        $data[] = array('value' => $row['isocirc'], 'name' => 'isocirc');
        $data[] = array('value' => $row['full_'], 'name' => 'full');
        $data[] = array('value' => $row['only_'], 'name' => 'only');

        // 返回数据数组为 JSON
        echo json_encode($data);
    } else {
        echo "No data found for Dataset: " . $Dataset;
    }

    $stmt->close();
} else {
    echo "Dataset parameter is missing";
}

$conn->close();
?>
