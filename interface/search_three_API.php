<?php
include_once('conn.php');

// 获取GET请求中的搜索条件、页码和每页数据个数
if (isset($_GET['chr']) && isset($_GET['circRNA_start']) && isset($_GET['circRNA_end']) && isset($_GET['page']) && isset($_GET['limit'])) {
    $chr = $_GET['chr'];
    $circRNAStart = $_GET['circRNA_start'];
    $circRNAEnd = $_GET['circRNA_end'];
    $page = $_GET['page'];
    $limit = $_GET['limit'];

    // 查询数据库以获取总行数
    $countSql = "SELECT COUNT(*) AS total FROM circRNA_info WHERE chr = '$chr' AND circRNA_start = '$circRNAStart' AND circRNA_end = '$circRNAEnd'";
    $countResult = $conn->query($countSql);
    $totalCount = $countResult->fetch_assoc()['total'];

    // 计算偏移量
    $offset = ($page - 1) * $limit;

    // 使用查询来查找匹配的值并应用分页
    $sql = "SELECT * FROM circRNA_info WHERE chr = '$chr' AND circRNA_start = '$circRNAStart' AND circRNA_end = '$circRNAEnd' LIMIT $limit OFFSET $offset";

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
