<?php
// 数据库配置


// 创建数据库连接
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "数据库连接成功\n";
} catch (PDOException $e) {
    die("数据库连接失败: " . $e->getMessage());
}

// 指定图片所在目录
$directory = '/data2/baixue/atlas/新图/Pre-sample第一部分表达分图';

// 打开目录并读取文件名
if ($handle = opendir($directory)) {
    while (false !== ($file = readdir($handle))) {
        if (pathinfo($file, PATHINFO_EXTENSION) == 'svg') {
            $filePath = $directory . '/' . $file;

            // 准备数据库插入操作
            $sql = "INSERT INTO sample_expression (Dataset, image_path, value) 
                    VALUES (:dataset, :image_path, :value)";
            $stmt = $pdo->prepare($sql);

            // 提取 Dataset 和 value
            if (preg_match('/Plot\.PRJNA(\d+)\.HVG(\d+)\.svg/', $file, $matches)) {
                $dataset = 'PRJNA' . $matches[1];
                $value = $matches[2];
            } else {
                echo "文件名不符合预期格式: $file\n";
                continue;
            }

            $stmt->bindParam(':dataset', $dataset);
            $stmt->bindParam(':image_path', $filePath);
            $stmt->bindParam(':value', $value);

            try {
                $stmt->execute();
                echo "文件 '$file' 已成功插入到数据库中。\n";
            } catch (PDOException $e) {
                echo "插入文件 '$file' 时出错: " . $e->getMessage() . "\n";
            }
        }
    }
    closedir($handle);
} else {
    die("无法打开目录: $directory");
}

$pdo = null;
echo "数据库连接已关闭\n";
?>