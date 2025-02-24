<?php
include 'conn.php';

// 检查是否收到 'BloodCircR_ID' 参数
if (isset($_GET['BloodCircR_ID'])) {
    $BloodCircR_ID = $_GET['BloodCircR_ID'];

    $sql = "SELECT * FROM rbp_info WHERE BloodCircR_ID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $BloodCircR_ID);
    $stmt->execute();
    $result = $stmt->get_result();

    $rbp = array();
    $Column_one = array();
    $Column_two = array();
    $Column_three = array();

    while ($row = $result->fetch_assoc()) {
        $rbp[] = $row['RBP'];
        $Column_one[] = $row['Binding_sites'];

    }

    $response = array(
        "rbp" => $rbp,
        "Column_one" => $Column_one,
        "Column_two" => $Column_two,
        "Column_three" => $Column_three
    );

    echo json_encode($response);
    
    $stmt->close();
} else {
    echo "BloodCircR_ID parameter is missing";
}

$conn->close();
?>
