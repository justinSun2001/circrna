<?php
include_once('conn.php');

// 获取GET请求中的搜索条件、页码和每页数据个数
if (isset($_GET['BloodCircR_ID']) && isset($_GET['page']) && isset($_GET['limit'])) {
    $searchTerm = $_GET['BloodCircR_ID'];
    $page = intval($_GET['page']); // 将页码转换为整数
    $limit = intval($_GET['limit']); // 每页数据个数转换为整数
    $sortField = isset($_GET['field']) ? $_GET['field'] : 'disease'; // 默认排序字段
    $sortOrder = isset($_GET['order']) && in_array(strtolower($_GET['order']), ['asc', 'desc']) ? strtoupper($_GET['order']) : 'ASC'; // 默认排序顺序为 ASC

    // 计算偏移量
    $offset = ($page - 1) * $limit;

    // 使用预处理语句
    $sql = "SELECT disease, junction_reads_ratio 
            FROM expression_means3 
            WHERE BSJ_ID IN (SELECT BSJ_ID FROM circRNA_info WHERE BloodCircR_ID = ?) 
            ORDER BY `$sortField` $sortOrder 
            LIMIT ?, ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sii", $searchTerm, $offset, $limit); // 绑定参数，偏移量和限制数为整数类型
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $data = array();
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }

        // 查询总数，用于分页
        $countSql = "SELECT COUNT(*) as total 
                     FROM expression_means3 
                     WHERE BSJ_ID IN (SELECT BSJ_ID FROM circRNA_info WHERE BloodCircR_ID = ?)";
        $countStmt = $conn->prepare($countSql);
        $countStmt->bind_param("s", $searchTerm);
        $countStmt->execute();
        $countResult = $countStmt->get_result();
        $totalCount = $countResult->fetch_assoc()['total'];

        $response = array(
            'code' => 0,
            'msg' => '',
            'count' => $totalCount, // 数据总数
            'data' => $data // 当前页数据
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
        'msg' => '未提供有效的搜索条件',
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
