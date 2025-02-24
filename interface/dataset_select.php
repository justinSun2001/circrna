<?php

include_once('conn.php'); // 包含数据库连接文件

$action = isset($_GET['action']) ? $_GET['action'] : '';
$email = isset($_GET['email']) ? $_GET['email'] : '';
switch($action) {
    case 'getDiseases':
        getDiseasesAndPhenotypes();
        break;
    case 'getDatasets':
        $disease = isset($_GET['disease']) ? $_GET['disease'] : '';
        $phenotypes = isset($_GET['phenotypes']) ? $_GET['phenotypes'] : '';
        getUniqueValues('Dataset', 'Disease', $disease, 'Phenotype', $phenotypes, true);
        break;
    default:
        echo json_encode([]);
}
// 获取所有疾病和对应的表型数据
function getDiseasesAndPhenotypes() {
    global $conn;

    // 查询所有疾病及对应的表型
    $sql = "SELECT DISTINCT Disease, Phenotype FROM dataset_select ORDER BY Disease ASC, Phenotype ASC";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $result = $stmt->get_result();

    $data = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            if (!isset($data[$row['Disease']])) {
                $data[$row['Disease']] = [];
            }
            $data[$row['Disease']][] = $row['Phenotype'];
        }
    }
    // 从 user_data 表中读取数据
    $sqlUserData = "SELECT DISTINCT Disease, Phenotype FROM user_data ORDER BY Disease ASC, Phenotype ASC";
    $stmtUserData = $conn->prepare($sqlUserData);
    $stmtUserData->execute();
    $resultUserData = $stmtUserData->get_result();

    if ($resultUserData) {
        while ($row = $resultUserData->fetch_assoc()) {
            if (!isset($data[$row['Disease']])) {
                $data[$row['Disease']] = [];
            }
            // 如果表型不存在于当前疾病中，则添加
            if (!in_array($row['Phenotype'], $data[$row['Disease']])) {
                $data[$row['Disease']][] = $row['Phenotype'];
            }
        }
    }

    // 构建树形结构数据
    $treeData = [];
    foreach ($data as $disease => $phenotypes) {
        $diseaseNode = [
            'id' => $disease,
            'label' => $disease,
            'children' => []
        ];
        foreach ($phenotypes as $phenotype) {
            $diseaseNode['children'][] = [
                'id' => $phenotype,
                'label' => $phenotype
            ];
        }
        $treeData[] = $diseaseNode;
    }

    header('Content-Type: application/json');
    echo json_encode($treeData);
    $stmt->close();
    $stmtUserData->close();
    $conn->close();
    // // 构建树形结构数据
    // $treeData = [];
    // foreach ($data as $disease => $phenotypes) {
    //     $diseaseNode = [
    //         'id' => $disease,
    //         'label' => $disease,
    //         'children' => []
    //     ];
    //     foreach ($phenotypes as $phenotype) {
    //         $diseaseNode['children'][] = [
    //             'id' => $phenotype,
    //             'label' => $phenotype
    //         ];
    //     }
    //     $treeData[] = $diseaseNode;
    // }

    // header('Content-Type: application/json');
    // echo json_encode($treeData);
    // $stmt->close();
    // $conn->close();
}

function getUniqueValues($column, $filterColumn = '', $filterValue = '', $filterColumn1 = '', $filterValue1 = '', $isMultiple = false) {
    global $conn;
    global $email;

    // 基础查询
    $sql = "SELECT DISTINCT $column FROM dataset_select";
    $params = [];
    $conditions = [];

    // 添加第一个筛选条件
    if ($filterColumn && $filterValue) {
        if ($isMultiple) {
            $values = explode(',', $filterValue);
            $placeholders = implode(',', array_fill(0, count($values), '?'));
            $conditions[] = "$filterColumn IN ($placeholders)";
            $params = array_merge($params, $values);
        } else {
            $conditions[] = "$filterColumn = ?";
            $params[] = $filterValue;
        }
    }

    // 添加第二个筛选条件
    if ($filterColumn1 && $filterValue1) {
        if ($isMultiple) {
            $values = explode(',', $filterValue1);
            $placeholders = implode(',', array_fill(0, count($values), '?'));
            $conditions[] = "$filterColumn1 IN ($placeholders)";
            $params = array_merge($params, $values);
        } else {
            $conditions[] = "$filterColumn1 = ?";
            $params[] = $filterValue1;
        }
    }

    // 合并条件
    if (!empty($conditions)) {
        $sql .= " WHERE " . implode(' AND ', $conditions);
    }

    $sql .= " ORDER BY $column ASC";  // 按字母顺序排序
    $stmt = $conn->prepare($sql);
    if ($params) {
        $stmt->bind_param(str_repeat('s', count($params)), ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $uniqueValues = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $uniqueValues[] = $row[$column];
        }
    }

    // 如果用户已登录，从 user_data 表中查询数据
    if ($email) {
        $sqlUser = "SELECT DISTINCT $column FROM user_data WHERE email = ?";
        $paramsUser = [$email];
        $conditionsUser = [];

        // 添加第一个筛选条件
        if ($filterColumn && $filterValue) {
            if ($isMultiple) {
                $values = explode(',', $filterValue);
                $placeholders = implode(',', array_fill(0, count($values), '?'));
                $conditionsUser[] = "$filterColumn IN ($placeholders)";
                $paramsUser = array_merge($paramsUser, $values);
            } else {
                $conditionsUser[] = "$filterColumn = ?";
                $paramsUser[] = $filterValue;
            }
        }

        // 添加第二个筛选条件
        if ($filterColumn1 && $filterValue1) {
            if ($isMultiple) {
                $values = explode(',', $filterValue1);
                $placeholders = implode(',', array_fill(0, count($values), '?'));
                $conditionsUser[] = "$filterColumn1 IN ($placeholders)";
                $paramsUser = array_merge($paramsUser, $values);
            } else {
                $conditionsUser[] = "$filterColumn1 = ?";
                $paramsUser[] = $filterValue1;
            }
        }

        // 合并条件
        if (!empty($conditionsUser)) {
            $sqlUser .= " AND " . implode(' AND ', $conditionsUser);
        }

        $sqlUser .= " ORDER BY $column ASC";  // 按字母顺序排序
        $stmtUser = $conn->prepare($sqlUser);
        if ($paramsUser) {
            $stmtUser->bind_param(str_repeat('s', count($paramsUser)), ...$paramsUser);
        }
        $stmtUser->execute();
        $resultUser = $stmtUser->get_result();
        if ($resultUser) {
            while ($row = $resultUser->fetch_assoc()) {
                // 如果值不存在于 $uniqueValues 中，则添加
                if (!in_array($row[$column], $uniqueValues)) {
                    $uniqueValues[] = $row[$column];
                }
            }
        }
        $stmtUser->close();
    }

    header('Content-Type: application/json');
    echo json_encode($uniqueValues);
    $stmt->close();
    $conn->close();
}

?>
