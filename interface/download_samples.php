<?php
if(isset($_GET['fileParam'])){
    $fileParam = $_GET['fileParam'];
    $filePath = '/home/shaoxun/BloodCircleR/sampletable/'.$fileParam;  // 根据参数构建文件路径
    if (file_exists($filePath)) {
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="'. basename($filePath). '"');
        readfile($filePath);
        exit;
    } else {
        // echo "File not found";
        echo $filePath;
    }
} else {
    echo "未提供有效的文件参数";
}
?>