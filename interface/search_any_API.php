<?php
include_once('conn.php');

try {
    // 检查是否提供了必要的GET参数
    if (isset($_GET['search_any']) && isset($_GET['page']) && isset($_GET['limit'])) {
        $searchTerms = explode('、', $_GET['search_any']);
        $page = intval($_GET['page']);
        $limit = intval($_GET['limit']);

        // 检查页码和每页数据个数是否有效
        if ($page <= 0 || $limit <= 0) {
            throw new Exception("页码和每页数据个数必须为正整数");
        }

        // 构建查询条件
        $condition = '';
        foreach ($searchTerms as $term) {
            // print_r($term);
            $term = $conn->real_escape_string($term); // 防止SQL注入
            if ($condition !== '') {
                $condition .= ' OR ';
            }
            $condition .= "(`BloodCircR_ID` = '$term' OR `host_gene` = '$term' OR `ensembl_id` = '$term' OR `BSJ_ID` = '$term' OR `Uniform_ID` = '$term')";

            // 添加从BloodCircleR_other表中查找符合条件的BloodCircR_ID
            $condition .= " OR `BloodCircR_ID` IN (SELECT `BloodCircR_ID` FROM `alias_info` 
                            WHERE `circAtlas` = '$term' 
                            OR `Transcirc` = '$term' 
                            OR `FLcircAS` = '$term' 
                            OR `circBase` = '$term' 
                            OR `PltDB` = '$term')";
			$condition .= " OR `BloodCircR_ID` IN (SELECT `BloodCircR_ID` FROM `new_table` 
                            WHERE `DatasetID` = '$term')";
        }
        
        // 查询数据库以获取总行数
        $countSql = "SELECT COUNT(*) AS total FROM basic_info WHERE $condition";
        $countResult = $conn->query($countSql);

        // 检查查询是否成功
        if (!$countResult) {
            throw new Exception("查询失败: " . $conn->error);
        }

        $totalCount = $countResult->fetch_assoc()['total'];

        // 计算偏移量
        $offset = ($page - 1) * $limit;

        // 使用查询来查找匹配的值并应用分页
        $sql = "SELECT * FROM basic_info WHERE $condition LIMIT $limit OFFSET $offset";
        $result = $conn->query($sql);

        // 检查查询结果
        if (!$result) {
            throw new Exception("查询失败: " . $conn->error);
        }

        if ($result->num_rows > 0) {
            $data = array();
            while ($row = $result->fetch_assoc()) {
                $data[] = $row;
            }

            $response = array(
                'code' => 0,
                'msg' => '',
                'count' => $totalCount, // 设置为总行数
                'data' => $data
            );
        } else {
            $response = array(
                'code' => 0,
                'msg' => '没有找到匹配的数据',
                'count' => 0,
                'data' => array()
            );
        }

        // 设置响应头部为JSON格式
        header('Content-Type: application/json');
        echo json_encode($response);

    } else {
        throw new Exception("未提供有效的搜索条件、页码和每页数据个数");
    }
} catch (Exception $e) {
    // 错误处理：记录错误日志，并返回错误响应
    error_log($e->getMessage()); // 将错误信息写入日志

    $response = array(
        'code' => 1,
        'msg' => '服务器错误: ' . $e->getMessage(),
        'count' => 0,
        'data' => array()
    );

    // 设置响应头部为JSON格式
    header('Content-Type: application/json', true, 500); // 返回500状态码
    echo json_encode($response);
} finally {
    // 确保关闭数据库连接
    if ($conn) {
        $conn->close();
    }
}
?>
