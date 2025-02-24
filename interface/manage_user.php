<?php
include_once('conn.php'); // 包含数据库连接文件

$action = isset($_GET['action']) ? $_GET['action'] : '';
$email = isset($_GET['email']) ? $_GET['email'] : '';

switch($action) {
    case 'get':
        handleGetData();
        break;
    case 'update':
        $password = isset($_GET['password']) ? $_GET['password'] : '';
        $name = isset($_GET['name']) ? $_GET['name'] : '';
        $sex = isset($_GET['sex']) ? $_GET['sex'] : '';
        $birthday = isset($_GET['birthday']) ? $_GET['birthday'] : '';
        $career = isset($_GET['career']) ? $_GET['career'] : '';
        $address = isset($_GET['address']) ? $_GET['address'] : '';
        handleUpdateData($password, $name, $sex, $birthday, $career, $address);
        break;
    default:
        echo json_encode([]);
        break;
}

function handleGetData() {
    global $conn, $email;
    $sql = "SELECT * FROM users WHERE email = '$email'";
    $result = mysqli_query($conn, $sql);
    $data = [];
    if ($result && mysqli_num_rows($result) > 0) {
        $data = mysqli_fetch_assoc($result);
    }
    echo json_encode($data);
}

function handleUpdateData($password, $name, $sex, $birthday, $career, $address) {
    global $conn, $email;
    $sql = "UPDATE users SET password = '$password', name = '$name', sex = '$sex', birthday = '$birthday', career = '$career', address = '$address' WHERE email = '$email'";
    if (mysqli_query($conn, $sql)) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'failed', 'message' => mysqli_error($conn)]);
    }
}
?>
