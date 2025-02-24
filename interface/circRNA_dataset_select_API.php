<?php
include 'conn.php';


    
$sql = "SELECT Dataset FROM dataset_info";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $data = array();
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }

    $response = array(
        'data' => $data
    );

    // 设置响应头部为JSON格式
    header('Content-Type: application/json');

    // 返回JSON数据
    echo json_encode($response);
} else {
    $response = array(
        'code' => 0,
        'msg' => '没有找到匹配的数据',
    );

    // 设置响应头部为JSON格式
    header('Content-Type: application/json');

    // 返回JSON数据
    echo json_encode($response);
}
$conn->close();
?>
