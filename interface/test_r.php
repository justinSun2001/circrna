<?php
include_once('conn.php'); // 包含数据库连接文件
// 包含R脚本路径
include 'run_r_script.php';
$target_dir = "/data2/njucm1/BloodCircleR/Expression/data/";


// 获取前端传递的数据
$email = $_GET['email'];
$datasetname = $_GET['datasetname'];
$disease = $_GET['disease'];
$phenotype = $_GET['phenotype'];

$rScriptPath = $rScriptDir . "usr_data_convert.R";
// 构建R脚本执行命令
$command = "Rscript {$rScriptPath} '{$datasetname}'";
$output = [];
$return_var = 0;
exec($command, $output, $return_var);

// 错误输出测试
if ($return_var !== 0) {
    echo "执行R脚本时出错。脚本路径: " . $rScriptPath . "<br>";
    echo "返回状态: " . $return_var . "<br>";
    echo "输出: " . implode("<br>", $output);
    return;
} else {
    // 通过R生成的txt文件获取Group信息
    // 定义文件路径
    $sampletableFile = $target_dir ."User_upload/". $datasetname . "/" . $datasetname . "_SampleInfo.txt";
    // 检查文件是否存在
    if (!file_exists($sampletableFile)) {
        die("Error: The file '$sampletableFile' does not exist.");
    }
    // 读取文件并处理异常
    $lines = @file($sampletableFile, FILE_IGNORE_NEW_LINES);
    if ($lines === false) {
        die("Error: Failed to read the file '$sampletableFile'.");
    }
    // 检查文件是否为空
    if (empty($lines)) {
        die("Error: The file '$sampletableFile' is empty.");
    }
    // 解析文件头部
    $header = str_getcsv(array_shift($lines), "\t");
    if (empty($header)) {
        die("Error: Failed to parse the header row.");
    }
    // 检查'group'列是否存在
    if (!in_array('group', $header)) {
        die("Error: The 'group' column is missing in the file.");
    }
    $data = [];
    foreach ($lines as $line) {
        // 将每一行解析为关联数组并检查是否成功
        $row = str_getcsv($line, "\t");
        if (count($row) != count($header)) {
            die("Error: Data row does not match header length.");
        }
        $data[] = array_combine($header, $row);
    }
    // 提取group列的unique值并进行检查
    $groups = array_unique(array_column($data, 'group'));
    if (empty($groups)) {
        die("Error: No unique groups found.");
    }
    // 两两组合，以_为分割符，并避免重复和自身组合
    $combinations = [];
    foreach ($groups as $key1 => $group1) {
        foreach ($groups as $key2 => $group2) {
            if ($key1 < $key2) {
                $combinations[] = $group1 . "_" . $group2;
            }
        }
    }
    // 组合后的以,为分割符
    $group = implode(" vs ", array_unique($combinations));

    // 使用准备好的语句插入数据到 user_data 表
    $stmt = $conn->prepare("INSERT INTO user_data (email, filename1, filename2, filename3, filename4, Dataset, Disease, Phenotype, `Group`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    $filename1 = $datasetname . "_SampleInfo.txt";
    $filename2 = $datasetname . "_IsoformInfo.txt";
    $filename3 = $datasetname . "_CountMatrix.txt";
    $filename4 = $datasetname . "_TPMMatrix.txt";

    $stmt->bind_param("sssssssss", $email, $filename1, $filename2, $filename3, $filename4, $datasetname, $disease, $phenotype, $group);


    if ($stmt->execute() === TRUE) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => $stmt->error]);
    }

    $stmt->close();
}
?>