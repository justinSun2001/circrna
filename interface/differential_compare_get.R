args <- commandArgs(TRUE) # 获取所有命令行参数

# 检查至少有两个参数被传递
if(length(args) < 2) {
  stop("需要至少2个参数")
}

# 解析参数
param1 <- args[1] # 第一个参数
param2 <- args[2] # 第二个参数


dirraw <- param1
setwd(dirraw)
datasetname <- param2 # 参数，选数据集

# 检查文件是否存在
filepath1 <- paste0(dirraw,datasetname,"/final_results/DEresults.circ.3levels.rds"))
if (!file.exists(filepath1)) {
    stop(paste("Error: File", filepath1, "does not exist."))
}


DEresults <- readRDS(filepath1)
mycompare <- names(DEresults)
# 加载必要的库
library(jsonlite)
# 将数组转换为 JSON 格式
json_result <- toJSON(mycompare)

# 打印 JSON 格式的结果
cat(json_result)