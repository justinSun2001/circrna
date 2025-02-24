args <- commandArgs(TRUE) # 获取所有命令行参数

# 检查至少有两个参数被传递
if(length(args) < 2) {
  stop("需要至少两个参数")
}

# 解析参数
param1 <- args[1] # 第一个参数
param2 <- args[2] # 第二个参数

# 使用参数
cat("参数1是:", param1, "\n", "参数2是:", param2, "\n")
options(warn=-1)
suppressPackageStartupMessages({
    library(data.table)
    library(ggplot2)
    library(plyr)
    library(dplyr)
    library(RColorBrewer)
    library(gridExtra)
    library(png)
    library(grid)
    library(svglite) # 确保加载了svglite包
})
options(warn = -1)    
dirraw <- param1
setwd(dirraw)
colors <- brewer.pal(10, "Set3")
# 构建文件路径
file1 <- paste0(param1, "/circRNA.atlas.datatable_forwebsever.csv")
file2 <- paste0(param1, param2, "/quant.sf.allsample.txt")

# 检查文件是否存在
if (!file.exists(file1)) {
    stop(paste("Error: File", file1, "does not exist."))
}

if (!file.exists(file2)) {
    stop(paste("Error: File", file2, "does not exist."))
}
dirraw=param1
datasetname=param2
circRNA.atlas.datatable <- fread(paste0(param1, "/circRNA.atlas.datatable_forwebsever.csv"), data.table = F)
dict = circRNA.atlas.datatable # 需要预加载

print("转录本的检测比")
    quant.sf <- fread(paste0(param1,datasetname,"/quant.sf.allsample.txt"),data.table = F)
    quant.sf <- quant.sf[grep("chrMT",quant.sf$Name,invert = T),]
    quant.sf_circ <- quant.sf[grep("chr",quant.sf$Name),]
    quant.sf_circ <- quant.sf_circ[quant.sf_circ$NumReads>0,]
Nsample <- length(unique(quant.sf_circ$sampleID))
circRNA_detect <- count(quant.sf_circ,Name) %>% arrange(-n)
circRNA_detect$ratio <- round(circRNA_detect$n/Nsample,4)
circRNA_detect$BloodCircleR_ID <- mapvalues(circRNA_detect$Name,dict$transcript_id,dict$BloodCircleR_ID,warn_missing = F)
circRNA_detect$ConfidenceRank <- mapvalues(circRNA_detect$Name,dict$transcript_id,dict$ConfidenceRank,warn_missing = F)
circRNA_detect <- circRNA_detect[,c("BloodCircleR_ID","n","ratio","ConfidenceRank")]
df <- circRNA_detect %>%  # 这个变量包含检测的样本数和检测比
  mutate(group = cut(ratio, breaks = seq(0, 1, by = 0.1), right = TRUE, include.lowest = TRUE))
filepath <- paste0(param1,"/DetectRatio_",datasetname,".csv")
write.csv(df,file=filepath,row.names=F)
df_count <- df %>% count(group)
plot <- ggplot(df_count, aes(x = group, y = n)) +
  geom_bar(stat = "identity", fill = colors[1], width = 0.5) +  # Adjust width here
  geom_text(aes(label = n), vjust = -0.5, size = 5) +
  theme(
    axis.text.x = element_text(size = 16,angle = 90), # 适当减小字体大小以避免重叠
    text = element_text(size = 16),
    panel.background = element_blank(),  # 去掉绘图面板背景
    plot.background = element_blank(),   # 去掉整个图片背景
    axis.line = element_line(colour = "black"),  # 添加 X 轴和 Y 轴的线
    plot.margin = unit(c(1, 1, 1.5, 1), "cm")  # 调整图表边距以提供足够的显示空间
  ) +
  labs(title = "circRNA detection ratio", x = "Sample Fraction", y = "Number of circRNAs")


# 保存图像
ggsave(filename = "sample_detection.svg", plot = plot, width = 10, height = 7, dpi = 100)
