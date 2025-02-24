<?php

include_once('conn.php');

// 检查是否有 DatasetID 参数传递过来
if (isset($_GET['StudyID'])) {
    $StudyID = $_GET['StudyID'];

    // 使用参数化查询
    $sql = "SELECT * FROM study_info WHERE StudyID =?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $StudyID);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        // 设置正确的 HTTP 状态码和 JSON 格式响应头
        http_response_code(200);
        function m_mb_convert_encoding($string) {
        	if(!is_array($string) && !is_int($string)) {
        		return mb_convert_encoding($string, 'UTF-8', 'UTF-8');
        	}
        
        	foreach($string as $key => $value) {
        		$string[$key] = m_mb_convert_encoding($value);
        	}
        
        	return $string;
        }
        // 尝试进行 json_encode
        $result = json_encode(m_mb_convert_encoding($row));
        // var_dump(json_last_error()); //获取异常代码编号
        if ($result === false) {
            echo "json_encode 失败，可能存在数据类型不兼容、编码问题或循环引用等。";
        } else {
            echo $result;
        }
        // echo json_encode($combinedData);
        // echo "合并后的 combinedData：". print_r($combinedData, true);
        // echo "准备查询语句时出错：";
    } else {
        // 如果没有找到数据，设置相应状态码和消息
        http_response_code(404);
        echo json_encode(['message' => "No data found for the given StudyID."]);
    }

    $stmt->close();
} else {
    // 如果没有提供 DatasetID 参数，设置相应状态码和消息
    http_response_code(400);
    echo json_encode(['message' => "StudyID not provided."]);
}

$conn->close();

?>