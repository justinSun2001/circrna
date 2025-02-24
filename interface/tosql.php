<?php
// 数据库配置
$host = '10.120.53.201:3306';
$dbname = 'dbatlas';
$user = 'root';
$pass = '123456';

// 创建数据库连接
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("数据库连接失败: " . $e->getMessage());
}

// SVG 文件目录
$svgDir = '/data2/baixue/atlas/新文件夹';

// 遍历目录中的 SVG 文件
foreach (glob($svgDir . '/*.svg') as $filePath) {
    $datasetName = pathinfo($filePath, PATHINFO_FILENAME); // 文件名作为 Dataset 名称
    $svgContent = file_get_contents($filePath);

    // 更新数据库中的 image2 列
   $sql = "UPDATE sample_detection SET image2 = :image2 WHERE Dataset = :dataset";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':dataset', $datasetName);
    $stmt->bindParam(':image2', $svgContent, PDO::PARAM_LOB); // 更新 image2 列

    try {
        $stmt->execute();
        if ($stmt->rowCount() > 0) {
            echo "成功更新: $datasetName\n";
        } else {
            echo "未找到对应的 Dataset: $datasetName\n";
        }
    } catch (PDOException $e) {
        echo "更新失败: " . $e->getMessage() . "\n";
    }
}

// 关闭数据库连接
$pdo = null;
?>
