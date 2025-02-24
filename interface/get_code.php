<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
// 引入PHPMailer的核心文件
require_once("PHPMailer/src/PHPMailer.php");
require_once("PHPMailer/src/SMTP.php");
require_once("PHPMailer/src/Exception.php");
include_once('conn.php'); // 包含数据库连接文件
$action = isset($_GET['action']) ? $_GET['action'] : '';
// 接收POST数据
$email =$_GET['email'];
$verification_code = $_GET['verification_code'];
switch($action) {
    case 'register':
        register();
        break;
    case 'reset_password':
        reset_password();
        break;
    default:
        $response = array('status' => 'error', 'message' => 'null');
        echo json_encode($response);
}
function register(){
    global $conn;
    global $email;
    global $verification_code;
    // 发送邮件
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

    $mail->Subject = 'Your BloodCircR verification code';
    // 修改邮件正文，包含验证码
    $mail->Body = '<div style="border: 1px solid #ccc;"><h1 style="text-align:center">Here is your BloodCircR verification code!</h1>
        <p style="text-align:center">continue signing up for BloodCircR by entering the code below: </p>
        <p style="text-align:center; color:#B94A4A; text-decoration: underline; font-size: 24px;">' . $verification_code . '</p></div>';
    // 发送邮件 返回状态
    $status = $mail->send();
    if ($status) {
        // 发送成功
        $response = array('status' => 'success', 'message' => 'We have send the code to your email, Please check it!');
    } else {
        $response = array('status' => 'error', 'message' => 'Failed to send email.');
    }
    header('Content-Type: application/json');
    echo json_encode($response);
    $conn->close();
}

function reset_password(){
    global $conn;
    global $email;
    global $verification_code;
    
    // 验证邮箱地址是否已存在
    $sql = "SELECT * FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s",$email);
    $stmt->execute();
    $result = $stmt->get_result();

    // 检查是否有匹配的邮箱地址
    if ($result->num_rows > 0) {
        // 发送邮件
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

        $mail->Subject = 'Your BloodCircR verification code';
        // 修改邮件正文，包含验证码
        $mail->Body = '<div style="border: 1px solid #ccc;"><h1 style="text-align:center">Here is your BloodCircR verification code!</h1>
            <p style="text-align:center">continue signing up for BloodCircR by entering the code below: </p>
            <p style="text-align:center; color:#B94A4A; text-decoration: underline; font-size: 24px;">' . $verification_code . '</p></div>';
        // 发送邮件 返回状态
        $status = $mail->send();
        if ($status) {
            // 发送成功
            $response = array('status' => 'success', 'message' => 'We have send the code to your email, Please check it!');
        } else {
            $response = array('status' => 'error', 'message' => 'Failed to send email.');
        }
    } else {
        // 邮箱地址不存在，可以添加新用户
        $response = array('status' => 'error', 'message' => 'Email does not exist, you can register a new user!');
    }
    header('Content-Type: application/json');
    echo json_encode($response);
    $conn->close();

}

?>
