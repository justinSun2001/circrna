<?php

include_once('conn.php'); // 包含数据库连接文件

// 获取前端传来的参数
$currentPage = isset($_GET['currentPage'])? intval($_GET['currentPage']) : 1;
$pageSize = isset($_GET['pageSize'])? intval($_GET['pageSize']) : 10;
$datasetname = $_GET['datasetname'];
$searchField = isset($_GET['searchField'])? $_GET['searchField'] : '';
$searchKeyword = isset($_GET['searchKeyword'])? $_GET['searchKeyword'] : '';
$sortBy = isset($_GET['sortBy'])? $_GET['sortBy'] : '';
$sortOrder = isset($_GET['sortOrder'])? $_GET['sortOrder'] : '';

// 计算偏移量
$offset = ($currentPage - 1) * $pageSize;
// 预编译语句，防止 SQL 注入
$sql_count = "SELECT COUNT(*) as total FROM sample_detection_data WHERE Dataset=?";
if ($searchField!== 'All' && $searchKeyword!== '') {
    if ($searchField === 'ratio') {
        // 如果 searchField 是 ratio，使用 LIKE 进行模糊匹配
        $sql_count.= " AND $searchField LIKE?";
    } else {
        // 如果是其他字段，进行完全匹配
        $sql_count.= " AND $searchField =?";
    }
}

// 准备预处理语句
$stmt_count = $conn->prepare($sql_count);
if ($searchField!== 'All' && $searchKeyword!== '') {
    if ($searchField === 'ratio') {
        $searchKeyword = "%$searchKeyword%";
        $stmt_count->bind_param('ss', $datasetname, $searchKeyword); // 两个参数分别是 Dataset 和 $searchKeyword
    } else {
        $stmt_count->bind_param('ss', $datasetname, $searchKeyword); // 两个参数分别是 Dataset 和 $searchKeyword
    }
} else {
    $stmt_count->bind_param('s', $datasetname); // 只有 Dataset 参数
}

// 执行查询并获取结果
$stmt_count->execute();
$result_count = $stmt_count->get_result();
$total = $result_count->fetch_assoc()['total'];

// 返回数据总数给前端
$response = [
    "total" => $total
];

// 获取指定页的数据
$sql_data = "SELECT * FROM sample_detection_data WHERE Dataset=?";
if ($searchField!== 'All' && $searchKeyword!== '') {
    $sql_data.= " AND $searchField LIKE?";
}
if ($sortBy!== '' && $sortOrder!== '') {
    $sql_data.= " ORDER BY $sortBy $sortOrder";
}
$sql_data.= " LIMIT?,?";

// 准备预处理语句
$stmt_data = $conn->prepare($sql_data);
if ($searchField!== 'All' && $searchKeyword!== '') {
    $stmt_data->bind_param('ssii', $datasetname, $searchKeyword, $offset, $pageSize); // 绑定 Dataset, $searchKeyword, $offset 和 $pageSize
} else {
    $stmt_data->bind_param('sii', $datasetname, $offset, $pageSize); // 绑定 Dataset, $offset 和 $pageSize
}

// 执行查询并获取结果
$stmt_data->execute();
$result_data = $stmt_data->get_result();

$sample_detection_data = [];
if ($result_data->num_rows > 0) {
    while ($row = $result_data->fetch_assoc()) {
        $sample_detection_data[] = $row;
    }
}

// 返回数据给前端
$response["data"] = $sample_detection_data;

// 关闭语句和连接
$stmt_count->close();
$stmt_data->close();
$conn->close();

// 返回 JSON 数据
echo json_encode($response);

?>