<?php

include_once('conn.php');

// 检查是否有 DatasetID 参数传递过来
if (isset($_GET['DatasetID'])) {
    $DatasetID = $_GET['DatasetID'];

    // 使用参数化查询
    $sql = "SELECT * FROM dataset_info2 WHERE DatasetID =?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $DatasetID);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        // 设置正确的 HTTP 状态码和 JSON 格式响应头
        http_response_code(200);
        header('Content-Type: application/json');
        echo json_encode($row);
    } else {
        // 如果没有找到数据，设置相应状态码和消息
        http_response_code(404);
        echo json_encode(['message' => "No data found for the given DatasetID."]);
    }

    $stmt->close();
} else {
    // 如果没有提供 DatasetID 参数，设置相应状态码和消息
    http_response_code(400);
    echo json_encode(['message' => "DatasetID not provided."]);
}

$conn->close();

?>