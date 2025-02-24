<?php

set_time_limit(0); // 设置脚本无限制执行时间
ini_set('max_execution_time', 0); // 设置最大执行时间为0，表示无限制

include_once('conn.php'); // 包含数据库连接文件
// 包含R脚本路径
include 'run_r_script.php';
// 根据数据集名称获取图像
function fetchImageBySql($tablename, $datasetname) {
    global $conn; // 引入全局的$conn变量到函数作用域内

    $sql = "SELECT image1 FROM $tablename WHERE Dataset = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $datasetname);
    $stmt->execute();
    $result = $stmt->get_result();
    $images_data = $result->fetch_assoc();
    $stmt->close();

    return $images_data;
}
function fetchImageByFile($param1, $tablename) {
    $file1 = $param1 . $tablename . '.svg';
    // 检查文件是否存在
    if (!file_exists($file1)) {
        echo "一个或多个文件不存在。";
        return;
    }
    // 读取文件内容
    $images_data = file_get_contents($file1);

    return $images_data;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tablename = $_GET['tablename'];
    $datasetname = $_GET['datasetname'];
    $method = $_GET['method'];
    if (empty($tablename) || empty($datasetname) || empty($method)) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing tablename, datasetname, or method']);
        exit();
    }
    if ($method === 'sql') {
        $images_data = fetchImageBySql($tablename, $datasetname);

        if ($images_data && isset($images_data['image1'])) {

            // 将图像数据转换为Base64编码
            $img1_base64 = base64_encode($images_data['image1']);
            // 返回JSON响应，包含两个Base64编码的图像数据
            http_response_code(200);
            header('Content-Type: application/json');
            echo json_encode(["image1" => $img1_base64]);
            exit();
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'No image data found']);
            exit();
        }
    } elseif ($method === 'file') {
        $images_data = fetchImageByFile($param1,$tablename);

        if ($images_data) {

            // 返回图像内容
            // 将图像数据转换为Base64编码
            $img1_base64 = base64_encode($images_data);
            // 返回JSON响应，包含两个Base64编码的图像数据
            http_response_code(200);
            header('Content-Type: application/json');
            echo json_encode(["image1" => $img1_base64]);
            exit();
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'File not found']);
            exit();
        }
    } else {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid method']);
        exit();
    }
}
?>
