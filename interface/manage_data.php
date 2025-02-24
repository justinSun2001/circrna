<?php
include_once('conn.php'); // 包含数据库连接文件
// 包含R脚本路径
include 'run_r_script.php';
$target_dir = "/data2/njucm1/BloodCircleR/Expression/data/";


/**
 * 递归删除目录及其所有内容
 *
 * @param string $dir 目录路径
 * @return bool 是否删除成功
 */
function deleteDirectory($dir)
{
    if (!file_exists($dir)) {
        return true; // 目录不存在，视为删除成功
    }

    if (!is_dir($dir)) {
        return unlink($dir); // 如果是文件，直接删除
    }

    // 遍历目录内容
    foreach (scandir($dir) as $item) {
        if ($item == '.' || $item == '..') {
            continue; // 跳过当前目录和上级目录
        }

        $path = $dir . '/' . $item;

        // 递归删除子目录或文件
        if (!deleteDirectory($path)) {
            return false; // 如果删除失败，返回 false
        }
    }

    // 删除空目录
    return rmdir($dir);
}
// 获取 action 参数
$action = isset($_GET['action']) ? $_GET['action'] : (isset($_POST['action']) ? $_POST['action'] : '');

switch ($action) {
    case 'upload':
        handleUpload();
        break;
    case 'submit':
        handleSubmit();
        break;
    case 'getdata':
        handleGetData();
        break;
    case 'delete':
        handleDelete();
        break;
    case 'modify':
        handleModifyData();
        break;
    default:
        echo json_encode(['status' => 'error', 'message' => $action]);
}

$conn->close();

