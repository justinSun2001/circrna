<?php

include_once('conn.php'); // 包含数据库连接文件

$action = isset($_GET['action']) ? $_GET['action'] : '';
$email = isset($_GET['email']) ? $_GET['email'] : '';
switch($action) {
    case 'getDiseases':
        getUniqueValues('Disease');
        break;
    case 'getPhenotypes':
        $diseases = isset($_GET['diseases']) ? $_GET['diseases'] : '';
        getUniqueValues('Phenotype', 'Disease', $diseases, true);
        break;
    case 'getDatasets':
        $phenotypes = isset($_GET['phenotypes']) ? $_GET['phenotypes'] : '';
        getUniqueValues('Dataset', 'Phenotype', $phenotypes, true);
        break;
    default:
        echo json_encode([]);
}

function getUniqueValues($column, $filterColumn = '', $filterValue = '', $isMultiple = false) {
    global $conn;
    global $email;
    
    $sql = "SELECT DISTINCT $column FROM dataset_select_diff";
    $params = [];
    if ($filterColumn && $filterValue) {
        if ($isMultiple) {
            $values = explode(',', $filterValue);
            $placeholders = implode(',', array_fill(0, count($values), '?'));
            $sql .= " WHERE $filterColumn IN ($placeholders)";
            $params = $values;
        } else {
            $sql .= " WHERE $filterColumn = ?";
            $params = [$filterValue];
        }
    }
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
    } else {
        $uniqueValues = [];
    }

    if ($email) {
        $sql = "SELECT DISTINCT $column FROM user_data WHERE email = ? ";
        $params = [$email];
        if ($filterColumn && $filterValue) {
            if ($isMultiple) {
                $values = explode(',', $filterValue);
                $placeholders = implode(',', array_fill(0, count($values), '?'));
                $sql .= " AND $filterColumn IN ($placeholders)";
                $params = array_merge([$email], $values);
            } else {
                $sql .= " AND $filterColumn = ?";
                $params = [$email, $filterValue];
            }
        }
        $stmt = $conn->prepare($sql);
        if ($params) {
            $stmt->bind_param(str_repeat('s', count($params)), ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result) {
            while ($row = $result->fetch_assoc()) {
            // Add only if not already present in $uniqueValues
            if (!in_array($row[$column], $uniqueValues)) {
                $uniqueValues[] = $row[$column];
            }
            }
        }

    }

    header('Content-Type: application/json');
    echo json_encode($uniqueValues);
    $stmt->close();
    $conn->close();
}

?>
