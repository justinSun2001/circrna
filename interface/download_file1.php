<?php
if(isset($_GET['fileParam'])){
    $fileParam = $_GET['fileParam'];
    $filePath = '/data2/shaoxun/BloodCircleR/Expression/data/User_upload/Example/'.$fileParam.'.txt';  // 根据参数构建文件路径

    if (file_exists($filePath)) {
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="'. basename($filePath). '"');
        readfile($filePath);
        exit;
    } else {
        echo "文件不存在";
    }
} else {
    echo "未提供有效的文件参数";
}
?>