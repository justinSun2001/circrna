<?php
set_time_limit(0); // 设置脚本无限制执行时间
ini_set('max_execution_time', 0); // 设置最大执行时间为0，表示无限制

include_once('conn.php'); // 包含数据库连接文件
// 包含R脚本路径
include 'run_r_script.php';

function writeDB($datasetname) {
    global $conn; // 引入全局的$conn变量到函数作用域内
	$file1 = '/data2/njucm1/BloodCircleR/Expression/cache/differential_expression_4.svg';  
	$file2 = '/data2/njucm1/BloodCircleR/Expression/cache/differential_expression_4.csv';
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
    $sql = "CREATE TABLE IF NOT EXISTS differential_expression_4 (
        `Dataset` VARCHAR(255) UNIQUE,
        `image1` LONGBLOB,
        `data1` LONGBLOB
    )";
    $conn->query($sql);

    // 使用 INSERT ... ON DUPLICATE KEY UPDATE 语法插入或更新记录
    $insert_sql = "INSERT INTO differential_expression_4 (Dataset, image1, data1) VALUES (?, ?, ?)
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
    $sql = "SELECT image1, data1 FROM differential_expression_4 WHERE Dataset = ?";
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
$param3 = $_GET['compare'];
$param4 = $_GET['pvalue'];
$param5 = $_GET['FoldChange'];
$param6 = $_GET['detectR'];
$param7 = $_GET['type'];

//$param9 = $_GET['number'];

$rScriptPath = $rScriptDir."differential_expression_4.R"; // R脚本的路径
// 构建Rscript命令
   $command = "Rscript {$rScriptPath}  '{$param2}' '{$param3}' '{$param4}' '{$param5}' '{$param6}' '{$param7}' 2>&1";



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

// set_time_limit(0); // 设置脚本无限制执行时间
// ini_set('max_execution_time', 0); // 设置最大执行时间为0，表示无限制

// include_once('conn.php'); // 包含数据库连接文件
// include 'run_r_script.php'; // 包含R脚本路径

// // 创建数据库表格并确保 Dataset, pvalue, FoldChange, compare, type 的组合是唯一键
// function createTableIfNotExists() {
//     global $conn;
//     $sql = "CREATE TABLE IF NOT EXISTS differential_expression_4 (
//         `Dataset` VARCHAR(255),
//         `pvalue` VARCHAR(255) NOT NULL,
//         `FoldChange` VARCHAR(255) NOT NULL,
//         `compare` VARCHAR(255) NOT NULL,
//         `type` VARCHAR(255) NOT NULL,
//         `json_data` JSON NOT NULL
//     )";
//     $conn->query($sql);
// }


// // 判断数据库中是否已经存在对应的 JSON 文件
// function checkIfExists($datasetname, $pvalue, $foldchange, $compare, $type) {
//     global $conn;
//     // 使用字符串形式存储和比较
//     $sql = "SELECT json_data FROM differential_expression_4 WHERE Dataset = ? AND pvalue = ? AND FoldChange = ? AND `compare` = ? AND `type` = ?";
//     $stmt = $conn->prepare($sql);
//     // 检查 prepare 是否成功
//     if ($stmt === false) {
//         // 输出错误信息或记录日志
//         echo "Prepare failed: (" . $conn->errno . ") " . $conn->error;
//         return null; // 返回 null 表示出错
//     }
//     $stmt->bind_param("sssss", $datasetname, $pvalue, $foldchange, $compare, $type);
//     $stmt->execute();
//     $result = $stmt->get_result();
//     $json_data = $result->fetch_assoc();
//     $stmt->close();
//     return $json_data;
// }

// function writeDB($datasetname, $param1, $pvalue, $foldchange, $compare, $type) {
//     global $conn;
//     $file1 = $param1 . 'differential_expression_4.json';

//     if (!file_exists($file1)) {
//         echo "文件不存在。";
//         return;
//     }

//     $data1 = file_get_contents($file1);

//     if ($data1 === false) {
//         echo "读取文件内容失败。";
//         return;
//     }

//     // 假设 $data1 包含有效的 JSON 数据
//     $insert_sql = "INSERT INTO differential_expression_4 (Dataset, pvalue, FoldChange, `compare`, `type`, json_data) VALUES (?, ?, ?, ?, ?, ?)";
//     $stmt = $conn->prepare($insert_sql);

//     // 检查 prepare 是否成功
//     if ($stmt === false) {
//         echo "Prepare failed: (" . $conn->errno . ") " . $conn->error;
//         exit;
//     }

//     // 绑定参数并执行插入操作
//     $stmt->bind_param("ssssss", $datasetname, $pvalue, $foldchange, $compare, $type, $data1);

//     // 执行插入操作
//     $result = $stmt->execute();

//     // 检查执行结果
//     if ($result === false) {
//         echo "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
//         exit;
//     }

//     $stmt->close();

//     // 如果需要，可以添加成功插入后的进一步处理代码


//     // if (file_exists($file1)) {
//     //     unlink($file1);
//     // }
// }

// if ($_SERVER['REQUEST_METHOD'] === 'POST') {
//     createTableIfNotExists();
//     $datasetname = $_GET['datasetname'];
//     $param2 = $datasetname;
//     $pvalue = (string) $_GET['pvalue']; // 将 pvalue 转换为字符串
//     $foldchange = (string) $_GET['FoldChange']; // 将 FoldChange 转换为字符串

//     $compare = $_GET['compare'];
//     $type = $_GET['type'];

//     $existing_data = checkIfExists($datasetname, $pvalue, $foldchange, $compare, $type);
//     if ($existing_data) {
//         http_response_code(200);
//         header('Content-Type: application/json');
//         echo json_encode(["json_data" => $existing_data]);
//         exit;
//     }

//     $rScriptPath = $rScriptDir . "differential_expression_4.R";
//     $command = "{$rscriptPath} {$rScriptPath} '{$param1}' {$param2} {$pvalue} {$foldchange} '{$rScriptDir}' {$compare} {$type}";

//     $output = [];
//     $return_var = 0;
//     exec($command, $output, $return_var);

//     if ($return_var !== 0) {
//         echo "执行R脚本时出错。返回状态: " . $return_var . "<br>";
//         echo "输出: " . implode("<br>", $output);
//     } else {
//         // echo "R脚本输出: " . implode("<br>", $output);
//     }

//     writeDB($datasetname, $param1, $pvalue, $foldchange, $compare, $type);

//     $json_data = checkIfExists($datasetname, $pvalue, $foldchange, $compare, $type);
//     if ($json_data) {
//         http_response_code(200);
//         header('Content-Type: application/json');
//         echo json_encode(["json_data" => $json_data]);
//     } else {
//         http_response_code(404);
//         header('Content-Type: application/json');
//         echo json_encode(["error" => "Dataset not found"]);
//     }
// }
?>
