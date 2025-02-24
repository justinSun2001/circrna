<?php
include_once('conn.php'); // 包含数据库连接文件

// 接收POST数据
$email =$_GET['email'];
$password =$_GET['password'];


// 验证用户信息
// 验证邮箱地址是否已存在
$sql = "SELECT * FROM users WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s",$email);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    // 验证密码是否正确
    $sql = "SELECT * FROM users WHERE email = ? AND password = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss",$email, $password);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        // 密码正确，登录成功
         // 登录成功
        $response = array('status' => 'success', 'message' => 'Login successful!');
    } else {
        // 登录失败
        $response = array('status' => 'error', 'message' => 'Error password!');
    }
} else {
    // 邮箱地址不存在
    $response = array('status' => 'error', 'message' => 'No account found with this email. Please sign up first.');
}
$stmt->close();
$conn->close();
// 将响应编码为JSON并输出
echo json_encode($response);
?>
