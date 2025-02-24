<?php
set_time_limit(0); // 设置脚本无限制执行时间
ini_set('max_execution_time', 0); // 设置最大执行时间为0，表示无限制

include_once('conn.php'); // 包含数据库连接文件
// 包含R脚本路径
include 'run_r_script.php';
// 检查数据集是否存在
function checkDatasetExists($datasetname) {
    global $conn; // 引入全局的$conn变量到函数作用域内
    // 创建表的SQL语句
    $sqlCreateTable = "CREATE TABLE IF NOT EXISTS circRNA_characteristics (
        `Dataset` VARCHAR(255),
        `image1` LONGBLOB
    )";
    if ($conn->query($sqlCreateTable) === false) {
        die("创建表时出错: " . $conn->error);
    }
    $sql = "SELECT COUNT(*) FROM circRNA_characteristics WHERE Dataset = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $datasetname);
    $stmt->execute();
    $result = $stmt->get_result();
    $count = $result->fetch_row()[0];
    $stmt->close();
    return $count > 0;
}

// 写入数据库
function writeDB($datasetname, $param1) {
    global $conn; // 引入全局的$conn变量到函数作用域内

    $file1 = $param1 . 'circRNA_characteristics.svg';
    
    // 检查文件是否存在
    if (!file_exists($file1)) {
        echo "一个或多个文件不存在。";
        return;
    }

    // 读取文件内容
    $data1 = file_get_contents($file1);

    // 检查数据长度
    // echo "data1长度: " . strlen($data1) . "<br>";

    // 确认数据读取成功
    if ($data1 === false) {
        echo "读取文件内容失败。";
        return;
    }
    $sql = "CREATE TABLE IF NOT EXISTS circRNA_characteristics (
        `Dataset` VARCHAR(255),
        `image1` LONGBLOB,
    )";
    $conn->query($sql);

    $insert_sql = "INSERT INTO circRNA_characteristics (Dataset, image1) VALUES (?, ?)";
    $stmt = $conn->prepare($insert_sql);
    // $stmt->bind_param("sb", $datasetname, $data1);
    // 使用长字符串时, 需要使用 'b' 绑定参数
    $null = NULL;
    $stmt->bind_param("sb", $datasetname, $null);

    $stmt->send_long_data(1, $data1);
    $stmt->execute();
    $conn->commit();
    $stmt->close();

    // 删除文件
    if (file_exists($file1)) {
        unlink($file1);
    }
}

// 根据数据集名称获取图像
function fetchImagesByDatasetName($datasetname) {
    global $conn; // 引入全局的$conn变量到函数作用域内
    $sql = "SELECT image1 FROM circRNA_characteristics WHERE Dataset = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $datasetname);
    $stmt->execute();
    $result = $stmt->get_result();
    $images_data = $result->fetch_assoc();
    $stmt->close();
    $conn->close();
    return $images_data;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST'){

$datasetname = $_GET['datasetname'];
// print_r($datasetname);

// 准备参数(项目编号)
$param2 = $datasetname;
// 构建R脚本执行命令，参数通过命令行传给R脚本
$rScriptPath = $rScriptDir ."circRNA_characteristics.R"; // R脚本的路径
$command = "{$rscriptPath} {$rScriptPath} '{$param1}' {$param2} 2>&1";

// 检查是否此数据集已存在
if (checkDatasetExists($datasetname)) {
    // echo "Dataset '{$datasetname}' already exists in the table. Skipping insertion.";
    // 获取数据
    $images_data = fetchImagesByDatasetName($datasetname);
    if ($images_data) {
        // 将图像数据转换为Base64编码
        $img1_base64 = base64_encode($images_data['image1']);
        // 返回JSON响应，包含两个Base64编码的图像数据
        http_response_code(200);
        header('Content-Type: application/json');
        echo json_encode(["image1" => $img1_base64]);
    } else {
        http_response_code(404);
        header('Content-Type: application/json');
        echo json_encode(["error" => "Dataset not found1"]);
    }
} else {
    // 数据集不存在的情况
    // 生成图表（假设有一个方法来生成图表）
    // // 执行R脚本并捕获输出
    // shell_exec($command);
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
    writeDB($datasetname, $param1);
    // 获取数据
    $images_data = fetchImagesByDatasetName($datasetname);
    if ($images_data) {
        // 将图像数据转换为Base64编码
        $img1_base64 = base64_encode($images_data['image1']);
        // 返回JSON响应，包含两个Base64编码的图像数据
        http_response_code(200);
        header('Content-Type: application/json');
        echo json_encode(["image1" => $img1_base64]);
    } else {
        http_response_code(404);
        header('Content-Type: application/json');
        echo json_encode(["error" => "Dataset not found2"]);
    }
}
}
?>
