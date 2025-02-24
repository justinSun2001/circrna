<?php

include_once('conn.php');

// 获取所有的 dataset 和 studyid 列的数据
$sql_all_data = "SELECT StudyID, Dataset FROM dataset_info2";
$result_all_data = $conn->query($sql_all_data);

$data_array = array();
if ($result_all_data->num_rows > 0) {
    while ($row = $result_all_data->fetch_assoc()) {
        $studyIDs = explode('、', $row['StudyID']);
        foreach ($studyIDs as $studyID) {
            $data_array[$studyID] = $row['Dataset'];
        }
    }
}

// 检查是否有 studyID 参数传递过来
if (isset($_GET['StudyID'])) {
    $StudyID = $_GET['StudyID'];

    if (isset($data_array[$StudyID])) {
        echo $data_array[$StudyID];
    } else {
        echo "No dataset found for the given StudyID.";
    }
} else {
    echo "StudyID not provided.";
}


$conn->close();

?>