<?php
set_time_limit(0); // 设置脚本无限制执行时间
ini_set('max_execution_time', 0); // 设置最大执行时间为0，表示无限制

include_once('conn.php'); // 包含数据库连接文件
include 'run_r_script.php'; // 包含R脚本路径

// 检查数据集是否存在
function checkDatasetExists($datasetname) {
    global $conn; // 引入全局的$conn变量到函数作用域内
    $sqlCreateTable = "CREATE TABLE IF NOT EXISTS expression_1 (
        `Dataset` VARCHAR(255),
        `image1` LONGBLOB,
        `data1` LONGBLOB
    )";
    if ($conn->query($sqlCreateTable) === false) {
        die("创建表时出错: " . $conn->error);
    }
    $sql = "SELECT COUNT(*) FROM expression_1 WHERE Dataset = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $datasetname);
    $stmt->execute();
    $result = $stmt->get_result();
    $count = $result->fetch_row()[0];
    $stmt->close();
    return $count > 0;
}

// 运行 R 脚本并返回结果
function runRScript($datasetname) {
    global $rScriptDir;
  
    $rScriptPath = $rScriptDir . "expression_1.R"; // R脚本的路径
    $command = "Rscript {$rScriptPath} '{$datasetname}' 2>&1";

    $output = [];
    $return_var = 0;
    exec($command, $output, $return_var);
    if ($return_var !== 0) {
        echo "执行R脚本时出错。返回状态: " . $return_var . "<br>";
        echo "输出: " . implode("<br>", $output);
        return false;
    }
    return true;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $datasetname = $_GET['datasetname'];
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

    // 执行 R 脚本
    if (runRScript($datasetname)) {
        $file1 = '/data2/njucm1/BloodCircleR/Expression/cache/expression_1.svg';
        $file2 = '/data2/njucm1/BloodCircleR/Expression/cache/expression_1.csv';

        // 检查文件是否存在
        if (file_exists($file1) && file_exists($file2)) {
            // 读取文件内容
            $data1 = file_get_contents($file1);
            $data2 = file_get_contents($file2);

            // 确认数据读取成功
            if ($data1 !== false && $data2 !== false) {
                // 将图像数据转换为Base64编码
                $img1_base64 = base64_encode($data1);
                $csv1_base64 = base64_encode($data2);
                // 返回JSON响应，包含两个Base64编码的数据
                http_response_code(200);
                header('Content-Type: application/json');
                echo json_encode(["image1" => $img1_base64, "data1" => $csv1_base64]);
            } else {
                echo "读取文件内容失败。";
            }

            // 删除临时文件
            if (file_exists($file1)) {
                unlink($file1);
            }
            if (file_exists($file2)) {
                unlink($file2);
            }
        } else {
            echo "文件不存在。";
        }
    }
}
?>
