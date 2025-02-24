<?php
include 'conn.php';

// 检查是否传递了 BloodCircR_ID 参数
if (isset($_GET['BloodCircR_ID'])) {
    $BloodCircR_ID = $_GET['BloodCircR_ID'];

    // 准备并执行获取节点数据的查询
    $enrichments_query = "SELECT * FROM enrichment WHERE BloodCircR_ID = ?";
    $enrichments_stmt = $conn->prepare($enrichments_query);
    $enrichments_stmt->bind_param("s", $BloodCircR_ID);
    $enrichments_stmt->execute();
    $enrichments_result = $enrichments_stmt->get_result();

    // 检查是否成功获取节点数据
    if ($enrichments_result) {
        // 遍历结果集并组装节点数据
        while ($row = $enrichments_result->fetch_assoc()) {
            // 分割'GeneRatio'的值
            $gene_ratios = explode('/', $row['GeneRatio']);
            // 将分割后的数据存储到数组中
            $enrichments[] = [
                'BloodCircR_ID'=> $row['BloodCircR_ID'],
                'name' => $row['ID'],
                'description' => $row['Description'],
                'number_of_genes' => $gene_ratios[0], // 斜杠前面的值
                'number_of_genes_in_background' => $gene_ratios[1], // 斜杠后面的值
                'p_value' => $row['pvalue'],
                'qvalue' => $row['qvalue'],
                'category' => $row['Type'],
                'p_adjust'=> $row['p.adjust'],
                'geneID'=> $row['geneID'],
                'geneSymbol'=> $row['geneSymbol'],
                'gene_ratios'=> $row['GeneRatio'],
            ];
        }
    } else {
        // 如果查询失败，返回错误消息
        die("获取富集数据失败: " . $conn->error);
    }

    
    // 关闭数据库连接
    $conn->close();

    // 组装数据
    $data = ['enrichments' => $enrichments];

    // 输出数据以便前端JavaScript使用
    echo json_encode($data);
} else {
    // 如果没有传递 BloodCircR_ID 参数，返回错误消息
    die("缺少参数 BloodCircR_ID");
}
?>
