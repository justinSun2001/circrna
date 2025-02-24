<?php

set_time_limit(0); // 设置脚本无限制执行时间
ini_set('max_execution_time', 0); // 设置最大执行时间为0，表示无限制

include_once('conn.php'); // 包含数据库连接文件
// 包含R脚本路径
include 'run_r_script.php';

function writeDB($datasetname) {
    global $conn; // 引入全局的$conn变量到函数作用域内
	$file1 = '/data2/njucm1/BloodCircleR/Expression/cache/differential_expression_2.svg';  
	$file2 = '/data2/njucm1/BloodCircleR/Expression/cache/differential_expression_2.csv';
    // 检查文件是否存在
    if (!file_exists($file1) || !file_exists($file2)) {
        echo "文件不存在。";
        return;
    }

    // 读取文件内容
    $data1 = file_get_contents($file1);
    $data2 = file_get_contents($file2);

    // 确认数据读取成功
    if ($data1 === false || $data2 === false) {
        echo "读取文件内容失败。";
        return;
    }

    // 创建表格并确保 Dataset 字段是唯一键
    $sql = "CREATE TABLE IF NOT EXISTS differential_expression_2 (
        `Dataset` VARCHAR(255) UNIQUE,
        `image1` LONGBLOB,
        `data1` LONGBLOB
    )";
    $conn->query($sql);

    // 使用 INSERT ... ON DUPLICATE KEY UPDATE 语法插入或更新记录
    $insert_sql = "INSERT INTO differential_expression_2 (Dataset, image1, data1) VALUES (?, ?, ?)
                   ON DUPLICATE KEY UPDATE image1 = VALUES(image1), data1 = VALUES(data1)";
    $stmt = $conn->prepare($insert_sql);
    $null1 = NULL;
    $null2 = NULL;
    $stmt->bind_param("sbb", $datasetname, $null1, $null2);
    $stmt->send_long_data(1, $data1);
    $stmt->send_long_data(2, $data2);
    $stmt->execute();
    $conn->commit();
    $stmt->close();

    // 删除文件
    if (file_exists($file1)) {
        unlink($file1);
    }
    if (file_exists($file2)) {
        unlink($file2);
    }
}

// 根据数据集名称获取图像和CSV数据
function fetchImagesAndCSVByDatasetName($datasetname) {
    global $conn; // 引入全局的$conn变量到函数作用域内
    $sql = "SELECT image1, data1 FROM differential_expression_2 WHERE Dataset = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $datasetname);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();
    $stmt->close();
    $conn->close();
    return $data;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST'){

$datasetname = isset($_POST['datasetname']) ? $_POST['datasetname'] : (isset($_GET['datasetname']) ? $_GET['datasetname'] : '');


    // 如果没有 datasetname，输出错误信息
    if (empty($datasetname)) {
        echo "Datasetname 参数为空";
        exit;
    }


    // 处理 datasetname
    $datasetname = preg_replace('/\(.*/', '', $datasetname);
    $parts = explode(',', $datasetname);
    $newParts = [];

    foreach ($parts as $part) {
        $cleanPart = trim($part);
        if (!empty($cleanPart)) {
            $newParts[] = $cleanPart;
        }
    }

    $datasetname = implode(',', $newParts);
$param2 = $datasetname;
$param5 = $_GET['pvalue'];
$param6 = $_GET['FoldChange'];
$param4 = $_GET['level'];
$param7 = $_GET['detectR'];
$param3 = $_GET['compare'];
$param8 = $_GET['type'];
$param9 = $_GET['number'];
// 构建R脚本执行命令，参数通过命令行传给R脚本


$rScriptPath = $rScriptDir."differential_expression_2.R"; // R脚本的路径
// 构建Rscript命令
$command = "Rscript {$rScriptPath} '{$param2}' '{$param3}' '{$param4}' '{$param5}' '{$param6}' '{$param7}' '{$param8}' '{$param9}'  2>&1";



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
writeDB($datasetname);
// 获取数据
$data = fetchImagesAndCSVByDatasetName($datasetname);
if ($data) {
    // 将图像和CSV数据转换为Base64编码
    $img1_base64 = base64_encode($data['image1']);
    $csv1_base64 = base64_encode($data['data1']);
    // 返回JSON响应，包含两个Base64编码的数据
    http_response_code(200);
    header('Content-Type: application/json');
    echo json_encode(["image1" => $img1_base64, "data1" => $csv1_base64]);
} else {
    http_response_code(404);
    header('Content-Type: application/json');
    echo json_encode(["error" => "Dataset not found"]);
}
}
?>
