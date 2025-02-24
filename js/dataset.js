window.onload = function(){
layui.use(['table','form'], function(){/*layui引入*/
var table = layui.table,form = layui.form,$=layui.$;


/*获取get方式传递的Dataset*/
var urlParams = new URLSearchParams(window.location.search);
var Dataset = urlParams.get("Dataset");
console.log('Dataset',Dataset);

/*Samples*/
var Samples = table.render({
	elem: '#Samples'
	,page: true
	,width:1460
	,url: './interface/dataset_sample_API.php'
	,page: true //开启分页
	,id : 'Samples'
	,where: {Dataset: Dataset}
	,limits:[10,50,100]
	,cols: [[ //表头
	 {field: 'Sample', title: 'Sample', event: 'Go_sample',sort:true,align:'center'}
	  ,{field: 'group', title: 'group',sort:true,align:'center'}
	  ,{field: 'AvgSpotLen', title: 'AvgSpotLen',sort:true,align:'center'}
	  ,{field: 'Bases', title: 'Bases',sort:true,align:'center'}
	  ,{field: 'Bytes', title: 'Bytes',sort:true,align:'center'}
	]]
});


table.on('tool(Samples)', function(obj){
    let event = obj.event;
    if (event === "Go_sample") {
		window.location.href = 'sample.html?Samples=' + encodeURIComponent($(this).find('div').html());
    }
});

/*dataset_CircRNA*/
var cirRNAs = table.render({
	elem: '#dataset_CircRNA'
	,page: true
	,width:1460
	,url: './interface/dataset_CircRNA_API.php'
	,page: true //开启分页
	,id : 'dataset_CircRNA'
	,where: {Dataset: Dataset}
	,limits:[10,50,100]
	,cols: [[ //表头
	{field: 'BloodCircR_ID', title: 'BloodCircR_ID',event: 'BloodCircR_ID_click',sort:true,align:'center'}
	  ,{field: 'BSJ', title: 'BSJ',sort:true,align:'center'}
	  ,{field: 'DetectCount', title: 'DetectCount', event: 'DetectCount_click',sort:true,align:'center'}
	  ,{field: 'DetectFraction', title: 'DetectFraction',sort:true,align:'center'}
	  ,{field: 'state', title: 'state',sort:true,align:'center'}
	]]
});
/*circRNA 下拉菜单*/
var newDiv = null; // 用于存储唯一的 <div> 元素
table.on('tool(dataset_CircRNA)', function (obj) {
    let event = obj.event;
    if (event === "DetectCount_click") {
		//点击元素的同级元素获取BloodCircR_ID
		let BloodCircR_ID = $(this).siblings('td[data-field="BloodCircR_ID"]').find('div').html();
		let DetectCount = $(this).find('div').html()
        var leftOffset = $(this).offset().left - $("#CircRNA_tableout").offset().left;
        var topOffset = $(this).offset().top - $("#CircRNA_tableout").offset().top;

        // 销毁已存在的 newDiv（如果有）
        if (newDiv) {
            newDiv.remove();
        }

        // 创建新的 <div> 元素
        newDiv = $("<div>").attr('class','newDiv');
        newDiv.css({
            position: "absolute",
            left: leftOffset - 1 + "px",
            top: topOffset + 39 + "px",
            width: $(this).width(),
        });

        // 添加到页面
        $("#CircRNA_tableout").append(newDiv);
		console.log(Dataset,BloodCircR_ID,DetectCount)
        // 发起 Ajax 请求获取数据
        $.ajax({
            url: './interface/dataset_circRNA_click_samples_API.php?Dataset=' + Dataset +'&DetectCount=' + DetectCount +'&BloodCircR_ID=' + BloodCircR_ID ,
            dataType: 'json',
            success: function (data) {
				console.log(data)
                // 创建一个列表
                var ul = $("<ul>");

                // 循环数据，为列表项添加超链接
                for (var i = 0; i < data.length; i++) {
                    var item = data[i];
                    var li = $("<li>");
                    var link = $("<a>").text('Samples : ' + item.Samples).attr('href', 'sample.html?Samples=' + item.Samples);
                    li.append(link);
                    ul.append(li);
                }

                newDiv.append(ul);

                // 根据列表项数量设置 newDiv 的高度
                //newDiv.css("height", (data.length * 35) + "px");
            },
            error: function (xhr, status, error) {
                console.error("Ajax request failed: " + error);
            }
        });
		/*区域外点击销毁*/
		$(document).on("click", function (e) {
            if (!newDiv || !newDiv.is(e.target) && newDiv.has(e.target).length === 0 && !$(this).is(e.target)) {
                // 如果点击位置不在 newDiv 或 this 区域内，销毁 newDiv
                newDiv.remove();
            }
        });
        console.log($(this).find('div').html());
    }else if(event === 'BloodCircR_ID_click'){
		window.location.href = 'circRNA.html?BloodCircR_ID=' + encodeURIComponent($(this).text());
	}
});

/*选项获取 + 选后处理*/
$.ajax({
    url: './interface/circRNA_dataset_select_API.php',
    dataType: 'json',
    success: function (data) {
        let datasetFromGet = Dataset; // 替换为从 GET 请求中获取的 dataset 值
        let selectHtml = '';
        for (let i = 0; i < data.data.length; i++) {
            let datasetValue = data.data[i].Dataset;
            let selected = (datasetValue === datasetFromGet) ? 'selected' : ''; // 如果与 GET 中的 dataset 值相同，则添加 selected
            selectHtml += `<option value="${datasetValue}" ${selected}>${datasetValue}</option>`;
        }
        $('#Dataset_select').html(selectHtml);
    },
    error: function (xhr, status, error) {
        console.error("Ajax request failed: " + error);
    }
});

$('#Dataset_select').on("change", function() {
  var selectedValue = $(this).val();
  console.log('selectedValue',selectedValue)
	window.location.href = 'browse_dataset.html?Dataset=' + encodeURIComponent(selectedValue);
});

/*state 扇形图*/
var State_echarts_Dom = document.getElementById('State_echarts');
var State_echarts_Chart = echarts.init(State_echarts_Dom);
var Type_data;
var State_echarts_option;
$.ajax({
	type: "GET", 
	url: "./interface/dataset_state_echarts_API.php?Dataset=" + Dataset, 
	dataType: "json",
	success: function(data) {
		Type_data = data;
		State_echarts_option = {
		  title: {
			text: 'State',
			left: 'center'
		  },
		  tooltip: {
			trigger: 'item'
		  },
		  color:['#DE6F6B', '#5A6FC0', '#9ECA7F', '#F2CA6B'],
		  legend: {
			orient: 'vertical',
			left: 'left'
		  },
		  series: [
			{
			  name: 'State',
			  type: 'pie',
			  radius: '70%',
			  data: Type_data,
			  emphasis: {
				itemStyle: {
				  shadowBlur: 12,
				  shadowOffsetX: 0,
				  shadowColor: 'rgba(0, 0, 0, 0.5)'
				}
			  }
			}
		  ]
		};
		State_echarts_option && State_echarts_Chart.setOption(State_echarts_option);
	},
	error: function(xhr, status, error) {
		console.log("请求失败: " + error);
	}
});


/*Exonic and Length distribution  1  */
var Exonic_echarts1_Dom = document.getElementById('Exonic_echarts1');
var Exonic_echarts1_Chart = echarts.init(Exonic_echarts1_Dom);
var Exonic_echarts1_option;
$.ajax({
    type: 'GET',
    url: './interface/dataset_exons_echarts1_API.php?Dataset=' + Dataset,
    success: function(data) {
		let jsonData = JSON.parse(data);
		Exonic_echarts1_option = {
			tooltip: {
				trigger: 'axis',
				axisPointer: {
					type: 'shadow'
				}
			},
		  xAxis: {
			type: 'category',
			data:jsonData.xAxis,
			name: 'Number of exons', // X 轴说明文字
			interval: 4, // 设置刻度分隔间隔为 5-1 = 4
			nameLocation: 'center', // 放置在坐标轴末端
			splitNumber: 5, // 控制坐标轴分割的段数

			axisLabel: {
			  textStyle: {
				color: '#000', // 说明文字颜色
				fontSize: 18 // 说明文字大小
			  },
			  margin: 10 // 说明文字与轴线的距离
			},
			nameTextStyle: {
			  color: '#000', // Y 轴名称的颜色
			  fontSize: 14, // Y 轴名称的大小
			  padding: [20, 0, 0, 0] // 调整名称与轴线的距离，上右下左
			}
		  },
		  yAxis: {
			type: 'value',
			data:jsonData.yAxis,
			name: 'counts',
			nameLocation: 'center', // 放置在坐标轴中间
			nameRotate: 90, // 旋转角度，以度为单位
			nameTextStyle: {
			  color: '#000', // Y 轴名称的颜色
			  fontSize: 14, // Y 轴名称的大小
			  padding: [0, 0, 30, 0] // 调整名称与轴线的距离，上右下左
			}
		  },
		  series: [
			{

			  data: jsonData.yAxis,
			  type: 'bar',
			  itemStyle: {
				color: '#97CCE8',
				borderWidth: 0, // 设置边框宽度
				borderColor: 'black' // 设置边框颜色

			  },
			  barCategoryGap: '10%' // 设置柱子间距为 0

			}
		  ],

		};
		Exonic_echarts1_option && Exonic_echarts1_Chart.setOption(Exonic_echarts1_option);
    },
    error: function(xhr, status, error) {
        console.log(error);
    }
});

/*Exonic and Length distribution  2  */
var Length_echarts2_Dom = document.getElementById('Length_echarts2');
var Length_echarts2_Chart = echarts.init(Length_echarts2_Dom);
var Length_echarts2_option;
$.ajax({
    type: 'GET',
    url: './interface/dataset_exons_echarts2_API.php?Dataset=' + Dataset,
    success: function(data) {
		let jsonData = JSON.parse(data);
		Length_echarts2_option = {
			tooltip: {
				trigger: 'axis',
				axisPointer: {
					type: 'shadow'
				}
			},
		  xAxis: {
			type: 'category',
			data:jsonData.xAxis,
			name: 'log10Length（nt）', // X 轴说明文字
			interval: 4, // 设置刻度分隔间隔为 5-1 = 4
			nameLocation: 'center', // 放置在坐标轴末端
			splitNumber: 5, // 控制坐标轴分割的段数
			axisLabel: {
			  textStyle: {
				color: '#000', // 说明文字颜色
				fontSize: 18 // 说明文字大小
			  },
			  margin: 10 // 说明文字与轴线的距离
			},
			nameTextStyle: {
			  color: '#000', // Y 轴名称的颜色
			  fontSize: 14, // Y 轴名称的大小
			  padding: [20, 0, 0, 0] // 调整名称与轴线的距离，上右下左
			}
		  },
		  yAxis: {
			type: 'value',
			data:jsonData.yAxis,
			name: 'counts',
			nameLocation: 'center', // 放置在坐标轴末端
			nameRotate: 90, // 旋转角度，以度为单位
			nameTextStyle: {
			  color: '#000', // Y 轴名称的颜色
			  fontSize: 14, // Y 轴名称的大小
			  padding: [0, 0, 30, 0] // 调整名称与轴线的距离，上右下左
			}
		  },
		  series: [
			{
			  data: jsonData.yAxis,
			  type: 'bar',
			  itemStyle: {
				color: '#97CCE8',
				borderWidth: 0, // 设置边框宽度
				borderColor: 'black' // 设置边框颜色
			  },
			  barCategoryGap: '10%' // 设置柱子间距为 0
			}
		  ]
		};
		Length_echarts2_option && Length_echarts2_Chart.setOption(Length_echarts2_option);
    },
    error: function(xhr, status, error) {
        console.log(error);
    }
});

/*Exonic and Length distribution  3  */
var proportion_echarts3_Dom = document.getElementById('proportion_echarts3');
var proportion_echarts3_Chart = echarts.init(proportion_echarts3_Dom);
var proportion_echarts3_option;
$.ajax({
    type: 'GET',
    url: './interface/dataset_exons_echarts3_API.php?Dataset=' + Dataset,
    success: function(data) {
		let jsonData = JSON.parse(data);
		proportion_echarts3_option = {
			tooltip: {
				trigger: 'axis',
				axisPointer: {
					type: 'shadow'
				}
			},
		  xAxis: {
			type: 'category',
			data:jsonData.xAxis,
			name: 'Expression Proportion', // X 轴说明文字
			interval: 4, // 设置刻度分隔间隔为 5-1 = 4
			nameLocation: 'center', // 放置在坐标轴末端
			splitNumber: 5, // 控制坐标轴分割的段数
			axisLabel: {
			  textStyle: {
				color: '#000', // 说明文字颜色
				fontSize: 18 // 说明文字大小
			  },
			  margin: 10 // 说明文字与轴线的距离
			},
			nameTextStyle: {
			  color: '#000', // Y 轴名称的颜色
			  fontSize: 14, // Y 轴名称的大小
			  padding: [20, 0, 0, 0] // 调整名称与轴线的距离，上右下左
			}
		  },
		  yAxis: {
			type: 'value',
			data:jsonData.yAxis,
			name: 'counts',
			nameLocation: 'center', // 放置在坐标轴末端
			nameRotate: 90, // 旋转角度，以度为单位
			nameTextStyle: {
			  color: '#000', // Y 轴名称的颜色
			  fontSize: 14, // Y 轴名称的大小
			  padding: [0, 0, 10, 0] // 调整名称与轴线的距离，上右下左
			}
		  },
		  series: [
			{
			  data: jsonData.yAxis,
			  type: 'bar',
			  itemStyle: {
				color: '#97CCE8',
				borderWidth: 0, // 设置边框宽度
				borderColor: 'black' // 设置边框颜色
			  },
			  barCategoryGap: '10%' // 设置柱子间距为 0
			}
		  ]
		};
		proportion_echarts3_option && proportion_echarts3_Chart.setOption(proportion_echarts3_option);
    },
    error: function(xhr, status, error) {
        console.log(error);
    }
});


/*Exonic and Length distribution  4  */
var proportion_echarts4_Dom = document.getElementById('proportion_echarts4');
var proportion_echarts4_Chart = echarts.init(proportion_echarts4_Dom);
var proportion_echarts4_option;
$.ajax({
    type: 'GET',
    url: './interface/dataset_exons_echarts4_API.php?Dataset=' + Dataset,
    success: function(data) {
		let jsonData = JSON.parse(data);
		console.log(232,jsonData)
		proportion_echarts4_option ={
			  xAxis: {
						name: 'Expression Proportion', // X 轴说明文字
						nameLocation: 'center', // 放置在坐标轴中间
						nameTextStyle: {
						  color: '#000', // Y 轴名称的颜色
						  fontSize: 14, // Y 轴名称的大小
						  padding: [20, 0, 0, 0] // 调整名称与轴线的距离，上右下左
						}
				},
			  yAxis: {
						name: 'Number of exons',
						nameLocation: 'center', // 放置在坐标轴末端
						nameRotate: 90, // 旋转角度，以度为单位
						nameTextStyle: {
						  color: '#000', // Y 轴名称的颜色
						  fontSize: 14, // Y 轴名称的大小
						  padding: [0, 0, 10, 0] // 调整名称与轴线的距离，上右下左
						}
				
			  },
			tooltip: {},
			  series: [
				{
				  symbolSize: 10,
				  color:'#000',
				  data: jsonData,
				  type: 'scatter'
				}
			  ]
			};
		proportion_echarts4_option && proportion_echarts4_Chart.setOption(proportion_echarts4_option);
    },
    error: function(xhr, status, error) {
        console.log(error);
    }
});
/*Exonic and Length distribution  5  */
var proportion_echarts5_Dom = document.getElementById('proportion_echarts5');
var proportion_echarts5_Chart = echarts.init(proportion_echarts5_Dom);
var proportion_echarts5_option;
$.ajax({
    type: 'GET',
    url: './interface/dataset_exons_echarts5_API.php?Dataset=' + Dataset,
    success: function(data) {
		let jsonData = JSON.parse(data);
		proportion_echarts5_option ={

			  xAxis: {
						name: 'Expression Proportion', // X 轴说明文字
						nameLocation: 'center', // 放置在坐标轴中间
						nameTextStyle: {
						  color: '#000', // Y 轴名称的颜色
						  fontSize: 14, // Y 轴名称的大小
						  padding: [20, 0, 0, 0] // 调整名称与轴线的距离，上右下左
						}
				},
			  yAxis: {
						name: 'log10exon_length',
						nameLocation: 'center', // 放置在坐标轴末端
						nameRotate: 90, // 旋转角度，以度为单位
						nameTextStyle: {
						  color: '#000', // Y 轴名称的颜色
						  fontSize: 14, // Y 轴名称的大小
						  padding: [0, 0, 10, 0] // 调整名称与轴线的距离，上右下左
						}
				
			  },
			tooltip: {},
			  series: [
				{
				  symbolSize: 10,
				  color:'#000',
				  data: jsonData,
				  type: 'scatter'
				}
			  ]
			};
		proportion_echarts5_option && proportion_echarts5_Chart.setOption(proportion_echarts5_option);
    },
    error: function(xhr, status, error) {
        console.log(error);
    }
});









});/*layui 使用区域收尾*/

}