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

    quant.sf <- fread(paste0(param1,datasetname,"/quant.sf.allsample.txt"),data.table = F)
    quant.sf <- quant.sf[grep("chrMT",quant.sf$Name,invert = T),]
    quant.sf <- quant.sf[grep("chr",quant.sf$Name),]
    quant.sf <- quant.sf[quant.sf$NumReads>0,]
subdata <- dict[dict$transcript_id %in% unique(quant.sf$Name),]

subdata$log2SplicedSequenceLength <- log2(subdata$SplicedSequenceLength)

plot1 <- ggplot(data = data.frame(subdata$log2SplicedSequenceLength), aes(x = subdata$log2SplicedSequenceLength)) +     
  geom_density(color = colors[1],fill = colors[1]) +    
  theme_minimal() +  
  theme(axis.title.x = element_text(size = 20),  
        axis.title.y = element_text(size = 20),
        axis.text = element_text(size = 20),
        plot.title = element_text(size = 20),
        panel.grid.major = element_blank(),  # 删除背景的网格线
        panel.grid.minor = element_blank(),  # 删除背景的网格线
        panel.border = element_rect(color = "gray", fill = NA, size = 1)) +  
  labs(title = "", x = "log2(SplicedSequenceLength)", y = "Density") + 
  scale_y_continuous(labels = scales::comma_format())

plot2 <- ggplot(data = data.frame(log2(subdata$exon_count)), aes(x = log2(subdata$exon_count))) +     
  geom_histogram(binwidth = 1,  color = "white", fill = colors[1], alpha = 1) +    
  theme_minimal() +  
  theme(axis.title.x = element_text(size = 20),  
        axis.title.y = element_text(size = 20),
        axis.text = element_text(size = 20),
        plot.title = element_text(size = 20),
        panel.grid.major = element_blank(),  # 删除背景的网格线
        panel.grid.minor = element_blank(),  # 删除背景的网格线
        panel.border = element_rect(color = "gray", fill = NA, size = 1)) +  
  labs(title = "", x = "Number of exons(log2) per isoform", y = "Number of isoform") + 
  scale_y_continuous(labels = scales::comma_format())

n_bsj_isoform <- count(subdata,BSJ_ID)
n_bsj_isoform <- n_bsj_isoform[order(n_bsj_isoform$n,decreasing = T),]
n_bsj_isoform$logn <- log2(n_bsj_isoform$n)
plot3 <- ggplot(data = data.frame(n_bsj_isoform$logn), aes(x = n_bsj_isoform$logn)) +       
  geom_histogram(binwidth = 0.5,  color = "white", fill = colors[1], alpha = 1) +    
  theme_minimal() +  
  theme(axis.title.x = element_text(size = 20),  
        axis.title.y = element_text(size = 20),
        axis.text = element_text(size = 20),
        plot.title = element_text(size = 20),
        panel.grid.major = element_blank(),  # 删除背景的网格线
        panel.grid.minor = element_blank(),  # 删除背景的网格线
        panel.border = element_rect(color = "gray", fill = NA, size = 1)) +  
  labs(title = "", x = "Number of circRNAs (log2n) per bsj", y = "Number of bsj") + 
  scale_y_continuous(labels = scales::comma_format())

n_gene_isoform <- count(subdata,host_gene)
n_gene_isoform <- n_gene_isoform[order(n_gene_isoform$n,decreasing = T),]
n_gene_isoform$logn <- log2(n_gene_isoform$n)
plot4 <- ggplot(data = data.frame(n_gene_isoform$logn), aes(x = n_gene_isoform$logn)) +       
  geom_histogram(binwidth = 0.5,  color = "white", fill = colors[1], alpha = 1) +    
  theme_minimal() +  
  theme(axis.title.x = element_text(size = 20),  
        axis.title.y = element_text(size = 20),
        axis.text = element_text(size = 20),
        plot.title = element_text(size = 20),
        panel.grid.major = element_blank(),  # 删除背景的网格线
        panel.grid.minor = element_blank(),  # 删除背景的网格线
        panel.border = element_rect(color = "gray", fill = NA, size = 1)) +  
  labs(title = "", x = "Number of circRNAs (log2n) per host gene", y = "Number of gene") + 
  scale_y_continuous(labels = scales::comma_format())



combined_plot <- grid.arrange(plot1, plot2, plot3, plot4, nrow = 1)
# 保存图像
ggsave(filename = "cricRNA_characteristics.svg", plot = combined_plot,  width = 28, height = 6, dpi = 100)
