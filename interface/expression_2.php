<?php
set_time_limit(0); // 设置脚本无限制执行时间
ini_set('max_execution_time', 0); // 设置最大执行时间为0，表示无限制

include_once('conn.php'); // 包含数据库连接文件
// 包含R脚本路径
include 'run_r_script.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST'){

$datasetname = $_GET['datasetname'];
$marker = $_GET['marker'];
$circRNAs = $_GET['circRNAs'];

// 使用逗号分割 datasetname
$parts = explode(';', $datasetname);
$newParts = [];

foreach ($parts as $part) {
    // 去除括号及其内容，只保留括号前的数据集编号
    $part = preg_replace('/\(.*/', '', $part);
    // 去除每部分的空白字符
    $cleanPart = trim($part);
    if (!empty($cleanPart)) {
        $newParts[] = $cleanPart;
    }
}
// 将提取出的数据集编号重新拼接成字符串
$datasetname = implode(',', $newParts);

// 准备参数(项目编号)
$param2 = $datasetname;
$param3 = $marker;
$param4 = $circRNAs;



// 构建R脚本执行命令，参数通过命令行传给R脚本

$rScriptPath = $rScriptDir . "expression_2.R"; // R脚本的路径
$command = "Rscript {$rScriptPath}  '{$param2}' '{$param3}' '{$param4}'";

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
$file1 = '/data2/njucm1/BloodCircleR/Expression/cache/expression_2.svg';
$file2 = '/data2/njucm1/BloodCircleR/Expression/cache/expression_2.csv';
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
