<?php
include_once('conn.php'); // 包含数据库连接文件
include 'run_r_script.php'; // 包含R脚本路径

function checkDatasetExists($datasetname, $table) {
    global $conn; // 引入全局的$conn变量到函数作用域内
    $sqlCreateTable = "CREATE TABLE IF NOT EXISTS $table (
        `Dataset` VARCHAR(255),
        `image` LONGBLOB
    )";
    if ($conn->query($sqlCreateTable) === false) {
        die("创建表时出错: " . $conn->error);
    }
    $sql = "SELECT COUNT(*) FROM $table WHERE Dataset = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $datasetname);
    $stmt->execute();
    $result = $stmt->get_result();
    $count = $result->fetch_row()[0];
    $stmt->close();
    return $count > 0;
}

function writeDB($datasetname, $param1, $table) {
    global $conn; // 引入全局的$conn变量到函数作用域内
    $file = $param1 . $table . '.svg';
    
    // 检查文件是否存在
    if (!file_exists($file)) {
        echo "文件不存在。";
        return;
    }

    // 读取文件内容
    $data = file_get_contents($file);
    if ($data === false) {
        echo "读取文件内容失败。";
        return;
    }
    
    $sql = "CREATE TABLE IF NOT EXISTS $table (
        `Dataset` VARCHAR(255),
        `image` LONGBLOB
    )";
    $conn->query($sql);

    $insert_sql = "INSERT INTO $table (Dataset, image) VALUES (?, ?)";
    $stmt = $conn->prepare($insert_sql);
    $null = NULL;
    $stmt->bind_param("sb", $datasetname, $null);
    $stmt->send_long_data(1, $data);
    $stmt->execute();
    $conn->commit();
    $stmt->close();

    if (file_exists($file)) {
        unlink($file);
    }
}

function fetchImagesByDatasetName($datasetname, $table) {
    global $conn; // 引入全局的$conn变量到函数作用域内
    $sql = "SELECT image FROM $table WHERE Dataset = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $datasetname);
    $stmt->execute();
    $result = $stmt->get_result();
    $images_data = $result->fetch_assoc();
    $stmt->close();
    $conn->close();
    return $images_data;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $datasetname = $_GET['datasetname'];
    $mode = $_GET['mode']; // 获取模式参数
    
    // 定义模式参数
    $validModes = ['differential_expression_1', 'differential_expression_2', 'differential_expression_3', 'differential_expression_4'];
    if (!in_array($mode, $validModes)) {
        http_response_code(400);
        echo json_encode(["error" => "Invalid mode"]);
        exit;
    }

    $tableName = $mode;

    $rScriptPath = $rScriptDir . $mode . ".R"; // R脚本路径根据模式动态变化
    $command = "{$rscriptPath} {$rScriptPath} '{$param1}' {$datasetname} 2>&1";


    // 检查是否此数据集已存在
    if (checkDatasetExists($datasetname, $tableName)) {
        $images_data = fetchImagesByDatasetName($datasetname, $tableName);
        if ($images_data) {
            $img_base64 = base64_encode($images_data['image']);
            http_response_code(200);
            header('Content-Type: application/json');
            echo json_encode(["image" => $img_base64]);
        } else {
            http_response_code(404);
            header('Content-Type: application/json');
            echo json_encode(["error" => "Dataset not found"]);
        }
    } else {
        // 数据集不存在的情况
        $output = [];
        $return_var = 0;
        exec($command, $output, $return_var);
        if ($return_var !== 0) {
            echo "执行R脚本时出错。返回状态: " . $return_var . "<br>";
            echo "输出: " . implode("<br>", $output);
        } else {
            writeDB($datasetname, $param1, $tableName);
            $images_data = fetchImagesByDatasetName($datasetname, $tableName);
            if ($images_data) {
                $img_base64 = base64_encode($images_data['image']);
                http_response_code(200);
                header('Content-Type: application/json');
                echo json_encode(["image" => $img_base64]);
            } else {
                http_response_code(404);
                header('Content-Type: application/json');
                echo json_encode(["error" => "Dataset not found"]);
            }
        }
    }
}
?>
