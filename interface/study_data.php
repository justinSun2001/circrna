<?php

// 包含数据库连接文件
include_once('conn.php');

// 查询详细信息
$sql = "SELECT StudyID, Title, DatasetID, Type, Description,type2 FROM study_info WHERE Type IS NOT NULL";
$result = $conn->query($sql);

$detailData = array();
if ($result) {
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $detailData[] = $row;
        }
    } else {
        // 如果没有查询到结果，给出提示信息
        echo "没有查询到符合条件的详细信息。";
        exit;
    }
} else {
    // 如果查询详细信息时出错，给出错误信息
    echo "查询详细信息时出错：". $conn->error;
    exit;
}

// 查询所有的 Type 值
$sql = "SELECT DISTINCT Type FROM study_info WHERE Type IS NOT NULL ORDER BY Type ASC";
$stmt = $conn->prepare($sql);
if ($stmt) {
    $stmt->execute();
    $result = $stmt->get_result();

    $typeData = array();
    if ($result) {
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $typeData[] = [
                    'id' => $row['Type'],
                    'label' => $row['Type']
                ];
            }
        } else {
            // 如果没有查询到结果，给出提示信息
            echo "没有查询到符合条件的类型信息。";
            exit;
        }
    } else {
        // 如果查询类型信息时出错，给出错误信息
        echo "查询类型信息时出错：". $conn->error;
        exit;
    }
} else {
    // 如果准备查询语句时出错，给出错误信息
    echo "准备查询语句时出错：". $conn->error;
    exit;
}
// 创建 Select All 节点
$selectAllNode = [
    'id' => 'selectAll',
    'label' => 'Select All',
    'children' => $typeData
];

// 合并两个数据
$combinedData = [
    'detailData' => $detailData,
    'typeData' => $selectAllNode
];

$conn->close();

header('Content-Type: application/json');
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
$result = json_encode(m_mb_convert_encoding($combinedData));
// var_dump(json_last_error()); //获取异常代码编号
if ($result === false) {
    echo "json_encode 失败，可能存在数据类型不兼容、编码问题或循环引用等。";
} else {
    echo $result;
}
// echo json_encode($combinedData);
// echo "合并后的 combinedData：". print_r($combinedData, true);
// echo "准备查询语句时出错：";
?>