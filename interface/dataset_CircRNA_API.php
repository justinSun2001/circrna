<?php
include_once('conn.php');

// 获取GET请求中的搜索条件、页码和每页数据个数
if (isset($_GET['Dataset']) && isset($_GET['page']) && isset($_GET['limit'])) {
    $searchTerm = $_GET['Dataset'];
    $page = $_GET['page'];
    $limit = $_GET['limit'];

    // 查询数据库以获取总行数
    $countSql = "SELECT COUNT(*) AS total FROM dataset_circrna WHERE Dataset LIKE '%$searchTerm%'";
    $countResult = $conn->query($countSql);
    $totalCount = $countResult->fetch_assoc()['total'];

    // 计算偏移量
    $offset = ($page - 1) * $limit;

    // 使用查询来查找匹配的值并应用分页
    $sql = "SELECT * FROM dataset_circrna WHERE Dataset LIKE '%$searchTerm%' LIMIT $limit OFFSET $offset";

    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $data = array();
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }

        $response = array(
            'code' => 0,
            'msg' => '',
            'count' => $totalCount, // 设置为总行数
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
            'count' => 0,
            'data' => array()
        );

        // 设置响应头部为JSON格式
        header('Content-Type: application/json');

        // 返回JSON数据
        echo json_encode($response);
    }
} else {
    $response = array(
        'code' => 1,
        'msg' => '未提供有效的搜索条件、页码和每页数据个数',
        'count' => 0,
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
