<?php
include_once('conn.php'); // 包含数据库连接文件
// CREATE TABLE contact (
//     id INT(11) AUTO_INCREMENT PRIMARY KEY,
//     name VARCHAR(255),
//     email VARCHAR(255) NOT NULL,
//     questions TEXT
// );

//     dataset_name VARCHAR(255) ,
//     dataset_link VARCHAR(255) NOT NULL,
//     pubmed_id VARCHAR(255) NOT NULL,
//     disease_name VARCHAR(255) NOT NULL,
//     phenotype VARCHAR(255),
//     species VARCHAR(255) NOT NULL,
//     tissue_type VARCHAR(255) NOT NULL,
//     data_type VARCHAR(255) NOT NULL,
//     dataset_description TEXT,

// 检查是否收到了POST请求
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 获取POST数据
    $First_name =$_GET['First_name'] ?? '';
	$Last_name =$_GET['Last_name'] ?? '';
    $Email =$_GET['Email'] ?? '';
	 $Title =$_GET['Title'] ?? '';
	  $Organization =$_GET['Organization'] ?? '';
    // $dataset_name =$_GET['dataset_name'] ?? '';
    // $dataset_link =$_GET['dataset_link'] ?? '';
    // $pubmed_id =$_GET['pubmed_id'] ?? '';
    // $disease_name =$_GET['disease_name'] ?? '';
    // $phenotype =$_GET['phenotype'] ?? '';
    // $species =$_GET['species'] ?? '';
    // $tissue_type =$_GET['tissue_type'] ?? '';
    // $data_type =$_GET['data_type'] ?? '';
    // $dataset_description =$_GET['dataset_description'] ?? '';
    $questions =$_GET['questions'] ?? '';

    // 准备SQL语句
    $sql = "INSERT INTO contact (First_name,Last_name,Email, Title,Organization,questions)
            VALUES (?, ?, ?, ?, ?, ?)";

    // 为mysqli预处理语句准备SQL语句
    if ($stmt =$conn->prepare($sql)) {
        // 绑定参数到预处理语句
        $stmt->bind_param("ssssss",$First_name, $Last_name,$Email,$Title, $Organization,$questions);

        // 执行预处理语句
        if ($stmt->execute()) {
            $response = array('status' => 'success', 'message' => 'New record created successfully');
        } else {
            $response = array('status' => 'error', 'message' => 'Error: ' .$stmt->error);
        }

        $stmt->close();
    } else {
        $response = array('status' => 'error', 'message' => 'Error: ' .$conn->error);
    }

    $conn->close();
    // 将响应编码为JSON并输出
    echo json_encode($response);
} else {
    echo json_encode(array('status' => 'error', 'message' => 'Invalid request'));
}
?>