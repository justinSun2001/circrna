<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
// 引入PHPMailer的核心文件
require_once("PHPMailer/src/PHPMailer.php");
require_once("PHPMailer/src/SMTP.php");
require_once("PHPMailer/src/Exception.php");
include_once('conn.php'); // 包含数据库连接文件
// CREATE TABLE users (
//     id INT(11) AUTO_INCREMENT PRIMARY KEY,
//     email VARCHAR(255) NOT NULL UNIQUE,
//     password VARCHAR(255) NOT NULL,
//     name VARCHAR(255),
//     sex VARCHAR(255),
//     birthday VARCHAR(255),
//     career VARCHAR(255),
//     address VARCHAR(255)
// );
// 接收POST数据
$email =$_GET['email'];
$password =$_GET['password'];

// 验证邮箱地址是否已存在
$sql = "SELECT * FROM users WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s",$email);
$stmt->execute();$result = $stmt->get_result();

// 检查是否有匹配的邮箱地址
if ($result->num_rows > 0) {
    // 邮箱地址已存在
    $response = array('status' => 'error', 'message' => 'An account with this email already exists. Please log in instead.');
} else {
        // 注册成功，发送邮件
        // 实例化PHPMailer核心类
        $mail = new PHPMailer();
        // 是否启用smtp的debug进行调试 开发环境建议开启 生产环境注释掉即可 默认关闭debug调试模式
        // $mail->SMTPDebug = 1;
        // 使用smtp鉴权方式发送邮件
        $mail->isSMTP();
        // smtp需要鉴权 这个必须是true
        $mail->SMTPAuth = true;
        // 链接qq域名邮箱的服务器地址
        $mail->Host = 'smtp.qq.com';
        // 设置使用ssl加密方式登录鉴权
        $mail->SMTPSecure = 'ssl';
        // 设置ssl连接smtp服务器的远程服务器端口号
        $mail->Port = 465;
        // 设置发送的邮件的编码
        $mail->CharSet = 'UTF-8';
        // 设置发件人昵称 显示在收件人邮件的发件人邮箱地址前的发件人姓名
        $mail->FromName = 'BloodCircR';
        // smtp登录的账号 QQ邮箱即可
        $mail->Username = '1442261496@qq.com';
        // smtp登录的密码 使用生成的授权码
        $mail->Password = 'gxaiyqyphggkggdc';
        // 设置发件人邮箱地址 同登录账号
        $mail->From = '1442261496@qq.com';
        // 邮件正文是否为html编码 注意此处是一个方法
        $mail->isHTML(true);
        // 设置收件人邮箱地址
        $mail->addAddress($email);
        // 添加多个收件人 则多次调用方法即可
        // $mail->addAddress('87654321@163.com');
        // 添加该邮件的主题
        $mail->Subject = 'Welcome to BloodCircR - The Human Blood Circular RNA Database!';
        // 添加邮件正文
        $mail->Body = '<h1>Dear [' . $email . '],</h1>
        <p>You are now part of <b>BloodCircR</b>, the world\'s largest database of blood circular RNAs (circRNAs). Welcome, we are glad to have you join us!</p>
        <p>Whether you are new to circRNA or an expert in the field, we want to support you with powerful tools and resources to help you grow in the field of data science.</p>
        <p><b>So, where\'s the best place to start?</b></p>
        <p>We highly recommend exploring our blood circular RNA database. You will be able to analyze the expression of circRNAs from human blood samples derived from RNA-seq studies.</p>
        <p style="text-align:center;"><a href="http://10.120.53.201:8002/">Get Started Now!</a></p>
        <p>This is a fun and engaging way to gain insight into how our platform works. You will become more familiar with how our online coding environment, open datasets, and open pretrained models work together to help you build data science projects.</p>
        <p>Thank you again for joining us, and we look forward to your exciting journey with BloodCircR!</p>
        <p>Best regards,<br>The BloodCircR Team</p>';


        // 为该邮件添加附件
        // $mail->addAttachment('./example.pdf'); 
        // 发送邮件 返回状态
        $status = $mail->send();
        
        if ($status) {
            // 邮箱地址可用，添加新用户到数据库
            $sql = "INSERT INTO users (email, password) VALUES (?, ?)";
            $stmt =$conn->prepare($sql);
            $stmt->bind_param("ss",$email, $password);
            if ($stmt->execute()) {
                $response = array('status' => 'success', 'message' => 'Registration successful!');
            }
            else{
                $response = array('status' => 'error', 'message' => 'Failed to register user.');
            }
            $stmt->close();
        } else {
            $response = array('status' => 'error', 'message' => 'Failed to send email.');
        }
}

$conn->close();
// 将响应编码为JSON并输出
echo json_encode($response);
?>
