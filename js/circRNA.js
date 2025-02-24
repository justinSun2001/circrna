

window.onload = function () {
    layui.use(['table', 'form'], function () {/*layui引入*/
        var table = layui.table, form = layui.form, $ = layui.$;

        /*获取get方式传递的BloodCircR_ID*/
        var urlParams = new URLSearchParams(window.location.search);
        var BloodCircR_ID = urlParams.get("BloodCircR_ID");
        console.log('BloodCircR_ID', BloodCircR_ID);

        // 获取所有具有 class="xiala" 的元素
        const xialaElements = document.querySelectorAll('.xiala');

        // 遍历所有 xiala 元素并为其添加点击事件监听器
        xialaElements.forEach(xialaElement => {
            xialaElement.addEventListener('click', function () {
                // 找到点击元素的父元素（即 box）
                const box = this.closest('.box');
                if (box) {
                    // 获取 box 元素的当前高度
                    const currentHeight = box.clientHeight;

                    // 切换高度
                    if (currentHeight === 45) {
                        box.style.height = 'auto';
                        this.style.transform = "rotate(0deg)"
                    } else {
                        box.style.height = '45px';
                        this.style.transform = "rotate(-180deg)"
                    }
                }
            });
        });
        /*basic_info 开始*/
        $.ajax({
            type: 'GET',
            url: './interface/circRNA_table1_API.php?BloodCircR_ID=' + BloodCircR_ID,
            success: function (data) {
                let jsonData = JSON.parse(data);
                $('#BloodCircR_ID').text(jsonData.BloodCircR_ID);
				$('#Uniform_ID').text(jsonData.Uniform_ID);
                $('#BSJ_ID').text(jsonData.BSJ_ID);
				 $('#IsoformID').text(jsonData.IsoformID);
                $('#chr').text(jsonData.chr);
                $('#Strand').text(jsonData.strand);
                $('#circRNA_start').text(jsonData.circRNA_start);
                $('#circRNA_end').text(jsonData.circRNA_end);
                $('#circRNA_type').text(jsonData.circRNA_type);
                $('#Host_gene').html(`<a href="https://www.genecards.org/cgi-bin/carddisp.pl?gene=${jsonData.host_gene}" style="color: #B94A4A; text-decoration: none;" " target="_blank" onmouseover="this.style.textDecoration='underline'" onmouseout="this.style.textDecoration='none'">${jsonData.host_gene}</a>`);
                $('#Ensembl_ID').text(jsonData.ensembl_id);
                $('#Genomic_length').text(jsonData.Genomic_length);
                $('#Spliced_sequence_length').text(jsonData.Spliced_sequence_length);
				$('#IsoformState').text(jsonData.IsoformState);
               $('#Source').text(jsonData.Source);
               let sourceText = jsonData.Source;
               
               // 为包含 Full 的部分添加跳转链接
               sourceText = sourceText.replace(/Full/g, match => {
                   return `<a href="#box4_5" style="color:#B94A4A; cursor: pointer; text-decoration: none;" onmouseover="this.style.textDecoration='underline'" onmouseout="this.style.textDecoration='none'">${match}</a>`;
               });
               
               // 处理 Full 部分独占一行
               sourceText = sourceText.replace(/,?\s*(Full)/g, '$1');
               
               // 处理 FLcircAS_ 部分独占一行
               sourceText = sourceText.replace(/,?\s*((?:FLcircAS_[^,]*,?\s*)+)/g, '<br>$1<br>');
               
               // 处理 IsoCirc_ 部分独占一行
               sourceText = sourceText.replace(/,?\s*((?:IsoCirc_[^,]*,?\s*)+)/g, '<br>$1<br>');
               
               // 移除开头和结尾多余的逗号和换行符
               sourceText = sourceText.replace(/(^<br>)|(<br>$)/g, '');
               
               // 移除连续的多余换行符
               sourceText = sourceText.replace(/(<br>\s*)+/g, '<br>');
               
               // 确保每行不以逗号结尾
               sourceText = sourceText.replace(/,\s*<br>/g, '<br>');
               
               // 更新页面元素
               $('#Source').html(sourceText);
               $('#ConfidenceRank').text(jsonData.ConfidenceRank);




            },
            error: function (xhr, status, error) {
                console.log(error);
            }
        });

        /*Sequence高亮处理函数*/
   function highlightMatchingText(seqValue, Sequence) {
       var outputHtml = "";
       
       // 去除多余空格
       seqValue = seqValue.trim();
       Sequence = Sequence.trim();
   
       // 查找最后一次出现的位置
       var index = Sequence.lastIndexOf(seqValue);
   
       if (index >= 0) { // 如果匹配到
           outputHtml += Sequence.substring(0, index); // 匹配之前的部分
           outputHtml += "<span style='background-color: #E6B7B7;'>" + Sequence.substring(index, index + seqValue.length) + "</span>"; // 高亮匹配部分
           outputHtml += Sequence.substring(index + seqValue.length); // 匹配之后的部分
       } else {
           outputHtml = Sequence; // 未匹配，保持原样
       }
       return outputHtml;
   }

        /*Structure环形图*/
        var Structure_seq_value;
        var StructureDom = document.getElementById('StructureDom');
        var StructureChart = echarts.init(StructureDom);
        var Structureoption;
        $.ajax({
            type: 'GET',
            url: './interface/circRNA_Structure_API.php?BloodCircR_ID=' + BloodCircR_ID,
            success: function (data) {
                let jsonData = JSON.parse(data);
				console.log(jsonData);
				console.log('数据长度:', jsonData.data.length);
	jsonData.data.forEach((item, index) => { console.log(`数据项 ${index + 1}:`, item); });
                Structureoption = {
                    backgroundColor: 'rgba(0,0,0,0)',
                    tooltip: {
                        trigger: 'item'
                    },
                    toolbox: {
                        show: true,
                        feature: {

                            saveAsImage: {show: true,
							title:'SaveAsImage', backgroundColor: 'rgba(0,0,0,0)'
							},
                        }
                    },
                    series: [
                        {
                            name: '',
                            type: 'pie',
                            radius: ['75%', '90%'],
                            center: ['50%', '50%'],
                            data: jsonData.data,
                            label: {
                                position: 'inside',
                                rotate: 'tangential',
                                color: 'rgba(255,255,255,1)',
                                fontSize: 20
                            },
                            labelLine: {
                                show: false,
                                lineStyle: {
                                    color: 'rgba(0, 0, 0, 0.3)'
                                },
                                smooth: 1,
                                length: 1,
                                length2: 1,
                                position: 'inside'
                            },
                            itemStyle: {
                                color: '#E8D4A9',
                                borderRadius: 0,
                                borderColor: '#fff',
                                borderWidth: 2
                            },
                            animationType: 'scale',
                            animationEasing: 'elasticOut',
                            animationDelay: function (idx) {
                                return Math.random() * 200;
                            }
                        }
                    ]
                };
                Structureoption && StructureChart.setOption(Structureoption);
                /*基本数据渲染*/

                //Sequence 行
                document.getElementById('Sequence_value').innerHTML = jsonData.Sequence[0];
                Structure_seq_value = jsonData.Sequence[0];


                // exons 行
                var flexCenterElement = document.querySelector(".exonsbox");
                for (var i = 0; i < jsonData.exonData.length; i++) {
                    // 获取当前 exonData 对象
                    var exonDataObject = jsonData.exonData[i];
                    // 创建一个新的 capsule1 元素
                    var capsuleElement = document.createElement("div");
                    capsuleElement.className = "capsule1";
                    var capsuleLeft = document.createElement("div");
                    capsuleLeft.className = "capsule1_left";
                    capsuleLeft.textContent = exonDataObject.name;
                    var capsuleRight = document.createElement("div");
                    capsuleRight.className = "capsule1_right";
                    capsuleRight.textContent = exonDataObject.exonvalue;
                    capsuleElement.appendChild(capsuleLeft);
                    capsuleElement.appendChild(capsuleRight);
                    // 将 capsule1 元素添加到包含 class="flexcenter" 的 <td> 元素中
                    flexCenterElement.appendChild(capsuleElement);
                }
                // 获取所有的 capsuleElement 元素
                var capsuleElements = document.querySelectorAll(".capsule1");
                // 遍历每个 capsuleElement 元素，为其添加鼠标触碰事件监听器
                capsuleElements.forEach(function (capsuleElement) {
                    capsuleElement.addEventListener("click", function (event) {

                        // 重置所有颜色为 #8DCBDF
                        Structureoption.series[0].data.forEach(function (dataItem, index) {
                            Structureoption.series[0].data[index].itemStyle = {
                                color: '#E8D4A9'
                            };
                        });

                        var capsule1LeftValue = event.currentTarget.querySelector(".capsule1_left").textContent;
                        console.log("capsule1_left value: " + capsule1LeftValue);

                        // 根据触碰对象查找name相同的
                        var matchedObject = jsonData.newData.find(function (obj) {
                            return obj.name === capsule1LeftValue;
                        });

                        console.log(66, capsule1LeftValue);

                        var seqValue = matchedObject.seq; // 拿到name对应的seq


                        // 获取高亮后的 HTML
                        var highlightedHtml = highlightMatchingText(seqValue, jsonData.Sequence[0]);
                        document.getElementById('Sequence_value').innerHTML = highlightedHtml;

                        // 修改Structureoption中相应的数据项颜色
                        Structureoption.series[0].data.forEach(function (dataItem, index) {
                            if (dataItem.name === capsule1LeftValue) {
                                Structureoption.series[0].data[index].itemStyle = {
                                    color: '#B94A4A' // 设置不同的颜色，这里使用橙色
                                };
                            }
                        });

                        // 重新渲染图表
                        Structureoption && StructureChart.setOption(Structureoption);
                    });

                    /*capsuleElement.addEventListener("mouseout",function(){
                        document.getElementById('Sequence_value').innerHTML = jsonData.Sequence[0];
                    })*/
                });

                StructureChart.on('mouseover', function (params) {//鼠标触碰事件
                    //根据触碰对象查找name相同的
                    var matchedObject = jsonData.newData.find(function (obj) {
                        return obj.name === params.name;
                    });
                    var seqValue = matchedObject.seq;//拿到name对应的seq


                    // 获取高亮后的 HTML
                    var highlightedHtml = highlightMatchingText(seqValue, jsonData.Sequence[0]);
                    document.getElementById('Sequence_value').innerHTML = highlightedHtml;

                });
                StructureChart.on('mouseout', function (params) {//鼠标离开事件  复原
                    document.getElementById('Sequence_value').innerHTML = jsonData.Sequence[0];
                });


            },
            error: function (xhr, status, error) {
                console.log(error);
            }
        });
        /*ORF的触碰展开行与数据获取展示*/

        const capsuleDivs = document.querySelectorAll('.capsule2');
        const orfInfoDiv = document.getElementById('ORF_INFO');

        $.ajax({
            type: 'GET',
            url: './interface/circRNA_Structure_ORF_API.php?BloodCircR_ID=' + BloodCircR_ID,
            success: function (data) {
                let jsonData = JSON.parse(data);
                var ORFCont = document.querySelector(".ORF_cont");
                var capsules = document.querySelectorAll(".capsule2");

                // 用于跟踪当前显示的 capsule
                var activeCapsule = null;

                jsonData.data.forEach(function (item) {
                    var capsule = document.createElement("div");
                    capsule.classList.add("capsule2");
                    capsule.textContent = item.name;

                    capsule.addEventListener("click", function () {
                        if (activeCapsule === capsule) {
                            // 如果点击的是当前显示的 capsule，则隐藏
                            ORFCont.innerHTML = '';
                            document.getElementById('Sequence_value').innerHTML = Structure_seq_value;
                            orfInfoDiv.style.display = 'none';
                            activeCapsule = null;
                        } else {
                            // 否则显示新的 ，并更新
                            ORFCont.innerHTML = item.value;
                            console.log(265, item.orf_nucl)
                            var highlightedHtml = highlightMatchingText(item.orf_nucl, Structure_seq_value);
                            document.getElementById('Sequence_value').innerHTML = highlightedHtml;
                            orfInfoDiv.style.display = 'block';
                            if (activeCapsule) {
                                activeCapsule.classList.remove('active');
                            }
                            capsule.classList.add('active');
                            activeCapsule = capsule;
                        }
                    });

                    //document.querySelector(".ORF_box").appendChild(capsule);
                });
            }
        });


        //IRES 单行数据渲染
        $.ajax({
            type: 'GET',
            url: './interface/circRNA_Structure_IRES_API.php?BloodCircR_ID=' + BloodCircR_ID,
            success: function (data) {
                let jsonData = JSON.parse(data);
                jsonData.data.forEach(function (item) {
                    var capsule = document.createElement("div");
                    capsule.classList.add("capsule3");
                    capsule.textContent = item.IRES;
                    //document.querySelector(".IRES_box").appendChild(capsule);
                    // 获取所有的 capsuleElement 元素
                    var capsule3_Elements = document.querySelectorAll(".capsule3");
                    // 遍历每个 capsuleElement 元素，为其添加鼠标触碰事件监听器
                    capsule3_Elements.forEach(function (capsule3_Elements) {
                        capsule3_Elements.addEventListener("click", function (event) {
                            var capsule3Value = event.target.textContent;
                            //根据触碰对象查找name相同的
                            var matchedObject = jsonData.data.find(function (item) {
                                return item.IRES === capsule3Value;
                            });
                            var seqValue = matchedObject.IRES_seq;//拿到name对应的seq
                            /**********/
                            function highlightMatchingText2(seqValue, Sequence) {
                                var outputHtml = "";
                                var index = Sequence.indexOf(seqValue);

                                if (index >= 0) {
                                    // 查到正向匹配，进行高亮处理
                                    outputHtml += Sequence.substring(0, index);
                                    outputHtml += "<span style='color: #B94A4A;'>" + seqValue + "</span>";
                                    outputHtml += Sequence.substring(index + seqValue.length);
                                } else {
                                    outputHtml = Sequence; // 如果没有正向匹配，则保持原样
                                    console.log(312, seqValue.length);

                                    for (let i = 0; i < seqValue.length; i++) {
                                        var newseqValue = seqValue.slice(0, seqValue.length - i);
                                        index = Sequence.indexOf(newseqValue);
                                        if (index >= 0) {
                                            // 查到逆向匹配，进行高亮处理
                                            outputHtml = Sequence.substring(0, index);
                                            outputHtml += "<span style='color: #B94A4A;'>" + newseqValue + "</span>";
                                            outputHtml += Sequence.substring(index + newseqValue.length);
                                            var newseqValue2 = seqValue.slice(seqValue.length - i, seqValue.length);
                                            var index2 = outputHtml.indexOf(newseqValue2);

                                            outputHtml2 = outputHtml.slice(index2 + 1, outputHtml.length);
                                            outputHtml2 = "<span style='color: #B94A4A;'>" + newseqValue2 + "</span>" + outputHtml2;
                                            outputHtml = outputHtml2
                                            break;
                                        }
                                    }
                                }

                                return outputHtml;
                            }


                            // 获取高亮后的 HTML
                            var highlightedHtml = highlightMatchingText2(seqValue, Structure_seq_value);
                            document.getElementById('Sequence_value').innerHTML = highlightedHtml;

                        });
                        /*
                        capsule3_Elements.addEventListener("mouseout",function(){
                            document.getElementById('Sequence_value').innerHTML = Structure_seq_value;
                        })
                        */
                    });

                });
            }
        });

    

/*Alias for circRNA 开始*/
$.ajax({
    type: 'GET',
    url: './interface/circRNA_alias_info_API.php?BloodCircR_ID=' + BloodCircR_ID,
    success: function (data) {
        let jsonData = JSON.parse(data);

        function createLinkOrText(elementId, dataValue, urlPrefix) {
            if (dataValue === 'N/A') {
                $('#' + elementId).html(dataValue);
            } else {
                $('#' + elementId).html(`<a href="${urlPrefix}${dataValue}" target="_blank">${dataValue}</a>`);
            }
        }

        createLinkOrText('circAtlas', jsonData.circAtlas, 'https://ngdc.cncb.ac.cn/circatlas/circ_detail1.php?ID=');
        createLinkOrText('circBase', jsonData.circBase, 'http://www.circbase.org/cgi-bin/singlerecord.cgi?id=');
        $('#exorBase').html(jsonData.exorBase);  // exorBase doesn't seem to need a link
        createLinkOrText('PltDB', jsonData.PltDB, 'https://www.pltdb-hust.com/#/home');
        createLinkOrText('Transcirc', jsonData.Transcirc, 'https://www.biosino.org/transcirc/detail/');
        createLinkOrText('FLcircAS', jsonData.FLcircAS, 'https://cosbi.ee.ncku.edu.tw/FL-circAS/BSJ_Detail/?species=human&id=');
    },
    error: function (xhr, status, error) {
        console.log(error);
    }
});



        /*evidence 雷达图*/
        /*
        var evidence_echarts_Dom = document.getElementById('evidence_echarts');
        var evidence_echarts_Chart = echarts.init(evidence_echarts_Dom);
        var evidence_echarts_option;
         $.ajax({
            type: "GET", // 或 "POST"，取决于您的接口要求的请求方式
            url: './interface/circRNA_evidence_echarts_API.php?BloodCircR_ID=' + BloodCircR_ID,
            dataType: "json",
            success: function(data) {

                evidence_echarts_option = {
                  title: {
                    text: ''
                  },
                  legend: {},
                  radar: {
                    indicator: [
                      { name: 'm6A sites', max: 1,min:-0.25 },
                      { name: 'Ribosome_polysome_profiling', max: 1,min:-0.25 },
                      { name: 'Translation_initiation_site', max: 1,min:-0.25 },
                      { name: 'ORF', max: 1 ,min:-0.25},
                      { name: 'IRES_sequence', max: 1,min:-0.25 },
                      { name: 'SeqComp', max: 1,min:-0.25 },
                      { name: 'MS_evidence', max: 1 ,min:-0.25}
                    ]
                  },
                  series: [
                    {
                      name: 'Budget vs spending',
                      type: 'radar',
                      data: [
                        {
                          value: data.data[0].value,
                          name: data.data[0].name
                        }
                      ]
                    }
                  ]
                };

                evidence_echarts_option && evidence_echarts_Chart.setOption(evidence_echarts_option);
            },
            error: function(xhr, status, error) {
                console.log("请求失败: " + error);
            }
        });
        */

        /*RBP 堆叠柱状*/
        var rbp;
        var Column_one;
        var Column_two;
        var Column_three;
        $.ajax({
            type: 'GET',
            url: './interface/circRNA_RBP_echarts_API.php?BloodCircR_ID=' + BloodCircR_ID,
            success: function (data) {
                let jsonData = JSON.parse(data);
                rbp = jsonData.rbp;
                Column_one = jsonData.Column_one;
                Column_two = jsonData.Column_two;
                Column_three = jsonData.Column_three;
                let RBP_echarts_Dom = document.getElementById('RBP_echarts');
                let RBP_echarts_Chart = echarts.init(RBP_echarts_Dom);
                let RBP_echarts_option;
                RBP_echarts_option = {
                    legend: {
                        left: 20,
        textStyle: { // 设置 legend 字体样式
            fontSize: 18, // 设置字体大小
            color: '#333333' ,// 可以设置字体颜色
			fontFamily: 'Helvetica',
        }
                    },
                    grid: {
                        left: '5%',
                        right: '1%',
                        bottom: '1%',
                        containLabel: true
                    },
                    tooltip: {
                        trigger: 'axis',
                        axisPointer: {
                            type: 'shadow'
                        }
                    },
                    toolbox: {
                        show: true,
                        feature: {

                            saveAsImage: {show: true,

			            title: 'SaveAsImage' , backgroundColor: 'rgba(0,0,0,0)'}
                        }
                    },
                    xAxis: {
                        type: 'category',
                        data: rbp,
                        axisLabel: {
                            rotate: 90,  // 旋转标签
                            color: '#333333',  // 设置 x 轴标签的字体颜色（红色）
                            textStyle: { // 标题的文本样式
                            	fontSize: 16,
								fontFamily: 'Helvetica'
                            }
											  
                        }
                    },
                    yAxis: {
                        type: 'value',
						axisLabel: {
						   
						    color: '#333333',  // 设置 x 轴标签的字体颜色（红色）
						                         
						}
                    },
                    series: [
                        {
                            name: 'Binding_sites',
                            data: Column_one,
                            type: 'bar',
                            stack: 'total',
						
                            backgroundStyle: {
                                color: 'rgba(229, 178, 178,1)'
                            }
                        }
                    ], backgroundColor: 'rgba(0,0,0,0)'
                };
                RBP_echarts_option && RBP_echarts_Chart.setOption(RBP_echarts_option);
            },
            error: function (xhr, status, error) {
                console.log(error);
            }
        });

        /*miRNA 关系图*/
        let miRNA_echarts_Dom = document.getElementById('miRNA_1_echarts');
        let miRNA_echarts_Chart = echarts.init(miRNA_echarts_Dom);
        let miRNA_echarts_option;//图表渲染选项
        miRNA_echarts_option = {
            toolbox: {
                show: true,
                feature: {

                    saveAsImage: {show: true,
					title: 'SaveAsImage' }
                }
            },
            series: [
                {

                    type: 'graph',
                    layout: 'force',//力导图 自动连接
                    symbolSize: 35,
                    roam: true,
                    label: {
                        show: true,
                        textStyle: {
                            color: '#fff',
                            textBorderColor: 'black',  // 设置文本描边颜色
                            textBorderWidth: 3 // 设置文本描边宽度
                        }
                    },
                    edgeSymbol: ['circle'],
                    edgeSymbolSize: [4, 10],
                    edgeLabel: {
                        fontSize: 20,
                        textStyle: {
                            color: '#8DCBDF',
                            textBorderColor: 'black',  // 设置文本描边颜色
                            textBorderWidth: 5  // 设置文本描边宽度
                        },
                    },
                    itemStyle: {
                        color: function (params) {
                            if (params.dataIndex == 0) {
                                return '#91cc75'
                            } else {
                                return '#5470c6'
                            }
                        }
                    },
                    force: {
                        repulsion: 360,
                        edgeLength: [10, 50],
                        layoutAnimation: false,
                    },
                    lineStyle: {
                        opacity: 0.9,
                        width: 2,
                        curveness: 0
                    }
                }
            ],
			 backgroundColor: 'rgba(0,0,0,0)'
        };
        $.ajax({
            type: 'GET',
            url: './interface/circRNA_miRNA_echarts_API.php?BloodCircR_ID=' + BloodCircR_ID + '&choose=' + '8mer-1a',
            success: function (data) {
                let jsonData = JSON.parse(data);
                console.log(1212, jsonData)
                //数据赋值给绘制选项
                miRNA_echarts_option.series[0].data = jsonData.data;
                miRNA_echarts_option.series[0].links = jsonData.links;
                miRNA_echarts_Chart.clear();
                miRNA_echarts_option && miRNA_echarts_Chart.setOption(miRNA_echarts_option);
            },
            error: function (xhr, status, error) {
                console.log(error);
            }
        });
        /*miRNA 关系图2*/
        let miRNA_echarts2_Dom = document.getElementById('miRNA_2_echarts');
        let miRNA_echarts2_Chart = echarts.init(miRNA_echarts2_Dom);
        let miRNA_echarts2_option;//图表渲染选项
        miRNA_echarts2_option = {
            toolbox: {
                show: true,
                feature: {

                    saveAsImage: {show: true,
					title: 'SaveAsImage' ,}
                }
            },
            series: [
                {

                    type: 'graph',
                    layout: 'force',//力导图 自动连接
                    symbolSize: 35,
                    roam: true,
                    label: {
                        show: true,
                        textStyle: {
                            color: '#fff',
                            textBorderColor: 'black',  // 设置文本描边颜色
                            textBorderWidth: 3 // 设置文本描边宽度
                        }
                    },
                    edgeSymbol: ['circle'],
                    edgeSymbolSize: [4, 10],
                    edgeLabel: {
                        fontSize: 20,
                        textStyle: {
                            color: '#8DCBDF',
                            textBorderColor: 'black',  // 设置文本描边颜色
                            textBorderWidth: 5  // 设置文本描边宽度
                        },
                    },
                    itemStyle: {
                        color: function (params) {
                            if (params.dataIndex == 0) {
                                return '#91cc75'
                            } else {
                                return '#5470c6'
                            }
                        }
                    },
                    force: {
                        repulsion: 360,
                        edgeLength: [10, 50],
                        layoutAnimation: false,
                    },
                    lineStyle: {
                        opacity: 0.9,
                        width: 2,
                        curveness: 0
                    }
                }
            ], backgroundColor: 'rgba(0,0,0,0)'
        };
        $.ajax({
            type: 'GET',
            url: './interface/circRNA_miRNA_echarts_API.php?BloodCircR_ID=' + BloodCircR_ID + '&choose=' + '7mer-m8',
            success: function (data) {
                let jsonData = JSON.parse(data);
                //数据赋值给绘制选项

                console.log('miRNA_echarts2_option', jsonData)
                miRNA_echarts2_option.series[0].data = jsonData.data;
                miRNA_echarts2_option.series[0].links = jsonData.links;
                miRNA_echarts2_Chart.clear();
                miRNA_echarts2_option && miRNA_echarts2_Chart.setOption(miRNA_echarts2_option);
            },
            error: function (xhr, status, error) {
                console.log(error);
            }
        });
        /*miRNA 关系图3*/
        let miRNA_echarts3_Dom = document.getElementById('miRNA_3_echarts');
        let miRNA_echarts3_Chart = echarts.init(miRNA_echarts3_Dom);
        let miRNA_echarts3_option;//图表渲染选项
        miRNA_echarts3_option = {
            toolbox: {
                show: true,
                feature: {

                    saveAsImage: {show: true,
					title: 'SaveAsImage' }
                }
            },
            series: [
                {

                    type: 'graph',
                    layout: 'force',//力导图 自动连接
                    symbolSize: 35,
                    roam: true,
                    label: {
                        show: true,
                        textStyle: {
                            color: '#fff',
                            textBorderColor: 'black',  // 设置文本描边颜色
                            textBorderWidth: 3 // 设置文本描边宽度
                        }
                    },
                    edgeSymbol: ['circle'],
                    edgeSymbolSize: [4, 10],
                    edgeLabel: {
                        fontSize: 20,
                        textStyle: {
                            color: '#8DCBDF',
                            textBorderColor: 'black',  // 设置文本描边颜色
                            textBorderWidth: 5  // 设置文本描边宽度
                        },
                    },
                    itemStyle: {
                        color: function (params) {
                            if (params.dataIndex == 0) {
                                return '#91cc75'
                            } else {
                                return '#5470c6'
                            }
                        }
                    },
                    force: {
                        repulsion: 360,
                        edgeLength: [10, 50],
                        layoutAnimation: false,
                    },
                    lineStyle: {
                        opacity: 0.9,
                        width: 2,
                        curveness: 0
                    }
                }
            ]
        };
        $.ajax({
            type: 'GET',
            url: './interface/circRNA_miRNA_echarts_API.php?BloodCircR_ID=' + BloodCircR_ID + '&choose=' + '7mer-1a',
            success: function (data) {
                let jsonData = JSON.parse(data);
                //数据赋值给绘制选项
                miRNA_echarts3_option.series[0].data = jsonData.data;
                miRNA_echarts3_option.series[0].links = jsonData.links;
                miRNA_echarts3_Chart.clear();
                miRNA_echarts3_option && miRNA_echarts3_Chart.setOption(miRNA_echarts3_option);
            },
            error: function (xhr, status, error) {
                console.log(error);
            }
        });
        /*miRNA 关系图4*/
        let miRNA_echarts4_Dom = document.getElementById('miRNA_4_echarts');
        let miRNA_echarts4_Chart = echarts.init(miRNA_echarts4_Dom);
        let miRNA_echarts4_option;//图表渲染选项
        miRNA_echarts4_option = {
            toolbox: {
                show: true,
                feature: {

                    saveAsImage: {show: true,title: 'SaveAsImage' }
                }
            },
            series: [
                {

                    type: 'graph',
                    layout: 'force',//力导图 自动连接
                    symbolSize: 35,
                    roam: true,
                    label: {
                        show: true,
                        textStyle: {
                            color: '#fff',
                            textBorderColor: 'black',  // 设置文本描边颜色
                            textBorderWidth: 3 // 设置文本描边宽度
                        }
                    },
                    edgeSymbol: ['circle'],
                    edgeSymbolSize: [4, 10],
                    edgeLabel: {
                        fontSize: 20,
                        textStyle: {
                            color: '#8DCBDF',
                            textBorderColor: 'black',  // 设置文本描边颜色
                            textBorderWidth: 5  // 设置文本描边宽度
                        },
                    },

                    itemStyle: {
                        color: function (params) {
                            if (params.dataIndex == 0) {
                                return '#91cc75'
                            } else {
                                return '#5470c6'
                            }
                        }
                    },
                    force: {
                        repulsion: 360,
                        edgeLength: [10, 50],
                        layoutAnimation: false,
                    },
                    lineStyle: {
                        opacity: 0.9,
                        width: 2,
                        curveness: 0
                    }
                }
            ]
        };
        $.ajax({
            type: 'GET',
            url: './interface/circRNA_miRNA_echarts_API.php?BloodCircR_ID=' + BloodCircR_ID + '&choose=' + '6mer',
            success: function (data) {
                let jsonData = JSON.parse(data);
                //数据赋值给绘制选项
                miRNA_echarts4_option.series[0].data = jsonData.data;
                miRNA_echarts4_option.series[0].links = jsonData.links;
                miRNA_echarts4_Chart.clear();
                miRNA_echarts4_option && miRNA_echarts4_Chart.setOption(miRNA_echarts4_option);
            },
            error: function (xhr, status, error) {
                console.log(error);
            }
        });


        /*Co-expression network 关系图*/
        /*
                let Co_expression_echarts_Dom = document.getElementById('Co_expression_echarts');
                let Co_expression_echarts_Chart = echarts.init(Co_expression_echarts_Dom);
                let Co_expression_echarts_option;//图表渲染选项
                Co_expression_echarts_option = {
                    series: [
                        {

                            type: 'graph',
                            layout: 'force',//力导图 自动连接
                            symbolSize: 35,
                            roam: true,
                            label: {
                                show: true,
                                textStyle: {
                                    color: '#fff',
                                    textBorderColor: 'black',  // 设置文本描边颜色
                                    textBorderWidth: 3 // 设置文本描边宽度
                                }
                            },
                            edgeSymbol: ['circle'],
                            edgeSymbolSize: [4, 10],
                            edgeLabel: {
                                fontSize: 20,
                                textStyle: {
                                    color: '#8DCBDF',
                                    textBorderColor: 'black',  // 设置文本描边颜色
                                    textBorderWidth: 5  // 设置文本描边宽度
                                },
                            },
                            itemStyle: {
                                color: function (params) {
                                    if (params.dataIndex == 0) {
                                        return '#7CB508'
                                    } else {
                                        return '#008AFF'
                                    }
                                }
                            },
                            force: {
                                repulsion: 500,
                                edgeLength: [10, 50],
                                layoutAnimation: false,
                            },
                            lineStyle: {
                                opacity: 0.9,
                                width: 2,
                                curveness: 0
                            }
                        }
                    ]
                };
                $.ajax({
                    type: 'GET',
                    url: './interface/circRNA_Co_expression_echarts_API.php?BloodCircR_ID=' + BloodCircR_ID,
                    success: function (data) {
                        let jsonData = JSON.parse(data);
                        console.log(773,jsonData)
                        //数据赋值给绘制选项
                        Co_expression_echarts_option.series[0].data = jsonData.data;
                        Co_expression_echarts_option.series[0].links = jsonData.links;
                        Co_expression_echarts_Chart.clear();
                        Co_expression_echarts_option && Co_expression_echarts_Chart.setOption(Co_expression_echarts_option);
                    },
                    error: function (xhr, status, error) {
                        console.log(error);
                    }
                });
        */
        let Co_expression_echarts_Dom = document.getElementById('Co_expression_echarts');
        let Co_expression_echarts_Chart = echarts.init(Co_expression_echarts_Dom);
        let Co_expression_echarts_option;//图表渲染选项
        Co_expression_echarts_option = {
            tooltip: {},
            toolbox: {
                show: true,
                feature: {

                    saveAsImage: {show: true,
					title: 'SaveAsImage' }
                }
            },
            legend: [
                {
                    data: ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I']
                }
            ],
            series: [
                {
                    name: 'gene',
                    type: 'graph',
                    layout: 'none',
                    //data: graph.nodes,
                    //links: graph.links,
                    categories: [{
                        name: 'A',
                        itemStyle: {color: 'red'}
                    }, {name: 'B'}, {name: 'C'}, {name: 'D'}, {name: 'E'}, {name: 'F'}, {name: 'G'}, {name: 'H'}, {name: 'I'}],
                    roam: true,
                    label: {
                        show: true,
                        position: 'right',
                        formatter: '{b}'
                    },
                    labelLayout: {
                        hideOverlap: true
                    },
                    scaleLimit: {
                        min: 0.4,
                        max: 2
                    },
                    lineStyle: {
                        color: 'source',
                        curveness: 0.3
                    }
                }
            ]
        };
        $.ajax({
            type: 'GET',
            url: './interface/circRNA_network_API.php?BloodCircR_ID=' + BloodCircR_ID,
            success: function (data) {
                let jsonData = JSON.parse(data);
                console.log(1212, jsonData)
                //数据赋值给绘制选项
                // 遍历节点数据，为重复名称的节点添加唯一标识

                Co_expression_echarts_option.series[0].data = jsonData.nodes;
                Co_expression_echarts_option.series[0].links = jsonData.links;
                Co_expression_echarts_Chart.clear();
                Co_expression_echarts_option && Co_expression_echarts_Chart.setOption(Co_expression_echarts_option);
            },
            error: function (xhr, status, error) {
                console.log(error);
            }
        });

        /*circRNA Expression 箱线图 1 */
        var datasets;
        var expression;
        var Expression_echarts1_Dom = document.getElementById('Expression_echarts1');
        var Expression_echarts1_Chart = echarts.init(Expression_echarts1_Dom);
        var Expression_echarts1_option;
        $.ajax({
            type: 'GET',
            url: './interface/circRNA_Expression_echarts1_API.php?BloodCircR_ID=' + BloodCircR_ID,
            success: function (data) {
                let jsonData = JSON.parse(data);
                let categories = jsonData.datasets
                let boxplotData = jsonData.boxplotData
                let scatterData = jsonData.scatterData
                Expression_echarts1_option = {
                    title: {
                        text: ''
                    },
                    tooltip: {
                        trigger: 'axis',
                        axisPointer: {
                            type: 'shadow'
                        }
                    }, toolbox: {
                        show: true,
                        feature: {

                            saveAsImage: {show: true,
							title: 'SaveAsImage' }
                        }
                    },
                    legend: {
                        data: ['Dataset', 'Sample']
                    },
                    xAxis: {
                        type: 'category',
                        data: categories,
                        axisLabel: {
                            rotate: 45// 负值表示逆时针旋转标签
                        }
                    },
                    yAxis: {
                        type: 'value',
                        name: 'Expression'
                    },
                    series: [
                        {
                            name: 'Dataset',
                            type: 'boxplot',
                            data: boxplotData,
                            tooltip: {
                                formatter: function (param) {
                                    return [
                                        'name: ' + param.name,
                                        'min: ' + param.data[0],
                                        'Q1: ' + param.data[1],
                                        'median: ' + param.data[2],
                                        'Q3: ' + param.data[3],
                                        'max: ' + param.data[4]
                                    ].join('<br/>');
                                }
                            }
                        },
                        {
                            name: 'Sample',
                            type: 'scatter',
                            data: scatterData,
                            symbolSize: 5,  // 设置点的大小
                            tooltip: {
                                formatter: function (param) {
                                    return param.seriesName + '<br/>' + param.data.name + ': ' + param.data.value;
                                }
                            }
                        }
                    ]
                };
                Expression_echarts1_option && Expression_echarts1_Chart.setOption(Expression_echarts1_option);

            },
            error: function (xhr, status, error) {
                console.log(error);
            }
        });

        /*tab切换*/
        $(".tabbox").hide();
        $("#tabbox1").show();
        showExpression3();
        $(".tabbtnbox button").click(function () {
            // 隐藏所有的div
            // $(".tabbox").hide();
            $(".tabbtnbox button").removeClass("act");
            // 获取点击的按钮的索引
            var index = $(this).index();
            $(this).addClass("act")
            // 显示对应索引的div
            // $(".tabbox").eq(index).show();
            console.log(index);
            if(index===0){
                showExpression3();
            }else if(index===1){
                showExpression4();
            }else if(index===2){
                showExpression5();
            }
        });

        $(".tabbox_miRNA").hide();
        $("#tabbox_miRNA_1").show();
        $(".tabbtnbox_miRNA button").click(function () {
            // 隐藏所有的div
            $(".tabbox_miRNA").hide();
            $(".tabbtnbox_miRNA button").removeClass("act");
            // 获取点击的按钮的索引
            var index = $(this).index();
            $(this).addClass("act")
            // 显示对应索引的div
            $(".tabbox_miRNA").eq(index).show();
        });

        $(".tabbox_go").hide();
        $("#tabbox_go_1").show();
        $(".tabbtnbox_go button").click(function () {
            // 隐藏所有的div
            $(".tabbox_go").hide();
            $(".tabbtnbox_go button").removeClass("act");
            // 获取点击的按钮的索引
            var index = $(this).index();
            $(this).addClass("act")
            // 显示对应索引的div
            $(".tabbox_go").eq(index).show();
            
        });

        $(".tabbox_pathways").hide();
        $("#tabbox_pathways_1").show();
        $(".tabbtnbox_pathways button").click(function () {
            // 隐藏所有的div
            $(".tabbox_pathways").hide();
            $(".tabbtnbox_pathways button").removeClass("act");
            // 获取点击的按钮的索引
            var index = $(this).index();
            $(this).addClass("act")
            // 显示对应索引的div
            $(".tabbox_pathways").eq(index).show();
        });

        Expression_echarts1_Chart.on('click', function (params) {
            console.log(792, params.name)
            var Expression_echarts2_Dom = document.getElementById('Expression_echarts2');
            var Expression_echarts2_Chart = echarts.init(Expression_echarts2_Dom);
            var Expression_echarts2_option;
            Expression_echarts2_Dom.style.display = 'block';
            $.ajax({
                type: 'GET',
                url: './interface/circRNA_Expression_echarts2_API.php?BloodCircR_ID=' + BloodCircR_ID + '&Dataset=' + params.name,
                success: function (data) {
                    let jsonData = JSON.parse(data);
                    console.log(802, jsonData)
                    Expression_echarts2_option = {
                        title: [
                            {
                                text: 'Comparison of ' + BloodCircR_ID + ' in ' + params.name,
                                left: 'center'
                            },
                            {
                                text: '0: control_exp                                                     1: fever_exp',
                                textStyle: {
                                    fontWeight: 'normal',
                                    fontSize: 14,
                                    lineHeight: 20,
                                },
                                left: 'center',
                                top: '90%'
                            }
                        ],
                        dataset: [
                            {
                                // prettier-ignore
                                source: jsonData.data
                            },
                            {
                                transform: {
                                    type: 'boxplot',
                                }
                            },
                            {
                                fromDatasetIndex: 1,
                                fromTransformResult: 1
                            }
                        ],
                        tooltip: {
                            trigger: 'item',
                            axisPointer: {
                                type: 'shadow'
                            }
                        },
                        grid: {
                            left: '10%',
                            right: '10%',
                            bottom: '15%'
                        },
                        toolbox: {
                            show: true,
                            feature: {

                                saveAsImage: {show: true,
								title: 'SaveAsImage' }
                            }
                        },
                        xAxis: {
                            name:'Disease',
                            type: 'category',
                            boundaryGap: true,
                            nameGap: 30,
                            splitArea: {
                                show: false
                            },
                            splitLine: {
                                show: false
                            }
                        },
                        yAxis: {
                            type: 'value',
                            name: 'TPM',
                            splitArea: {
                                show: true
                            }
                        },
                        series: [
                            {
                                name: 'boxplot',
                                type: 'boxplot',
                                datasetIndex: 1
                            },
                            {
                                name: 'outlier',
                                type: 'scatter',
                                datasetIndex: 2
                            }
                        ]
                    };
                    Expression_echarts2_option && Expression_echarts2_Chart.setOption(Expression_echarts2_option);
                },
                error: function (xhr, status, error) {
                    console.log(error);
                }
            });

        });


        /*circRNA Expression 箱线图 3 */
        function showExpression3(){
            var datasets;
            var expression;
            var Expression_echarts3_Dom = document.getElementById('Expression_echarts');
            var Expression_echarts3_Chart = echarts.init(Expression_echarts3_Dom);
            var Expression_echarts3_option;
            $.ajax({
                type: 'GET',
                url: './interface/circRNA_Expression_echarts3_API.php?BloodCircR_ID=' + BloodCircR_ID,
                success: function (data) {
                    let jsonData = JSON.parse(data);
                    let categories = jsonData.disease;
                    let boxplotData = jsonData.boxplotData;
                    let outlierData = jsonData.outlierData;
                    console.log(boxplotData);
                    console.log(outlierData);
				   // 创建包含 categories 和对应 boxplotData、outlierData 的数组，用于排序
				let combinedData = categories.map((category, index) => {
                        return {
                            category: category,
                            boxplot: boxplotData[index],
                            // outlier: outlierData[index]
                        };
                    });
                    // 对 combinedData 进行排序，按照 category 排序
                    combinedData.sort(function(a, b) {
                        return a.category.localeCompare(b.category);
                    });
                    // 重新分配排序后的 categories、boxplotData、outlierData
                    categories = combinedData.map(item => item.category);
                    boxplotData = combinedData.map(item => item.boxplot);
                    // outlierData = combinedData.map(item => item.outlier);
                    Expression_echarts3_option = {

                        tooltip: {
                            trigger: 'axis',
                            axisPointer: {
                                type: 'shadow'
                            }
                        },
						grid: {
						    left: '12%',
						    right: '10%',
						    bottom: '26%'
						},
                        toolbox: {
                            show: true,
                            feature: {

                                saveAsImage: {show: true,
								title: 'SaveAsImage' }
                            }
                        },
                        xAxis: {
                            name:'Disease',
                            type: 'category',
                            data: categories,
								axisTick: { alignWithLabel: true, },
                            axisLabel: {
                                rotate:30,  // 旋转标签
                                color: '#333333',  // 设置 x 轴标签的字体颜色（红色）
                                      textStyle: { // 标题的文本样式
                                      	fontSize: 16
                                      }               
								
                            },
                            nameLocation: 'end',
                            nameGap: 20,
                            nameTextStyle: {  // 设置标题文本的样式
                                fontFamily: 'Helvetica',  // 字体系列
                                fontSize: 16,  // 字体大小
                                color: '#333333',  // 字体颜色
                                fontWeight: 'bold'  // 字体粗细
                            }
                        },
                        yAxis: {
                            type: 'value',
                            name: 'TPM ',
					        axisLabel: {
					                        color: '#333333',  // 设置 y 轴标签的字体颜色（蓝色）
					            textStyle: { // 标题的文本样式
					            	fontSize: 16
					            }           
					                    },
                            nameTextStyle: {  // 设置标题文本的样式
                                fontFamily: 'Helvetica',  // 字体系列
                                fontSize: 16,  // 字体大小
                                color: '#333333',  // 字体颜色
                                fontWeight: 'bold'  // 字体粗细
                            }
                        },
                        series: [
                            {
                                name: '',
                                type: 'boxplot',
                                data: boxplotData,
                                itemStyle: {
                                    color: '#A9D2C3',
                                    borderColor:'#333333'
                                },
                                tooltip: {
                                    formatter: function (param) {
                                        return [
                                            'name: ' + param.name,
                                            'min: ' + param.data[0],
                                            'Q1: ' + param.data[1],
                                            'median: ' + param.data[2],
                                            'Q3: ' + param.data[3],
                                            'max: ' + param.data[4]
                                        ].join('<br/>');
                                    }
                                }
                            },
                            // {
                            //     name: '',
                            //     type: 'scatter',
                            //     data: outlierData,
                            //     tooltip: {
                            //         trigger: 'item',
                            //         axisPointer: {
                            //           type: 'shadow'
                            //         }
                            //       }, itemStyle: {
                            //         color: '#333333' // Set scatter point color
                            //     }
                            // }
                        ], backgroundColor: 'rgba(0,0,0,0)'
                    };
                    Expression_echarts3_option && Expression_echarts3_Chart.setOption(Expression_echarts3_option);

                },
                error: function (xhr, status, error) {
                    console.log(error);
                }
            });
            /*expressions*/

            // 渲染表格
            var expression1 = layui.table.render({
                elem: '#expression',
                page: true,
                url: './interface/circRNA_expression3_API.php',
                limits: [10,20],
                id: 'expression1',
                where: { BloodCircR_ID: BloodCircR_ID,
                    field: 'disease', //排序字段
                    order: 'asc' //排序方式 
                },
                cols: [[
                    { field: 'disease', title: 'Disease', sort: true, align: 'center' },
                    { field: 'TPM', title: 'Mean Expression', sort: true, align: 'center' }
                ]]
            });
            var lastSortField = ''; // 上一次的排序字段
            var lastSortOrder = ''; // 上一次的排序顺序
            layui.table.on('sort(expression1)', function (obj) { //注：tool是工具条事件名，test是table原始容器的属性 lay-filter="对应的值"
                console.log(obj.field); //当前排序的字段名
                console.log(obj.type); //当前排序类型：desc（降序）、asc（升序）、null（空对象，默认排序）
                console.log(this); //当前排序的 th 对象

                // 判断是否需要重新加载数据
                if (obj.field !== lastSortField || obj.type !== lastSortOrder) {
                    // 保存当前排序字段和排序顺序
                    lastSortField = obj.field;
                    lastSortOrder = obj.type;
                    console.log('reload');
                    // 重新加载数据
                    layui.table.reload('expression1', {
                        where: {
                            BloodCircR_ID: BloodCircR_ID,
                            field: obj.field, //排序字段
                            order: obj.type //排序方式
                        }
                    });
                }
            })
        }

        function showExpression4(){
            /*circRNA Expression 箱线图 4 */
            var datasets;
            var expression;
            var Expression_echarts4_Dom = document.getElementById('Expression_echarts');
            var Expression_echarts4_Chart = echarts.init(Expression_echarts4_Dom);
            var Expression_echarts4_option;
            $.ajax({
                type: 'GET',
                url: './interface/circRNA_Expression_echarts4_API.php?BloodCircR_ID=' + BloodCircR_ID,
                success: function (data) {
                    let jsonData = JSON.parse(data);
                    let categories = jsonData.disease;
                    let boxplotData = jsonData.boxplotData;
             let combinedData = categories.map((category, index) => {
                                     return {
                                         category: category,
                                         boxplot: boxplotData[index],
                                     };
                                 });
                                 // 对 combinedData 进行排序，按照 category 排序
                                 combinedData.sort(function(a, b) {
                                     return a.category.localeCompare(b.category);
                                 });
                                 // 重新分配排序后的 categories、boxplotData、outlierData
                                 categories = combinedData.map(item => item.category);
                                 boxplotData = combinedData.map(item => item.boxplot);
                    Expression_echarts4_option = {

                        tooltip: {
                            trigger: 'axis',
                            axisPointer: {
                                type: 'shadow'
                            }
                        },
                        toolbox: {
                            show: true,
                            feature: {

                                saveAsImage: {show: true,
								title: 'SaveAsImage' }
                            }
                        },
                        xAxis: {
                            type: 'category',
                            data: categories,
                            axisLabel: {
                                rotate: 30// 负值表示逆时针旋转标签
                            },
                            nameLocation: 'end',
                            nameGap: 20,
                            nameTextStyle: {  // 设置标题文本的样式
                                fontFamily: 'Helvetica',  // 字体系列
                                fontSize: 16,  // 字体大小
                                color: '#333333',  // 字体颜色
                                fontWeight: 'bold'  // 字体粗细
                            }
                        },
                        yAxis: {
                            type: 'value',
                            name: 'TPM',
                            nameTextStyle: {  // 设置标题文本的样式
                                fontFamily: 'Helvetica',  // 字体系列
                                fontSize: 16,  // 字体大小
                                color: '#333333',  // 字体颜色
                                fontWeight: 'bold'  // 字体粗细
                            }
                        },
                        series: [
                            {
                                name: '',
                                type: 'boxplot',
                                data: boxplotData,
                                itemStyle: {
                                    color: '#C6B3C7' // 设置箱子的颜色
                                },
                                tooltip: {
                                    formatter: function (param) {
                                        return [
                                            'name: ' + param.name,
                                            'min: ' + param.data[0],
                                            'Q1: ' + param.data[1],
                                            'median: ' + param.data[2],
                                            'Q3: ' + param.data[3],
                                            'max: ' + param.data[4]
                                        ].join('<br/>');
                                    }
                                }
                            }
                        ]
                    };
                    Expression_echarts4_option && Expression_echarts4_Chart.setOption(Expression_echarts4_option);

                },
                error: function (xhr, status, error) {
                    console.log(error);
                }
            });
            /*expressions*/
            // 渲染表格
            var expression2 = layui.table.render({
                elem: '#expression',
                page: true,
                url: './interface/circRNA_expression4_API.php',
               limits: [10,20],
                id: 'expression2',
                where: { BloodCircR_ID: BloodCircR_ID,
                    field: 'disease', //排序字段
                    order: 'asc' //排序方式
                },
                cols: [[
                    { field: 'disease', title: 'Disease', sort: true, align: 'center' },
                    { field: 'TPM', title: 'Mean Expression', sort: true, align: 'center' }
                ]]
            });
            var lastSortField = ''; // 上一次的排序字段
            var lastSortOrder = ''; // 上一次的排序顺序
            layui.table.on('sort(expression2)', function (obj) { //注：tool是工具条事件名，test是table原始容器的属性 lay-filter="对应的值"
                console.log(obj.field); //当前排序的字段名
                console.log(obj.type); //当前排序类型：desc（降序）、asc（升序）、null（空对象，默认排序）
                console.log(this); //当前排序的 th 对象

                // 判断是否需要重新加载数据
                if (obj.field !== lastSortField || obj.type !== lastSortOrder) {
                    // 保存当前排序字段和排序顺序
                    lastSortField = obj.field;
                    lastSortOrder = obj.type;
                    console.log('reload');
                    // 重新加载数据
                    layui.table.reload('expression2', {
                        where: {
                            BloodCircR_ID: BloodCircR_ID,
                            field: obj.field, //排序字段
                            order: obj.type //排序方式
                        }
                    });
                }
            })
        }


        function showExpression5(){
            /*circRNA Expression 箱线图 5 */
            var datasets;
            var expression;
            var Expression_echarts5_Dom = document.getElementById('Expression_echarts');
            var Expression_echarts5_Chart = echarts.init(Expression_echarts5_Dom);
            var Expression_echarts5_option;
            $.ajax({
                type: 'GET',
                url: './interface/circRNA_Expression_echarts5_API.php?BloodCircR_ID=' + BloodCircR_ID,
                success: function (data) {
                    let jsonData = JSON.parse(data);
                    let categories = jsonData.disease
                    let boxplotData = jsonData.boxplotData
                 let combinedData = categories.map((category, index) => {
                     return {
                         category: category,
                         boxplot: boxplotData[index],
                     };
                 });
                 // 对 combinedData 进行排序，按照 category 排序
                 combinedData.sort(function(a, b) {
                     return a.category.localeCompare(b.category);
                 });
                 // 重新分配排序后的 categories、boxplotData、outlierData
                 categories = combinedData.map(item => item.category);
                 boxplotData = combinedData.map(item => item.boxplot);

                    Expression_echarts5_option = {

                        tooltip: {
                            trigger: 'axis',
                            axisPointer: {
                                type: 'shadow'
                            }
                        }, toolbox: {
                            show: true,
                            feature: {

                                saveAsImage: {show: true,
								title: 'SaveAsImage' }
                            }
                        },
                        xAxis: {
                            name:'Disease',
                            type: 'category',
                            data: categories,
							axisTick: { alignWithLabel: true, },
                            axisLabel: {
                                rotate: 30// 负值表示逆时针旋转标签
                            },
                            nameLocation: 'end',
                            nameGap: 20,
                            nameTextStyle: {  // 设置标题文本的样式
                                fontFamily: 'Helvetica',  // 字体系列
                                fontSize: 16,  // 字体大小
                                color: '#333333',  // 字体颜色
                                fontWeight: 'bold'  // 字体粗细
                            }
                        },
                        yAxis: {
                            type: 'value',
                            name: 'Junction ratio',

                            nameTextStyle: {  // 设置标题文本的样式
                                fontFamily: 'Helvetica',  // 字体系列
                                fontSize: 16,  // 字体大小
                                color: '#333333',  // 字体颜色
                                fontWeight: 'bold'  // 字体粗细
                            }
                        },
                        series: [
                            {
                                name: '',
                                type: 'boxplot',
                                data: boxplotData,
                                itemStyle: {
                                    color: '#EDCA9D' // 设置箱子的颜色
                                },
                                tooltip: {
                                    formatter: function (param) {
                                        return [
                                            'name: ' + param.name,
                                            'min: ' + param.data[0],
                                            'Q1: ' + param.data[1],
                                            'median: ' + param.data[2],
                                            'Q3: ' + param.data[3],
                                            'max: ' + param.data[4]
                                        ].join('<br/>');
                                    }
                                }
                            }
                        ]
                    };
                    Expression_echarts5_option && Expression_echarts5_Chart.setOption(Expression_echarts5_option);

                },
                error: function (xhr, status, error) {
                    console.log(error);
                }
            });
            /*expressions*/
            // 渲染表格
            var expression3 = layui.table.render({
                elem: '#expression',
                page: true,
                url: './interface/circRNA_expression5_API.php',

                limits: [10,20],
                id: 'expression3',
                where: { BloodCircR_ID: BloodCircR_ID,
                    field: 'disease', //排序字段
                    order: 'asc' //排序方式
                },
                cols: [[
                    { field: 'disease', title: 'Disease', sort: true, align: 'center' },
                    { field: 'junction_reads_ratio', title: 'Mean Junction Ratio', sort: true, align: 'center' }
                ]]
            });
            var lastSortField = ''; // 上一次的排序字段
            var lastSortOrder = ''; // 上一次的排序顺序
            layui.table.on('sort(expression3)', function (obj) { //注：tool是工具条事件名，test是table原始容器的属性 lay-filter="对应的值"
                console.log(obj.field); //当前排序的字段名
                console.log(obj.type); //当前排序类型：desc（降序）、asc（升序）、null（空对象，默认排序）
                console.log(this); //当前排序的 th 对象

                // 判断是否需要重新加载数据
                if (obj.field !== lastSortField || obj.type !== lastSortOrder) {
                    // 保存当前排序字段和排序顺序
                    lastSortField = obj.field;
                    lastSortOrder = obj.type;
                    console.log('reload');
                    // 重新加载数据
                    layui.table.reload('expression3', {
                        where: {
                            BloodCircR_ID: BloodCircR_ID,
                            field: obj.field, //排序字段
                            order: obj.type //排序方式
                        }
                    });
                }
            })
        }



        // /*横向带颜色柱状图 Go trems 1*/
        // var GO_1_echarts_Dom = document.getElementById('GO_1_echarts');
        // var GO_1_echarts_Chart = echarts.init(GO_1_echarts_Dom);
        // var GO_1_echarts_option;
        // $.ajax({
        //     type: 'GET',
        //     url: './interface/circRNA_Go_trem_1_API.php?BloodCircR_ID=' + BloodCircR_ID + '&choose=' + 'go_biological_process',
        //     success: function (data) {
        //         let jsonData = JSON.parse(data);
        //         //console.log(1773,jsonData)
        //         let Ydata = jsonData.yAxisData
        //         let Sdata = jsonData.seriesData
        //         let tooltipData = jsonData.tooltipData

        //         GO_1_echarts_option = {
        //             tooltip: {
        //                 trigger: 'axis',
        //                 axisPointer: {
        //                     type: 'shadow'
        //                 },
        //                 formatter: function (params) {
        //                     var content = '';
        //                     var dataIndex = params[0].dataIndex;

        //                     if (tooltipData[dataIndex]) {
        //                         content += '<b>Term</b>: ' + tooltipData[dataIndex].Term + '<br>';
        //                         content += '<b>P_value</b>: ' + tooltipData[dataIndex].P_value + '<br>';
        //                         content += '<b>Adjusted_P_value</b>: ' + tooltipData[dataIndex].Adjusted_P_value + '<br>';
        //                         content += '<b>Odds_Ratio</b>: ' + tooltipData[dataIndex].Odds_Ratio + '<br>';
        //                         content += '<b>Combined_Score</b>: ' + tooltipData[dataIndex].Combined_Score + '<br>';
        //                     }

        //                     return content;
        //                 }
        //             },
        //             toolbox: {
        //                 show: true,
        //                 feature: {

        //                     saveAsImage: {show: true}
        //                 }
        //             },
        //             toolbox: {
        //                 show: true,
        //                 feature: {

        //                     saveAsImage: {show: true}
        //                 }
        //             },
        //             yAxis: {
        //                 type: 'category',
        //                 name: 'Terms',
        //                 data: Ydata,
        //                 axisLabel: {
        //                     show: false  // 不显示轴上的文字
        //                 }
        //             },
        //             xAxis: {
        //                 type: 'value',
        //                 name: 'combined score'
        //             },
        //             visualMap: {
        //                 min: 0,
        //                 max: 200,
        //                 calculable: true,
        //                 orient: 'horizontal',
        //                 left: 'center',
        //                 bottom: '0%',
        //                 dimension: 0,
        //                 text: ['High Score', 'Low Score'],
        //                 inRange: {
        //                     color: ['#D7D68A', '#BF444C']
        //                 }
        //             },
        //             series: [
        //                 {
        //                     data: Sdata,
        //                     type: 'bar',
        //                     label: {
        //                         show: true,
        //                         position: 'insideLeft', // 在柱体内部的右侧显示标签
        //                         formatter: '{b}'  // 显示 yAxis 对应的文本
        //                     }
        //                 }
        //             ]
        //         };

        //         GO_1_echarts_option && GO_1_echarts_Chart.setOption(GO_1_echarts_option);

        //     },
        //     error: function (xhr, status, error) {
        //         console.log(error);
        //     }
        // });

        // /*横向带颜色柱状图 Go trems 2*/
        // var GO_2_echarts_Dom = document.getElementById('GO_2_echarts');
        // var GO_2_echarts_Chart = echarts.init(GO_2_echarts_Dom);
        // var GO_2_echarts_option;
        // $.ajax({
        //     type: 'GET',
        //     url: './interface/circRNA_Go_trem_1_API.php?BloodCircR_ID=' + BloodCircR_ID + '&choose=' + 'go_cellular_component',
        //     success: function (data) {
        //         let jsonData = JSON.parse(data);
        //         let Ydata = jsonData.yAxisData
        //         let Sdata = jsonData.seriesData
        //         let tooltipData = jsonData.tooltipData
        //         GO_2_echarts_option = {
        //             tooltip: {
        //                 trigger: 'axis',
        //                 axisPointer: {
        //                     type: 'shadow'
        //                 },
        //                 formatter: function (params) {
        //                     var content = '';
        //                     var dataIndex = params[0].dataIndex;

        //                     if (tooltipData[dataIndex]) {
        //                         content += '<b>Term</b>: ' + tooltipData[dataIndex].Term + '<br>';
        //                         content += '<b>P_value</b>: ' + tooltipData[dataIndex].P_value + '<br>';
        //                         content += '<b>Adjusted_P_value</b>: ' + tooltipData[dataIndex].Adjusted_P_value + '<br>';
        //                         content += '<b>Odds_Ratio</b>: ' + tooltipData[dataIndex].Odds_Ratio + '<br>';
        //                         content += '<b>Combined_Score</b>: ' + tooltipData[dataIndex].Combined_Score + '<br>';
        //                     }

        //                     return content;
        //                 }
        //             },
        //             toolbox: {
        //                 show: true,
        //                 feature: {

        //                     saveAsImage: {show: true}
        //                 }
        //             },
        //             yAxis: {
        //                 type: 'category',
        //                 name: 'Terms',
        //                 data: Ydata,
        //                 axisLabel: {
        //                     show: false  // 不显示轴上的文字
        //                 }
        //             },
        //             xAxis: {
        //                 type: 'value',
        //                 name: 'combined score'
        //             },
        //             visualMap: {
        //                 min: 0,
        //                 max: 50,
        //                 calculable: true,
        //                 orient: 'horizontal',
        //                 left: 'center',
        //                 bottom: '0%',
        //                 dimension: 0,
        //                 text: ['High Score', 'Low Score'],
        //                 inRange: {
        //                     color: ['#D7D68A', '#BF444C']
        //                 }
        //             },
        //             series: [
        //                 {
        //                     data: Sdata,
        //                     type: 'bar',
        //                     label: {
        //                         show: true,
        //                         position: 'insideLeft', // 在柱体内部的右侧显示标签
        //                         formatter: '{b}'  // 显示 yAxis 对应的文本
        //                     }
        //                 }
        //             ]
        //         };

        //         GO_2_echarts_option && GO_2_echarts_Chart.setOption(GO_2_echarts_option);

        //     },
        //     error: function (xhr, status, error) {
        //         console.log(error);
        //     }
        // });


        // /*横向带颜色柱状图 Go trems 3*/
        // var GO_3_echarts_Dom = document.getElementById('GO_3_echarts');
        // var GO_3_echarts_Chart = echarts.init(GO_3_echarts_Dom);
        // var GO_3_echarts_option;
        // $.ajax({
        //     type: 'GET',
        //     url: './interface/circRNA_Go_trem_1_API.php?BloodCircR_ID=' + BloodCircR_ID + '&choose=' + 'go_molecular_function',
        //     success: function (data) {
        //         let jsonData = JSON.parse(data);
        //         let Ydata = jsonData.yAxisData
        //         let Sdata = jsonData.seriesData
        //         let tooltipData = jsonData.tooltipData
        //         GO_3_echarts_option = {
        //             tooltip: {
        //                 trigger: 'axis',
        //                 axisPointer: {
        //                     type: 'shadow'
        //                 },
        //                 formatter: function (params) {
        //                     var content = '';
        //                     var dataIndex = params[0].dataIndex;

        //                     if (tooltipData[dataIndex]) {
        //                         content += '<b>Term</b>: ' + tooltipData[dataIndex].Term + '<br>';
        //                         content += '<b>P_value</b>: ' + tooltipData[dataIndex].P_value + '<br>';
        //                         content += '<b>Adjusted_P_value</b>: ' + tooltipData[dataIndex].Adjusted_P_value + '<br>';
        //                         content += '<b>Odds_Ratio</b>: ' + tooltipData[dataIndex].Odds_Ratio + '<br>';
        //                         content += '<b>Combined_Score</b>: ' + tooltipData[dataIndex].Combined_Score + '<br>';
        //                     }

        //                     return content;
        //                 }
        //             },
        //             toolbox: {
        //                 show: true,
        //                 feature: {

        //                     saveAsImage: {show: true}
        //                 }
        //             },
        //             yAxis: {
        //                 type: 'category',
        //                 name: 'Terms',
        //                 data: Ydata,
        //                 axisLabel: {
        //                     show: false  // 不显示轴上的文字
        //                 }
        //             },
        //             xAxis: {
        //                 type: 'value',
        //                 name: 'combined score'
        //             },
        //             visualMap: {
        //                 min: 0,
        //                 max: 150,
        //                 calculable: true,
        //                 orient: 'horizontal',
        //                 left: 'center',
        //                 bottom: '0%',
        //                 dimension: 0,
        //                 text: ['High Score', 'Low Score'],
        //                 inRange: {
        //                     color: ['#D7D68A', '#BF444C']
        //                 }
        //             },
        //             series: [
        //                 {
        //                     data: Sdata,
        //                     type: 'bar',
        //                     label: {
        //                         show: true,
        //                         position: 'insideLeft', // 在柱体内部的右侧显示标签
        //                         formatter: '{b}'  // 显示 yAxis 对应的文本
        //                     }
        //                 }
        //             ]
        //         };

        //         GO_3_echarts_option && GO_3_echarts_Chart.setOption(GO_3_echarts_option);

        //     },
        //     error: function (xhr, status, error) {
        //         console.log(error);
        //     }
        // });

        // /*横向带颜色柱状图 tabbox_pathways_1*/
        // var pathways_1_echarts_Dom = document.getElementById('pathways_1_echarts');
        // var pathways_1_echarts_Chart = echarts.init(pathways_1_echarts_Dom);
        // var pathways_1_echarts_option;
        // $.ajax({
        //     type: 'GET',
        //     url: './interface/circRNA_Go_trem_1_API.php?BloodCircR_ID=' + BloodCircR_ID + '&choose=' + 'kegg',
        //     success: function (data) {
        //         let jsonData = JSON.parse(data);
        //         let Ydata = jsonData.yAxisData
        //         let Sdata = jsonData.seriesData
        //         let tooltipData = jsonData.tooltipData
        //         pathways_1_echarts_option = {
        //             tooltip: {
        //                 trigger: 'axis',
        //                 axisPointer: {
        //                     type: 'shadow'
        //                 },
        //                 formatter: function (params) {
        //                     var content = '';
        //                     var dataIndex = params[0].dataIndex;

        //                     if (tooltipData[dataIndex]) {
        //                         content += '<b>Term</b>: ' + tooltipData[dataIndex].Term + '<br>';
        //                         content += '<b>P_value</b>: ' + tooltipData[dataIndex].P_value + '<br>';
        //                         content += '<b>Adjusted_P_value</b>: ' + tooltipData[dataIndex].Adjusted_P_value + '<br>';
        //                         content += '<b>Odds_Ratio</b>: ' + tooltipData[dataIndex].Odds_Ratio + '<br>';
        //                         content += '<b>Combined_Score</b>: ' + tooltipData[dataIndex].Combined_Score + '<br>';
        //                     }

        //                     return content;
        //                 }
        //             },
        //             toolbox: {
        //                 show: true,
        //                 feature: {

        //                     saveAsImage: {show: true}
        //                 }
        //             },
        //             yAxis: {
        //                 type: 'category',
        //                 name: 'Terms',
        //                 data: Ydata,
        //                 axisLabel: {
        //                     show: false  // 不显示轴上的文字
        //                 }
        //             },
        //             xAxis: {
        //                 type: 'value',
        //                 name: 'combined score'
        //             },
        //             visualMap: {
        //                 min: 0,
        //                 max: 50,
        //                 calculable: true,
        //                 orient: 'horizontal',
        //                 left: 'center',
        //                 bottom: '0%',
        //                 dimension: 0,
        //                 text: ['High Score', 'Low Score'],
        //                 inRange: {
        //                     color: ['#D7D68A', '#BF444C']
        //                 }
        //             },
        //             series: [
        //                 {
        //                     data: Sdata,
        //                     type: 'bar',
        //                     label: {
        //                         show: true,
        //                         position: 'insideLeft', // 在柱体内部的右侧显示标签
        //                         formatter: '{b}'  // 显示 yAxis 对应的文本
        //                     }
        //                 }
        //             ]
        //         };

        //         pathways_1_echarts_option && pathways_1_echarts_Chart.setOption(pathways_1_echarts_option);

        //     },
        //     error: function (xhr, status, error) {
        //         console.log(error);
        //     }
        // });

        // /*横向带颜色柱状图 tabbox_pathways_2*/
        // var pathways_2_echarts_Dom = document.getElementById('pathways_2_echarts');
        // var pathways_2_echarts_Chart = echarts.init(pathways_2_echarts_Dom);
        // var pathways_2_echarts_option;
        // $.ajax({
        //     type: 'GET',
        //     url: './interface/circRNA_Go_trem_1_API.php?BloodCircR_ID=' + BloodCircR_ID + '&choose=' + 'reactome',
        //     success: function (data) {
        //         let jsonData = JSON.parse(data);
        //         let Ydata = jsonData.yAxisData
        //         let Sdata = jsonData.seriesData
        //         let tooltipData = jsonData.tooltipData
        //         pathways_2_echarts_option = {
        //             tooltip: {
        //                 trigger: 'axis',
        //                 axisPointer: {
        //                     type: 'shadow'
        //                 },
        //                 formatter: function (params) {
        //                     var content = '';
        //                     var dataIndex = params[0].dataIndex;

        //                     if (tooltipData[dataIndex]) {
        //                         content += '<b>Term</b>: ' + tooltipData[dataIndex].Term + '<br>';
        //                         content += '<b>P_value</b>: ' + tooltipData[dataIndex].P_value + '<br>';
        //                         content += '<b>Adjusted_P_value</b>: ' + tooltipData[dataIndex].Adjusted_P_value + '<br>';
        //                         content += '<b>Odds_Ratio</b>: ' + tooltipData[dataIndex].Odds_Ratio + '<br>';
        //                         content += '<b>Combined_Score</b>: ' + tooltipData[dataIndex].Combined_Score + '<br>';
        //                     }

        //                     return content;
        //                 }
        //             },
        //             toolbox: {
        //                 show: true,
        //                 feature: {

        //                     saveAsImage: {show: true}
        //                 }
        //             },
        //             yAxis: {
        //                 type: 'category',
        //                 name: 'Terms',
        //                 data: Ydata,
        //                 axisLabel: {
        //                     show: false  // 不显示轴上的文字
        //                 }
        //             },
        //             xAxis: {
        //                 type: 'value',
        //                 name: 'combined score'
        //             },
        //             visualMap: {
        //                 min: 0,
        //                 max: 250,
        //                 calculable: true,
        //                 orient: 'horizontal',
        //                 left: 'center',
        //                 bottom: '0%',
        //                 dimension: 0,
        //                 text: ['High Score', 'Low Score'],
        //                 inRange: {
        //                     color: ['#D7D68A', '#BF444C']
        //                 }
        //             },
        //             series: [
        //                 {
        //                     data: Sdata,
        //                     type: 'bar',
        //                     label: {
        //                         show: true,
        //                         position: 'insideLeft', // 在柱体内部的右侧显示标签
        //                         formatter: '{b}'  // 显示 yAxis 对应的文本
        //                     }
        //                 }
        //             ]
        //         };

        //         pathways_2_echarts_option && pathways_2_echarts_Chart.setOption(pathways_2_echarts_option);

        //     },
        //     error: function (xhr, status, error) {
        //         console.log(error);
        //     }
        // });


        /*layui表格渲染*/

        /*evidence*/
        /*
        var cirRNAs = table.render({
            elem: '#evidence'
            ,page: true
            ,width:1500
            ,url: './interface/circRNA_evidence_API.php'
            ,page: true //开启分页
            ,id : 'cirRNAs'
            ,limits:[10,50,100]
            ,where: {BloodCircR_ID: BloodCircR_ID}
            ,cols: [[ //表头
              {field: 'BloodCircR_ID', title: 'BloodCircR_ID'}
              ,{field: 'evidences_num', title: 'evidences_num'}
              ,{field: 'evidences_score', title: 'evidences_socre'}
              ,{field: 'm6A_sites', title: 'm6A_sites'}
              ,{field: 'Ribosome_polysome_profiling', title: 'Ribosome_polysome_profiling'}
              ,{field: 'Translation_initiation_site', title: 'Translation_initiation_site'}
              ,{field: 'ORF', title: 'ORF'}
              ,{field: 'IRES_sequence', title: 'IRES_sequence'}
              ,{field: 'SeqComp', title: 'SeqComp'}
              ,{field: 'MS_evidence', title: 'MS_evidence'}
            ]]
        });
        */
        /*support_reads_table*/

        var support = table.render({
            elem: '#support_reads_table'
            , page: true
            // , width: 1460
            , url: './interface/circRNA_support_API.php?BloodCircR_ID=' + BloodCircR_ID
            , page: true //开启分页
            , id: 'support'
            , limit: 20
            , limits: [20, 50, 100]
            , where: {BloodCircR_ID: BloodCircR_ID,
                field: 'DatasetID', //排序字段
                order: 'asc' //排序方式
            }
            ,height: 720 // 设置固定高度
            , cols: [[ //表头
                {field: 'DatasetID', title: 'DatasetID', event: 'show_link', sort: true, align: 'center'}
                , {field: 'Sample', title: 'Sample', event: 'show_img', sort: true, align: 'center'}
            ]],

            done: function (res) {
                console.log(res);
                // 移除已有的所有选项
                $("#Dataset_select").empty();
                // 表格渲染完成后的操作
                $("#Dataset_select").append("<option value='All'>All</option>");
                var uniqueIDs = res.distinctIDs;
                for (var i = 0; i < uniqueIDs.length; i++) {
                    var id = uniqueIDs[i]; // 获取 Dataset 值
                    var option = "<option value='" + id + "'";
                    if (id === res.DatasetID) { // 判断是否默认选中
                        option += " selected";
                    }
                    option += ">" + id + "</option>";
                    $("#Dataset_select").append(option); // 添加到选择框中
                }

            }
        });
		
        var DatasetID = '';
        var supportSortField = ''; // 上一次的排序字段
        var supportSortOrder = ''; // 上一次的排序顺序
        table.on('sort(support)', function (obj) { //注：tool是工具条事件名，test是table原始容器的属性 lay-filter="对应的值"
            console.log(obj.field); //当前排序的字段名
            console.log(obj.type); //当前排序类型：desc（降序）、asc（升序）、null（空对象，默认排序）
            console.log(this); //当前排序的 th 对象
            console.log(BloodCircR_ID);
            console.log(DatasetID);
            console.log(supportSortField);
            console.log(supportSortOrder);

            // 判断是否需要重新加载数据
            if (DatasetID!== 'All') {
                // 保存当前排序字段和排序顺序
                supportSortField = obj.field;
                supportSortOrder = obj.type;
                console.log('reload');
                // 重新加载数据
                table.reload('support', {
                    where: {
                        BloodCircR_ID: BloodCircR_ID,
                        DatasetID: DatasetID,
                        field: obj.field, //排序字段
                        order: obj.type //排序方式
                    }
                });
            }else{
                // 保存当前排序字段和排序顺序
                supportSortField = obj.field;
                supportSortOrder = obj.type;
                console.log('reload');
                // 重新加载数据
                table.reload('support', {
                    where: {
                        BloodCircR_ID: BloodCircR_ID,
                        field: obj.field, //排序字段
                        order: obj.type //排序方式
                    }
                });
            }
        });
        // 监听选择框的变化事件
        $("#Dataset_select").change(function () {
            var selectedID = $(this).val(); // 获取选择框的值
            // 判断选择的值是否为 "All"
            if (selectedID === "All") {
                DatasetID = "All";
                console.log(selectedID);
                support.reload({
                    where: {
                        BloodCircR_ID: BloodCircR_ID,
                        field: 'DatasetID', //排序字段
                        order: 'asc', //排序方式
                    }
                });
            } else {
                DatasetID = selectedID;
                console.log(selectedID);
                // 重新加载表格数据，传入选择的 Dataset 值作为筛选条件
                support.reload({
                    where: {
                        BloodCircR_ID: BloodCircR_ID,
                        field: 'DatasetID', //排序字段
                        order: 'asc', //排序方式
                        DatasetID: selectedID
                    }
                });
            }
        });

        $.ajax({
            url: './interface/circRNA_suppot_img_API.php?BloodCircR_ID=' + BloodCircR_ID,
            type: 'GET',
            dataType: 'json',
            success: function (response) {
                // 成功接收到数据后的处理
                $('#support_reads_img').attr('src', './support_img_links/' + response.path);

                const firstSlashIndex = response.DatasetID; 
                const pathWithoutPrefix = response.Sample; 

                const formattedPath = `${firstSlashIndex}:${pathWithoutPrefix}`; // 格式化为"PRJNAxxxxxx:SRRxxxxxx"
                $('#support_reads_img_title').html(formattedPath); // 设置图片的标题
            },
            error: function (error) {
                // 请求失败时的处理
                console.error('Error:', error);
            }
        });
        table.on('tool(support)', function (obj) {
            let event = obj.event;
            console.log(event);
            let data = obj.data; // 获取当前行的数据
            if (event === "show_img") {
                let ajax_url = './interface/circRNA_suppot_img_API.php?Sample=' + encodeURIComponent($(this).find('div').html()) + '&BloodCircR_ID=' + BloodCircR_ID;
                $.ajax({
                    url: ajax_url,
                    type: 'GET',
                    dataType: 'json',
                    success: function (response) {
                        // 成功接收到数据后的处理
                        console.log(1217, response);
                        console.log('test');
                        $('#support_reads_img').attr('src', './support_img_links/' + response.path);
                        
                        const firstSlashIndex = response.DatasetID; 
                        const pathWithoutPrefix = response.Sample; 

                        const formattedPath = `${firstSlashIndex}:${pathWithoutPrefix}`; // 格式化为"PRJNAxxxxxx:SRRxxxxxx"
                        $('#support_reads_img_title').html(formattedPath); // 设置图片的标题

                    },
                    error: function (error) {
                        // 请求失败时的处理
                        console.error('Error:', error);
                    }
                });
            }
            if (event === "show_link") {
                // 获取当前行数据
                let dataset = data.Dataset; // 你可以根据需要选择字段
                let DatasetID = data.DatasetID;
                
                // 跳转到一个新的 URL 页面，可以根据具体需求调整 URL 的结构
                let url = `http://10.120.53.201:8002/datasetinfo.html?DatasetID=${encodeURIComponent(DatasetID)}&Dataset=${encodeURIComponent(dataset)}`;
                console.log(url);
                window.location.href = url; // 执行页面跳转
            }
        });
        var data1 = form.val("search_form1");
        form.on('submit(search_submit1)', function (data) {
            event.preventDefault();
            data1 = form.val("search_form1");
            var obj1 = {
                search_column: data1['item_name'],
                search_value: data1['search_value'],
                BloodCircR_ID: BloodCircR_ID
            };
            support.reload({
                url: './interface/circRNA_support_API.php'
                , where: obj1
                , page: {
                    curr: 1 //重新从第 1 页开始
                }
            });
            return false;
        });
        



        /*RBP*/
        var RBP = table.render({
            elem: '#RBP'
            , page: true
            , width: 1460
            , url: './interface/circRNA_RBP_API.php'
            , page: true //开启分页
            , id: 'RBP'
            , limits: [10, 50, 100]
            , where: {BloodCircR_ID: BloodCircR_ID,
                field: 'BloodCircR_ID', //排序字段
                order: 'asc' //排序方式
            }
            , cols: [[ //表头
                {field: 'BloodCircR_ID', title: 'BloodCircR ID', sort: true, align: 'center'}
                , {field: 'RBP', title: 'RBP', sort: true, align: 'center'}
                , {field: 'Binding_sites', title: 'Binding_sites', sort: true, align: 'center'}

            ]]
        });
        var RBPSortField = ''; // 上一次的排序字段
        var RBPSortOrder = ''; // 上一次的排序顺序
        table.on('sort(RBP)', function (obj) { //注：tool是工具条事件名，test是table原始容器的属性 lay-filter="对应的值"
            console.log(obj.field); //当前排序的字段名
            console.log(obj.type); //当前排序类型：desc（降序）、asc（升序）、null（空对象，默认排序）
            console.log(this); //当前排序的 th 对象

            // 判断是否需要重新加载数据
            if (obj.field !== RBPSortField || obj.type !== RBPSortOrder) {
                // 保存当前排序字段和排序顺序
                RBPSortField = obj.field;
                RBPSortOrder = obj.type;
                console.log('reload');
                // 重新加载数据
                table.reload('RBP', {
                    where: {
                        BloodCircR_ID: BloodCircR_ID,
                        field: obj.field, //排序字段
                        order: obj.type //排序方式
                    }
                });
            }
        });
        /*miRNA*/
        var miRNA = table.render({
            elem: '#miRNA'
            , page: true
            , width: 1460
            , url: './interface/circRNA_miRNA_API.php'
            , page: true //开启分页
            , limits: [10, 50, 100]
            , id: 'miRNA'
            , where: {BloodCircR_ID: BloodCircR_ID,
                field: 'BloodCircR_ID', //排序字段
                order: 'asc' //排序方式
            }
            , cols: [[ //表头
                {field: 'BloodCircR_ID', title: 'BloodCircR ID', sort: true, align: 'center'}
                , {field: 'miRNA', title: 'miRNA', sort: true, align: 'center'}
                , {field: 'start', title: 'start', sort: true, align: 'center'}
                , {field: 'end', title: 'end', sort: true, align: 'center'}
                , {field: 'site_type', title: 'site_type', sort: true, align: 'center'}

            ]]
        });
        var miRNASortField = ''; // 上一次的排序字段
        var miRNASortOrder = ''; // 上一次的排序顺序
        table.on('sort(miRNA)', function (obj) { //注：tool是工具条事件名，test是table原始容器的属性 lay-filter="对应的值"
            console.log(obj.field); //当前排序的字段名
            console.log(obj.type); //当前排序类型：desc（降序）、asc（升序）、null（空对象，默认排序）
            console.log(this); //当前排序的 th 对象

            // 判断是否需要重新加载数据
            if (obj.field !== miRNASortField || obj.type !== miRNASortOrder) {
                // 保存当前排序字段和排序顺序
                miRNASortField = obj.field;
                miRNASortOrder = obj.type;
                console.log('reload');
                // 重新加载数据
                table.reload('miRNA', {
                    where: {
                        BloodCircR_ID: BloodCircR_ID,
                        field: obj.field, //排序字段
                        order: obj.type //排序方式
                    }
                });
            }
        });
    });/*layui 使用区域收尾*/

}

