document.addEventListener('DOMContentLoaded', function () {
    var chartDom = document.getElementById('pie1_echarts');
    var myChart = echarts.init(chartDom);
    var option;

    option = {
        title: {
            text: 'Distribution of Sequencing Platform',
            subtext: 'Dataset',
            left: 'center'
        },
        tooltip: {
            trigger: 'item' ,
            formatter: function(params) {
                return `${params.name}: ${params.value}%`;
            }
        },
        legend: {
            orient: 'vertical',
            left: 'left'
        },
        series: [
            {
                name: 'Sequencing Platform',
                type: 'pie',
                radius: '70%',
                data: [
                    { value: 7.69, name: 'HiSeq X Ten' },
                    { value: 3.85, name: 'Illumina HiSeq 1500' },
                    { value: 19.23, name: 'Illumina HiSeq 2500' },
                    { value: 34.62, name: 'Illumina HiSeq 4000' },
                    { value: 34.61, name: 'Illumina NovaSea 6000' }
                ],
                emphasis: {
                    itemStyle: {
                        shadowBlur: 10,
                        shadowOffsetX: 0,
                        shadowColor: 'rgba(0, 0, 0, 0.5)'
                    },
                    label: {
                        formatter: '{b}: {c}%'
                    }
                }
            }
        ]
    };

    option && myChart.setOption(option);
});
document.addEventListener('DOMContentLoaded', function () {
    var chartDom = document.getElementById('pie2_echarts');
    var myChart = echarts.init(chartDom);
    var option;

    option = {
        title: {
            text: 'Disease Types',
            subtext: 'Dataset',
            left: 'center'
        },
        tooltip: {
            trigger: 'item' ,
            formatter: function(params) {
                return `${params.name}: ${params.value}%`;
            }
        },
        legend: {
            orient: 'vertical',
            left: 'left'
        },
        series: [
            {
                name: 'Sequencing Platform',
                type: 'pie',
                radius: '70%',
                data: [
                    { value: 61.5, name: 'Infectious disease' },
                    { value: 3.8, name: 'Genetic disease' },
                    { value: 7.7, name: 'Cancer disease' },
                    { value: 3.8, name: 'Vaccine' },
                    { value: 3.8, name: 'Retinal disease' },
                    { value: 3.8, name: 'Metabolic disease' },
                    { value: 11.5, name: 'Mental disorder' },
                    { value: 3.8, name: 'Inflammatory disease' }
                ],
                emphasis: {
                    itemStyle: {
                        shadowBlur: 10,
                        shadowOffsetX: 0,
                        shadowColor: 'rgba(0, 0, 0, 0.5)'
                    },
                    label: {
                        formatter: '{b}: {c}%'
                    }
                }
            }
        ]
    };

    option && myChart.setOption(option);
});
/*
document.addEventListener('DOMContentLoaded', function () {
    var chartDom = document.getElementById('bar_echarts');
    var myChart = echarts.init(chartDom);

    var option;

    option = {
        tooltip: {
            trigger: 'axis',
            axisPointer: {
                type: 'shadow'
            }
        },
        legend: {
            data: ['Series A', 'Series B', 'Series C']
        },
        grid: {
            left: '3%',
            right: '4%',
            bottom: '3%',
            containLabel: true
        },
        xAxis: {
            type: 'category',
            data: ['Category 1', 'Category 2', 'Category 3', 'Category 4', 'Category 5']
        },
        yAxis: [
            {
                type: 'value',
                name: 'Y Axis 1',
                min: 0,
                max: 250,
                interval: 50,
            },
            {
                type: 'value',
                name: 'Y Axis 2',
                min: 0,
                max: 25,
                interval: 5,
            },
            {
                type: 'value',
                name: 'Y Axis 3',
                min: 0,
                max: 100,
                interval: 20,
            }
        ],
        series: [
            {
                name: 'Series A',
                type: 'bar',
                data: [40, 60, 80, 100, 120]
            },
            {
                name: 'Series B',
                type: 'bar',
                yAxisIndex: 1,
                data: [10, 15, 20, 25, 30]
            },
            {
                name: 'Series C',
                type: 'bar',
                yAxisIndex: 2,
                data: [30, 40, 50, 60, 70]
            }
        ]
    };

    option && myChart.setOption(option);
});
// summary.js
*/
document.addEventListener('DOMContentLoaded', function () {
    var chartDom = document.getElementById('bar_echarts');
    var myChart = echarts.init(chartDom);
var option;

option = {
    legend: {},
    tooltip: {},
    dataset: {
        source: [
            ['Dataset', 'PRJNA352396', 'PRJNA352396', 'PRJNA390289', 'PRJNA429023', 'PRJNA507472',
                'PRJNA533086',
                'PRJNA545334',
                'PRJNA562305',
                'PRJNA588242',
                'PRJNA600846',
                'PRJNA606351',
                'PRJNA645700',
                'PRJNA662985',
                'PRJNA680771',
                'PRJNA689555',
                'PRJNA722046',
                'PRJNA738854',
                'PRJNA739257',
                'PRJNA741686',
                'PRJNA744408',
                'PRJNA754685',
                'PRJNA787316',
                'PRJNA787461',
                'PRJNA796224',
                'PRJNA849921',
                'PRJNA891054',
                'PRJNA901461'],
            ['Matcha Latte', 41.1, 30.4, 65.1, 53.3],
            ['Milk Tea', 86.5, 92.1, 85.7, 83.1],
            ['Cheese Cocoa', 24.1, 67.2, 79.5, 86.4]
        ]
    },
    xAxis: [
        { type: 'category', gridIndex: 0 },
        { type: 'category', gridIndex: 1 }
    ],
    yAxis: [{ gridIndex: 0 }, { gridIndex: 1 }],
    grid: [{ bottom: '55%' }, { top: '55%' }],
    series: [
        // These series are in the first grid.
        { type: 'bar', seriesLayoutBy: 'row' },
        { type: 'bar', seriesLayoutBy: 'row' },
        { type: 'bar', seriesLayoutBy: 'row' },
        // These series are in the second grid.
        { type: 'bar', xAxisIndex: 1, yAxisIndex: 1 },
        { type: 'bar', xAxisIndex: 1, yAxisIndex: 1 },
        { type: 'bar', xAxisIndex: 1, yAxisIndex: 1 },
        { type: 'bar', xAxisIndex: 1, yAxisIndex: 1 }
    ]
};

option && myChart.setOption(option);
});