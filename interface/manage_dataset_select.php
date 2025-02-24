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
    // case 'getGroups':
    //     $diseases = isset($_GET['diseases']) ? $_GET['diseases'] : '';
    //     $phenotypes = isset($_GET['phenotypes']) ? $_GET['phenotypes'] : '';
    //     $datasets = isset($_GET['datasets']) ? $_GET['datasets'] : '';
    //     getGroups($diseases, $phenotypes, $datasets);
    //     break;
    default:
        echo json_encode([]);
}

function getUniqueValues($column, $filterColumn = '', $filterValue = '', $isMultiple = false) {
    global $conn;
    global $email;
    
    $sql = "SELECT DISTINCT $column FROM dataset_select";
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

// function getGroups($diseases, $phenotypes, $datasets) {
//     global $conn;
//     global $email;

//     $diseasesArray = explode(',', $diseases);
//     $phenotypesArray = explode(',', $phenotypes);
//     $datasetsArray = explode(',', $datasets);

//     $placeholdersDiseases = implode(',', array_fill(0, count($diseasesArray), '?'));
//     $placeholdersPhenotypes = implode(',', array_fill(0, count($phenotypesArray), '?'));
//     $placeholdersDatasets = implode(',', array_fill(0, count($datasetsArray), '?'));

//     // Query dataset_select table
//     $sql = "SELECT DISTINCT `Group` FROM dataset_select WHERE Disease IN ($placeholdersDiseases) AND Phenotype IN ($placeholdersPhenotypes) AND Dataset IN ($placeholdersDatasets)";
//     $params = array_merge($diseasesArray, $phenotypesArray, $datasetsArray);

//     $stmt = $conn->prepare($sql);
//     $stmt->bind_param(str_repeat('s', count($params)), ...$params);
//     $stmt->execute();
//     $result = $stmt->get_result();

//     $groups = [];
//     while ($row = $result->fetch_assoc()) {
//         $groups[] = $row['Group'];
//     }

//     // Query user_data table if email is set
//     if ($email) {
//         $sql = "SELECT DISTINCT `Group` FROM user_data WHERE email = ? AND Disease IN ($placeholdersDiseases) AND Phenotype IN ($placeholdersPhenotypes) AND Dataset IN ($placeholdersDatasets)";
//         $paramsWithEmail = array_merge([$email], $diseasesArray, $phenotypesArray, $datasetsArray);

//         $stmt = $conn->prepare($sql);
//         $stmt->bind_param(str_repeat('s', count($paramsWithEmail)), ...$paramsWithEmail);
//         $stmt->execute();
//         $result = $stmt->get_result();

//         while ($row = $result->fetch_assoc()) {
//             // Add only if not already present in $groups
//             if (!in_array($row['Group'], $groups)) {
//                 $groups[] = $row['Group'];
//             }
//         }
//     }

//     // Return groups as JSON
//     header('Content-Type: application/json');
//     echo json_encode($groups);

//     // Close statement and connection
//     $stmt->close();
//     $conn->close();
// }

?>
