<?php
include_once('conn.php');

// 获取GET请求中的搜索条件、页码和每页数据个数
if (isset($_GET['BloodCircR_ID']) && isset($_GET['page']) && isset($_GET['limit'])) {
    $searchTerm = $_GET['BloodCircR_ID'];
    $page = $_GET['page'];
    $limit = $_GET['limit'];

    $sortField = isset($_GET['field']) ? $_GET['field'] : null; // 默认排序字段
    $sortOrder = isset($_GET['order']) ? $_GET['order'] : null; // 默认排序顺序

    // 使用预处理语句
    $countSql = "SELECT COUNT(*) AS total FROM mirna_info WHERE BloodCircR_ID LIKE ?";
    $stmt = $conn->prepare($countSql);
    $stmt->bind_param("s", $searchTerm);
    $stmt->execute();
    $countResult = $stmt->get_result();
    $totalCount = $countResult->fetch_assoc()['total'];

    // 计算偏移量
    $offset = ($page - 1) * $limit;

    // 使用预处理语句
    $sql = "SELECT * FROM mirna_info WHERE BloodCircR_ID LIKE ? ORDER BY `$sortField` $sortOrder LIMIT ? OFFSET ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sii", $searchTerm, $limit, $offset);
    $stmt->execute();
    $result = $stmt->get_result();

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
