<?php
set_time_limit(0); // 设置脚本无限制执行时间
ini_set('max_execution_time', 0); // 设置最大执行时间为0，表示无限制

include_once('conn.php'); // 包含数据库连接文件
// 包含R脚本路径
include 'run_r_script.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST'){

$datasetname = $_GET['datasetname'];
// print_r($datasetname);
$hostgenes = $_GET['hostgenes'];
// 使用逗号分割 datasetname
$parts = explode(',', $datasetname);
$newParts = [];
foreach ($parts as $part) {
    // 使用冒号分割每一部分，并获取第一部分
    $subParts = explode(':', $part);
    $newParts[] = $subParts[0];
}
$datasetname = implode(',', $newParts);
// 准备参数(项目编号)
$param2 = $datasetname;
$param3 = $hostgenes;
// 构建R脚本执行命令，参数通过命令行传给R脚本
$condaEnv = 'base'; // 替换为你的Anaconda环境名称
$rScriptPath = $rScriptDir . "expression_4.R"; // R脚本的路径
$command = "source activate {$condaEnv} && Rscript {$rScriptPath} '{$param1}' '{$param2}' '{$param3}' 2>&1";

$output = [];
$return_var = 0;
exec($command, $output, $return_var);
// 错误输出测试
if ($return_var !== 0) {
    echo "执行R脚本时出错。返回状态: " . $return_var . "<br>";
    echo "输出: " . implode("<br>", $output);
} else {
    // echo "R脚本输出: " . implode("<br>", $output);
}
// 获取数据
$file1 = $param1 . 'expression_4.svg';
$file2 = $param1 . 'expression_4_data.csv';
// 检查文件是否存在
if (!file_exists($file1) || !file_exists($file2)) {
    echo "文件不存在。";
    return;
}
// 读取文件内容
$images_data = file_get_contents($file1);
$csv_data = file_get_contents($file2);
if (file_exists($file1)) {
    unlink($file1);
}
if (file_exists($file2)) {
    unlink($file2);
}
if ($images_data) {
    // 将图像数据转换为Base64编码
    $img1_base64 = base64_encode($images_data);
    $csv1_base64 = base64_encode($csv_data);
    // 返回JSON响应，包含两个Base64编码的数据
    http_response_code(200);
    header('Content-Type: application/json');
    echo json_encode(["image1" => $img1_base64, "data1" => $csv1_base64]);
} else {
    http_response_code(404);
    header('Content-Type: application/json');
    echo json_encode(["error" => "Dataset not found2"]);
}
}
?>
