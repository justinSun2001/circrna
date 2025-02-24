<?php

include_once('conn.php');

// 检查是否有 DatasetID 参数传递过来
if (isset($_GET['DatasetID'])) {
    $DatasetID = $_GET['DatasetID'];

    // 使用参数化查询
    $sql = "SELECT Dataset FROM dataset_info2 WHERE DatasetID =?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $DatasetID);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        echo $row['Dataset'];
    } else {
        echo "No dataset found for the given DatasetID.";
    }

    $stmt->close();
} else {
    echo "DatasetID not provided.";
}

$conn->close();

?>