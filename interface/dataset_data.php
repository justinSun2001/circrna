<?php

include_once('conn.php'); // 包含数据库连接文件

// 查询详细信息
$sql = "SELECT * FROM dataset_info2 WHERE Type IS NOT NULL";
$result = $conn->query($sql);

$detailData = array();
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $detailData[] = $row;
    }
}

// 查询所有的 Type 值
$sql = "SELECT DISTINCT Type FROM dataset_info2 WHERE Type IS NOT NULL ORDER BY Type ASC";
$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();

$typeData = array();
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $typeId = $row['Type'];
        // 获取对应父节点下的子节点数据
        $sqlChildren = "SELECT DISTINCT Phenotype FROM dataset_info2 WHERE Type =? ORDER BY Phenotype ASC";
        $stmtChildren = $conn->prepare($sqlChildren);
        $stmtChildren->bind_param("s", $typeId);
        $stmtChildren->execute();
        $resultChildren = $stmtChildren->get_result();
        $childrenData = array();
        if ($resultChildren) {
            while ($childRow = $resultChildren->fetch_assoc()) {
                $childrenData[] = [
                    'id' => $childRow['Phenotype'],
                    'label' => $childRow['Phenotype']
                ];
            }
        }
        $typeData[] = [
            'id' => $typeId,
            'label' => $typeId,
            'children' => $childrenData
        ];
    }
}

// 查询所有的 Platform 值
$sql = "SELECT DISTINCT Platform FROM dataset_info2 WHERE Platform IS NOT NULL ORDER BY Platform ASC";
$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();

$platformData = array();
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $platformData[] = [
            'id' => $row['Platform'],
            'label' => $row['Platform']
        ];
    }
}
// 创建 Select All 节点
$selectAllNode = [
    'id' => 'selectAll',
    'label' => 'Select All',
    'children' => $typeData
];
$selectAllNode1 = [
    'id' => 'selectAll',
    'label' => 'Select All',
    'children' => $platformData
];
// 合并两个数据
$combinedData = [
    'detailData' => $detailData,
    'typeData' => $selectAllNode,
    'platformData' => $selectAllNode1
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
$result = json_encode($combinedData);
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