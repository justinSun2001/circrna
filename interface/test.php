<?php
include 'conn.php';

// 检查是否传递了 BloodCircR_ID 参数
if (isset($_GET['BloodCircR_ID'])) {
    $BloodCircR_ID = $_GET['BloodCircR_ID'];

    // 准备并执行获取节点数据的查询
    $nodes_query = "SELECT id, nodeclass, CAST(IDnumber AS CHAR) AS IDnumber FROM nodes WHERE BloodCircR_ID = ?";
    $nodes_stmt = $conn->prepare($nodes_query);
    $nodes_stmt->bind_param("s", $BloodCircR_ID);
    $nodes_stmt->execute();
    $nodes_result = $nodes_stmt->get_result();

    // 检查是否成功获取节点数据
    if ($nodes_result) {
        // 遍历结果集并组装节点数据
        while ($row = $nodes_result->fetch_assoc()) {
            $nodes[] = ['name' => $row['id'], 'category' => $row['nodeclass'], 'id'=> $row['IDnumber']];
        }
    } else {
        // 如果查询失败，返回错误消息
        die("获取节点数据失败: " . $conn->error);
    }

    // 准备并执行获取边数据的查询
    $links_query = "SELECT CAST(ID1 AS CHAR) AS ID1, CAST(ID2 AS CHAR) AS ID2 FROM edges WHERE BloodCircR_ID = ?";
    $links_stmt = $conn->prepare($links_query);
    $links_stmt->bind_param("s", $BloodCircR_ID);
    $links_stmt->execute();
    $links_result = $links_stmt->get_result();

    // 检查是否成功获取边数据
    if ($links_result) {
        // 遍历结果集并组装边数据
        while ($row = $links_result->fetch_assoc()) {
            $links[] = ['source' => $row['ID1'], 'target' => $row['ID2']];
        }
    } else {
        // 如果查询失败，返回错误消息
        die("获取边数据失败: " . $conn->error);
    }

    // 关闭数据库连接
    $conn->close();

    // 组装数据
    $data = ['nodes' => $nodes, 'links' => $links];

    // 输出数据以便前端JavaScript使用
    echo json_encode($data);
} else {
    // 如果没有传递 BloodCircR_ID 参数，返回错误消息
    die("缺少参数 BloodCircR_ID");
}
?>
