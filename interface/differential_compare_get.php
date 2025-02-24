<?php
set_time_limit(0); // 设置脚本无限制执行时间
ini_set('max_execution_time', 0); // 设置最大执行时间为0，表示无限制

include_once('conn.php'); // 包含数据库连接文件

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $datasetname = $_GET['datasetname'];

    // 查询 dataset_select 表
    $sql = "SELECT `Group` FROM dataset_select WHERE Dataset = '$datasetname'";
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $group_values = explode(' vs ', $row['Group']);
        // 将数组编码为 JSON 格式并输出给前端
        echo json_encode(["data" => $group_values]);
    } else {
        // 如果 dataset_select 表中没有找到结果，则查询 user_data 表
        $sql_user_data = "SELECT `Group` FROM user_data WHERE Dataset = '$datasetname'";
        $result_user_data = $conn->query($sql_user_data);

        if ($result_user_data->num_rows > 0) {
            $row_user_data = $result_user_data->fetch_assoc();
            $group_values = explode(' vs ', $row_user_data['Group']);
            // 将数组编码为 JSON 格式并输出给前端
            echo json_encode(["data" => $group_values]);
        } else {
            echo json_encode(array("error" => "No group found for the given dataset"));
        }
    }
}
?>
