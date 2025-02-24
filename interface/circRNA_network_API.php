<?php
include 'conn.php';

// 检查是否收到 'BloodCircR_ID' 参数
if (isset($_GET['BloodCircR_ID'])) {
    $BloodCircR_ID = $_GET['BloodCircR_ID'];
    // 查询 network 表中的数据
    $sqlNetwork = "SELECT id, name, symbolSize,x,y,`value`, category FROM network WHERE BloodCircR_ID = ?";
    $stmtNetwork = $conn->prepare($sqlNetwork);
    $stmtNetwork->bind_param("s", $BloodCircR_ID);
    $stmtNetwork->execute();
    $resultNetwork = $stmtNetwork->get_result();
    if ($resultNetwork->num_rows > 0) {
        $nodes = array();
        // 将 network 表的查询结果存入 nodes 数组
        while ($rowNetwork = $resultNetwork->fetch_assoc()) {
            $nodes[] = $rowNetwork;
        }
        // 查询 package 表中的数据
        $sqlPackage = "SELECT source,target FROM package WHERE BloodCircR_ID = ?";
        $stmtPackage = $conn->prepare($sqlPackage);
        $stmtPackage->bind_param("s", $BloodCircR_ID);
        $stmtPackage->execute();
        $resultPackage = $stmtPackage->get_result();

        if ($resultPackage->num_rows > 0) {
            $links = array();

            // 将 package 表的查询结果存入 links 数组
            while ($rowPackage = $resultPackage->fetch_assoc()) {
                $links[] = $rowPackage;
            }

            // 返回JSON格式的数据给前端
            $response = array(
                'nodes' => $nodes,
                'links' => $links
            );

            echo json_encode($response);
        } else {
            echo "No data found in the package table for the provided BloodCircR_ID";
        }

        $stmtPackage->close();
    } else {
        echo "No data found in the network table for the provided BloodCircR_ID";
    }

    $stmtNetwork->close();
} else {
    echo "BloodCircR_ID parameter is missing";
}

$conn->close();
?>