const urlParams = new URLSearchParams(window.location.search);
const BloodCircR_ID = urlParams.get('BloodCircR_ID');

// Fetch the data from the PHP script
fetch('./interface/test.php?BloodCircR_ID=' + BloodCircR_ID)
    .then(function(response) {
        if (!response.ok) {
            document.getElementById('rgnetwork_echarts').style.display = 'none';
            document.getElementById('container1').style.display = 'block';
            document.getElementById('centeredText1').style.display = 'block';
            throw new Error('Network response was not ok ' + response.statusText);
        }
        return response.json();
    })
    .then(function(data) {
        // 初始化 ECharts 图表
        var myChart = echarts.init(document.getElementById('rgnetwork_echarts'));
        document.getElementById('rgnetwork_echarts').style.display = 'block';
        document.getElementById('container1').style.display = 'none';
        document.getElementById('centeredText1').style.display = 'none';

        // 确保数据正确接收
        console.log('Received data:', data);

        if (!data || !data.nodes || !data.links) {
            throw new Error('Received data format is invalid');
        }

        // 处理节点数据，设置不同类别的 symbolSize
        data.nodes.forEach(function(node) {
            if (node.category === 'circRNA') {
                node.symbolSize = 30; // 调整大小
            } else {
                node.symbolSize = 15; // 调整大小
            }
        });

        // 配置 ECharts 图表
        var option = {
            tooltip: {},
            legend: {
				textStyle: { // 设置 legend 字体样式
				    fontSize: 18, // 设置字体大小
				    color: '#333333' ,// 可以设置字体颜色
					fontFamily:'Helvetica'
				},
                data: ['circRNA', 'RBP', 'miRNA', 'mRNA']
            },
            series: [{
                type: 'graph',
                layout: 'force',
                zoom: 2,
                data: data.nodes,
                links: data.links,
                categories: [
                    { name: 'RBP' },
                    { name: 'miRNA' },
                    { name: 'mRNA' },
                    { name: 'circRNA' }
                ],
               
                draggable: true,
                label: {
                    show: true,
                    position: 'right',
            textStyle: { // 设置节点标签字体样式
                fontFamily: 'Helvetica', // 设置字体族
                fontSize: 14, // 设置字体大小（可选）
               
            }
                },
                force: {
                    repulsion: 100
                },
                lineStyle: {
                    color: 'source',
                    curveness: 0.3
                },
                emphasis: {
                    focus: 'adjacency',
                    lineStyle: {
                        width: 10
                    }
                }
            }]
        };

        // 使用配置显示图表
        myChart.setOption(option);

        // 导出图表数据的功能
        document.getElementById('exportData').addEventListener('click', function() {
            exportChartData(data);
        });

        // 导出图表图片的功能
        document.getElementById('exportChart').addEventListener('click', function() {
            exportChartImage(myChart);
        });
    })
    .catch(function(error) {
        // 出错时显示准备好的图片
        console.error('Error fetching or processing data: ', error);
        document.getElementById('rgnetwork_echarts').style.display = 'none';
        document.getElementById('container1').style.display = 'block';
        document.getElementById('centeredText1').style.display = 'block';
    });