function handleUpload()
{
    global $target_dir;
    $response = array();
    if (!is_dir($target_dir) || !is_writable($target_dir)) {
        $response['status'] = 'error';
        $response['message'] = 'Target directory is not writable';
        echo json_encode($response);
        return;
    }
    // 检查是否是分片上传
    if (isset($_POST['index']) && isset($_POST['chunkCount'])) {
        $index = intval($_POST['index']);
        $chunkCount = intval($_POST['chunkCount']);
        $fileName =  $_POST['fileName'];// 确保名称与前端一致

        // 为每个分片创建唯一的临时文件名
        $chunkFileName = $target_dir . $fileName . '_part' . $index . '.txt';

        // 如果所有分片都已上传完毕，进行合并
        if ($index === $chunkCount) {
            $finalFileName = $target_dir . $fileName . '.txt';
            if ($fp = fopen($finalFileName, 'w')) {
                for ($i = 0; $i < $chunkCount; $i++) {
                    $chunkFile = $target_dir . $fileName . '_part' . $i . '.txt';
                    // 等待文件生成
                    $maxRetries = 10; // 设置最大重试次数
                    $retries = 0;
                    while (!file_exists($chunkFile) && $retries < $maxRetries) {
                        usleep(500000); // 等待 0.5 秒
                        $retries++;
                    }   
                     // 如果文件存在则进行写入
                    if (file_exists($chunkFile)) {
                        fwrite($fp, file_get_contents($chunkFile));
                        unlink($chunkFile); // 删除临时分片文件
                    } else {
                        $response['status'] = 'error';
                        $response['message'] = "无法找到文件：$chunkFile";
                        echo json_encode($response);
                        fclose($fp);
                        return;
                    }
                }
            $response['status'] = 'success';
            $response['file'] = $fileName;
            $response['message'] = '分片上传成功';
            echo json_encode($response);
            }
            
        }else {
             // 保存分片到临时文件
            if (move_uploaded_file($_FILES['chunk']['tmp_name'], $chunkFileName)) {
                $response['status'] = 'success';
                $response['message'] = 'Chunk uploaded successfully';
                echo json_encode($response);
                return;
            } else {
                $response['status'] = 'error';
                $response['message'] = error_get_last()['message']; 
                echo json_encode($response);
                return;
            }
        }
    } else {
        // 直接上传的处理逻辑
        $fileName =  $_POST['fileName'];// 确保名称与前端一致
        $target_file = $target_dir . $fileName .'.txt';

        $response = array();

        if (move_uploaded_file($_FILES["file"]["tmp_name"], $target_file)) {
            $response['status'] = 'success';
            $response['file'] = $fileName;
            $response['message'] = 'test';
        } else {
            $response['status'] = 'error';
            $response['message'] = error_get_last()['message'];
        }

        echo json_encode($response);
    }
}
function handleSubmit()
{
    global $conn;
    global $rScriptDir;
    // echo "Uploading file...";
    global $target_dir;

    // 获取前端传递的数据
    $email = $_GET['email'];
    $filename1 = $_GET['filename1'];
    $filename2 = $_GET['filename2'];
    $filename3 = $_GET['filename3'];
    $filename4 = $_GET['filename4'];
    $target_file1 = $target_dir . $filename1;
    $target_file2 = $target_dir . $filename2;
    $target_file3 = $target_dir . $filename3;
    $target_file4 = $target_dir . $filename4;
    $datasetname = $_GET['datasetname'];
    $disease = $_GET['disease'];
    $phenotype = $_GET['phenotype'];

    // 检查 user_data 表是否存在，如果不存在则创建
    $checkTableSql = "SHOW TABLES LIKE 'user_data'";
    $result = $conn->query($checkTableSql);

    if ($result->num_rows == 0) {
        $createTableSql = "
            CREATE TABLE user_data (
                id INT AUTO_INCREMENT PRIMARY KEY,
                email VARCHAR(255),
                filename1 VARCHAR(255),
                filename2 VARCHAR(255),
                filename3 VARCHAR(255),
                filename4 VARCHAR(255),
                Dataset VARCHAR(255) NOT NULL UNIQUE,
                Disease VARCHAR(255),
                Phenotype VARCHAR(255),
                `Group` VARCHAR(255),
                time TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ";
        if ($conn->query($createTableSql) !== TRUE) {
            echo json_encode(['status' => 'error', 'message' => 'Error creating table: ' . $conn->error]);
            return;
        }
    }
    // 检查是否存在相同的 Disease
    $checkDatasetSql = "SELECT * FROM user_data WHERE Dataset = ?";
    $stmt_get = $conn->prepare($checkDatasetSql);
    $stmt_get->bind_param("s", $datasetname);
    $stmt_get->execute();
    $result = $stmt_get->get_result();

    if ($result->num_rows > 0) {
        echo json_encode(['status' => 'error', 'message' => 'Dataset already exists, Please rename your dataset']);
        return;
    }
    $stmt_get->close();

    // Example为dataset的名称
    // txt文件保存路径为/home/shaoxun/data/circRNA/AQUARIUMfinished/FinalResults/Example_SampleInfo.txt
    $file1_path = $target_dir ."User_upload/". $datasetname . "/" . $datasetname . "_SampleInfo.txt";
    // txt文件保存路径为/home/shaoxun/data/circRNA/AQUARIUMfinished/FinalResults/Example_IsoformInfo.txt
    $file2_path = $target_dir ."User_upload/".  $datasetname . "/" . $datasetname . "_IsoformInfo.txt";
    // txt文件保存路径为/home/shaoxun/data/circRNA/AQUARIUMfinished/FinalResults/Example_CountMatrix.txt
    $file3_path = $target_dir ."User_upload/".  $datasetname . "/" . $datasetname . "_CountMatrix.txt";
    // txt文件保存路径为/home/shaoxun/data/circRNA/AQUARIUMfinished/FinalResults/Example_TPMMatrix.txt
    $file4_path = $target_dir ."User_upload/".  $datasetname . "/" . $datasetname . "_TPMMatrix.txt";
    // 三个值的存储路径
    $file5_path = $target_dir ."User_upload/".  $datasetname . "/" . $datasetname . "_info.txt";
    // 创建路径（如果不存在）
    $folder2 = dirname($file5_path);
    if (!is_dir($folder2)) {
        mkdir($folder2, 0777, true);
    }
    $folder1 = dirname($file1_path);
    if (!is_dir($folder1)) {
        mkdir($folder1, 0777, true);
    }
    try {
        if (!copy($target_file1, $file1_path)) {
            throw new Exception("Failed to copy $target_file1 to $file1_path");
        }
        if (!copy($target_file2, $file2_path)) {
            throw new Exception("Failed to copy $target_file2 to $file2_path");
        }
        if (!copy($target_file3, $file3_path)) {
            throw new Exception("Failed to copy $target_file3 to $file3_path");
        }
        if (!copy($target_file4, $file4_path)) {
            throw new Exception("Failed to copy $target_file4 to $file4_path");
        }
    } catch (Exception $e) {
        echo 'Error: ',  $e->getMessage(), "\n";
    }
    if (file_exists($target_file1)) {
        unlink($target_file1);
    }
    if (file_exists($target_file2)) {
        unlink($target_file2);
    }
    if (file_exists($target_file3)) {
        unlink($target_file3);
    }
    if (file_exists($target_file4)) {
        unlink($target_file4);
    }
    $data = $datasetname."\n".$disease."\n".$phenotype;
    // 使用 try-catch 捕获可能的异常
    try {
        // 尝试打开文件
        $file = fopen($file5_path, "w");
        if (!$file) {
            throw new Exception("Failed to open file for writing.");
        }

        // 尝试写入数据
        if (fwrite($file, $data . "\n") === false) {
            throw new Exception("Failed to write data to file.");
        }

        // 尝试关闭文件
        if (!fclose($file)) {
            throw new Exception("Failed to close the file.");
        }

        // echo "Data written to file successfully.\n";

    } catch (Exception $e) {
        // 捕获异常并输出错误信息
        echo 'Error: ',  $e->getMessage(), "\n";
    }

    echo json_encode(['status' => 'success']);

}


function handleGetData()
{
    global $conn;

    // 获取前端传来的参数
    $email = isset($_GET['email']) ? ($_GET['email']) : '';
    $currentPage = isset($_GET['currentPage']) ? intval($_GET['currentPage']) : 1;
    $pageSize = isset($_GET['pageSize']) ? intval($_GET['pageSize']) : 10;
    $searchField = isset($_GET['searchField']) ? $_GET['searchField'] : '';
    $searchKeyword = isset($_GET['searchKeyword']) ? $_GET['searchKeyword'] : '';
    $startTime = isset($_GET['startTime']) ? $_GET['startTime'] : '';
    $endTime = isset($_GET['endTime']) ? $_GET['endTime'] : '';


    // 构建查询语句
    $sql_count = "SELECT COUNT(*) as total FROM user_data WHERE email = '$email'";
    $sql_data = "SELECT * FROM user_data WHERE email = '$email'";

    if ($searchField !== 'Group' && $searchField !== '' && $searchKeyword !== '') {
        $sql_count .= " AND $searchField LIKE '%$searchKeyword%'";
        $sql_data .= " AND $searchField LIKE '%$searchKeyword%'";
    }

    if ($searchField == 'Group' && $searchKeyword !== '') {
        $sql_count .= " AND `Group` LIKE '%$searchKeyword%'";
        $sql_data .= " AND `Group` LIKE '%$searchKeyword%'";
    }

    if ($startTime !== '' && $endTime !== '') {
        // Ensure dates are in the correct format for SQL TIMESTAMP
        $sql_count .= " AND time BETWEEN '$startTime 00:00:00' AND '$endTime 23:59:59'";
        $sql_data .= " AND time BETWEEN '$startTime 00:00:00' AND '$endTime 23:59:59'";
    }

    // 获取总记录数
    $result_count = $conn->query($sql_count);
    $total = $result_count->fetch_assoc()['total'];

    $pageSize = $total < 10 ? $total : $pageSize;
    // 计算偏移量
    $offset = ($currentPage - 1) * $pageSize;

    // 获取指定页的数据
    $sql_data .= " LIMIT $offset, $pageSize";
    $result_data = $conn->query($sql_data);

    $data = [];
    if ($result_data->num_rows > 0) {
        while ($row = $result_data->fetch_assoc()) {
            $data[] = $row;
        }
    }

    // 返回数据总数给前端
    $response = [
        "total" => $total,
        "data" => $data
    ];

    header('Content-Type: application/json');
    echo json_encode($response);
}

function handleDelete()
{
    global $conn;
    global $target_dir;

    // 获取前端传递的id
    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;

    // 检查 id 是否为有效的整数
    if ($id <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid ID']);
        return;
    }

    // 先获取对应 id 的 datasetname 值
    $stmt_get = $conn->prepare("SELECT Dataset FROM user_data WHERE id = ?");
    $stmt_get->bind_param("i", $id);
    $stmt_get->execute();
    $stmt_get->bind_result($datasetname);
    $stmt_get->fetch();
    $stmt_get->close();

    if ($datasetname) {  // 检查是否成功获取到值
        // 删除数据库中的记录
        $stmt = $conn->prepare("DELETE FROM user_data WHERE id = ?");
        $stmt->bind_param("i", $id);

        // 执行删除数据库记录
        if ($stmt->execute()) {
            // 删除文件夹及其所有内容
            $dir_path = $target_dir . "User_upload/" . $datasetname;

            if (deleteDirectory($dir_path)) {
                echo json_encode(['status' => 'success', 'message' => 'Record and files deleted successfully', 'datasetname' => $datasetname]);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to delete directory']);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => $stmt->error]);
        }

        $stmt->close();
    } else {
        echo json_encode(['status' => 'error', 'message' => 'No datasetname found for the given ID']);
    }
}
function handleModifyData()
{
    global $conn;
    global $target_dir;

    // 获取前端传递的数据
    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    $datasetname = isset($_GET['Dataset']) ? $_GET['Dataset'] : '';
    $disease = isset($_GET['Disease']) ? $_GET['Disease'] : '';
    $phenotype = isset($_GET['Phenotype']) ? $_GET['Phenotype'] : '';

    // 检查 id 是否为有效的整数
    if ($id <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid ID']);
        return;
    }

    // 获取原始的 Dataset 值
    $stmt_get = $conn->prepare("SELECT Dataset FROM user_data WHERE id = ?");
    $stmt_get->bind_param("i", $id);
    $stmt_get->execute();
    $stmt_get->bind_result($oldDataset);
    $stmt_get->fetch();
    $stmt_get->close();

    // 更新数据库中的记录
    $filename1 = $datasetname . "_SampleInfo.txt";
    $filename2 = $datasetname . "_IsoformInfo.txt";
    $filename3 = $datasetname . "_CountMatrix.txt";
    $filename4 = $datasetname . "_TPMMatrix.txt";

    $stmt = $conn->prepare("UPDATE user_data SET filename1 = ?, filename2 = ?, filename3 = ?, filename4 = ?, Dataset = ?, Disease = ?, Phenotype = ? WHERE id = ?");
    $stmt->bind_param("sssssssi", $filename1, $filename2, $filename3, $filename4, $datasetname, $disease, $phenotype, $id);

    if ($stmt->execute()) {
        if ($oldDataset != $datasetname) {  // 比较原始值和新值
            $old_dir_path = $target_dir . "User_upload/" . $oldDataset;
            $new_dir_path = $target_dir . "User_upload/" . $datasetname;

            // 如果新文件夹不存在，则创建
            if (!is_dir($new_dir_path)) {
                mkdir($new_dir_path, 0777, true);
            }

            // 遍历旧文件夹中的所有文件
            if (is_dir($old_dir_path)) {
                $files = scandir($old_dir_path);
                foreach ($files as $file) {
                    if ($file != '.' && $file != '..') {
                        $old_file_path = $old_dir_path . '/' . $file;
                        $new_file_path = $new_dir_path . '/' . str_replace($oldDataset, $datasetname, $file);

                        // 复制文件到新路径
                        if (!copy($old_file_path, $new_file_path)) {
                            echo json_encode(['status' => 'error', 'message' => "Failed to copy $old_file_path to $new_file_path"]);
                            return;
                        }

                        // 删除旧文件
                        if (!unlink($old_file_path)) {
                            echo json_encode(['status' => 'error', 'message' => "Failed to delete $old_file_path"]);
                            return;
                        }
                    }
                }

                // 删除旧文件夹
                if (!deleteDirectory($old_dir_path)) {
                    echo json_encode(['status' => 'error', 'message' => "Failed to delete directory $old_dir_path"]);
                    return;
                }
            } else {
                echo json_encode(['status' => 'error', 'message' => "Directory $old_dir_path does not exist"]);
                return;
            }
        }

        echo json_encode(['status' => 'success', 'message' => 'Record updated successfully']);
    } else {
        echo json_encode(['status' => 'error', 'message' => $stmt->error]);
    }

    $stmt->close();
}
