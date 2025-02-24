<?php
set_time_limit(0); // 设置脚本无限制执行时间
ini_set('max_execution_time', 0); // 设置最大执行时间为0，表示无限制


if ($_SERVER['REQUEST_METHOD'] === 'POST'){

$datasetname = $_GET['datasetname'];
// 根据参数构建图片路径
$imagePath1 = "/Users/jutsinsun/Downloads/circRNALevel1/Plot.{$datasetname}.svg"; // 根据实际情况修改扩展名
$imagePath2 = "/Users/jutsinsun/Downloads/circRNALevel2/Plot.{$datasetname}.svg"; // 根据实际情况修改扩展名

// 检查文件是否存在并读取数据
$images_data = [];
if (file_exists($imagePath1)) {
    $images_data['image1'] = file_get_contents($imagePath1);
} else {
    http_response_code(404);
    header('Content-Type: application/json');
    echo json_encode(["error" => "Image1 not found"]);
    exit();
}

if (file_exists($imagePath2)) {
    $images_data['image2'] = file_get_contents($imagePath2);
} else {
    http_response_code(404);
    header('Content-Type: application/json');
    echo json_encode(["error" => "Image2 not found"]);
    exit();
}

// 将图像数据转换为Base64编码
$img1_base64 = base64_encode($images_data['image1']);
$img2_base64 = base64_encode($images_data['image2']);

// 返回JSON响应，包含两个Base64编码的图像数据
http_response_code(200);
header('Content-Type: application/json');
echo json_encode(["image1" => $img1_base64, "image2" => $img2_base64]);
}
?>
