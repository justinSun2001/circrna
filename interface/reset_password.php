<?php
include_once('conn.php'); // 包含数据库连接文件

// 接收POST数据
$email =$_GET['email'];
$password =$_GET['password'];

// 验证邮箱地址是否已存在
$sql = "SELECT * FROM users WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s",$email);
$stmt->execute();
$result = $stmt->get_result();
// 检查是否有匹配的邮箱地址
if ($result->num_rows > 0) {
    // 邮箱地址存在，更新密码
    $sql = "UPDATE users SET password = ? WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss",$password,$email);
    $stmt->execute();
    $response = array('status' => 'success', 'message' => 'Password reset successfully.');
} else {
    $response = array('status' => 'error', 'message' => 'Failed to reset password.');
}
// 关闭数据库连接
$stmt->close();
$conn->close();
// 将响应编码为JSON并输出
echo json_encode($response);
?>
