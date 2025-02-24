<?php
   $servername = "localhost";
    $username = "root";
    $password = "bieber1994";
    $dbname = "dbatlas";
  

  


$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// 设置字符集为 utf8mb4
$conn->set_charset("utf8mb4");


?>