// 导出数据为 CSV 格式
function exportChartData(data) {
    let csvContent = 'data:text/csv;charset=utf-8,';
    
    // 添加节点标题行
    csvContent += 'NodeID,Category,SymbolSize\n';
    
    // 遍历并添加节点数据
    data.nodes.forEach(function(node) {
        csvContent += `${node.name},${node.category},${node.symbolSize}\n`;
    });

    // 添加边标题行
    csvContent += '\nSource,Target\n';
    
    // 遍历并添加边数据
    data.links.forEach(function(link) {
        csvContent += `${link.source},${link.target}\n`;
    });

    // 创建下载链接
    var encodedUri = encodeURI(csvContent);
    var link = document.createElement('a');
    link.setAttribute('href', encodedUri);
    link.setAttribute('download', 'network_data.csv');
    document.body.appendChild(link); // 需要将链接附加到 DOM 对于 Firefox 是必需的
    link.click(); // 触发下载
    document.body.removeChild(link); // 完成后移除链接
}

// 导出图表图片为 PNG
function exportChartImage(myChart) {
    var url = myChart.getDataURL({
        type: 'png', // 图片类型可以为 'png' 或 'jpeg'
        pixelRatio: 2, // 图片分辨率
        backgroundColor: '#fff' // 背景颜色
    });

    // 创建下载链接
    var link = document.createElement('a');
    link.href = url;
    link.download = 'network_image.png'; // 设置下载的文件名
    link.click(); // 触发点击下载
}
