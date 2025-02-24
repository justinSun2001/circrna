window.onload = function(){
layui.use(['table','form'], function(){/*layui引入*/
var table = layui.table,form = layui.form,$=layui.$;


/*获取get方式传递的Dataset*/
var urlParams = new URLSearchParams(window.location.search);
var Samples = urlParams.get("Samples");
console.log('Samples',Samples);

var linetxt_Dom = document.getElementById('linetxt');
linetxt_Dom.innerHTML = Samples;
//sample.html --> Demographic_table
var cirRNAs = table.render({
	elem: '#Demographic_table'
	,page: false
	,width:1460
	,url: './interface/sample_Demographic_API.php'
	,id : 'cirRNAs'
	,where: {Samples: Samples, page: 1, limit: 10}
	,cols: [[ //表头
		{field: 'group', title: 'group',align:'center'}
	  ,{field: 'AvgSpotLen', title: 'AvgSpotLen',align:'center'}
	  ,{field: 'Bases', title: 'Bases',align:'center'}
	  ,{field: 'Bytes', title: 'Bytes',align:'center'}

	]]
});
//sample.html --> CircRNA
var cirRNAs = table.render({
	elem: '#sample_CircRNA'
	,page: false
	,width:1460
	,url: './interface/sample_CircRNA_API.php'
	,id : 'cirRNAs'
	,where: {Samples: Samples, page: 1, limit: 10}
	,cols: [[ //表头
	  {field: 'BloodCircR_ID', title: 'BloodCircR_ID',sort:true,align:'center'}
	  ,{field: 'DetectCount', title: 'DetectCount',sort:true,align:'center'}
	  ,{field: 'Length', title: 'Length',sort:true,align:'center'}
	  ,{field: 'EffectiveLength', title: 'EffectiveLength',sort:true,align:'center'}
	  ,{field: 'TPM', title: 'TPM',sort:true,align:'center'}
	  ,{field: 'NumReads', title: 'NumReads',sort:true,align:'center'}
	]]
});







});/*layui 使用区域收尾*/

}