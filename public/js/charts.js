// // Themes begin
// // am4core.useTheme(am4themes_animated);
// // Themes end
//
// // Create chart instance
// var chart = am4core.create("chartdiv", am4charts.XYChart);
//
// // Increase contrast by taking evey second color
// chart.colors.step = 2;
//
// // Add data
// // chart.data = generateChartData();
//
// $.ajax({
//     type: 'GET',
//     url: '/charts/data'
// }).done(function (response) {
//     var slowk = [],
//         slowd = [];
//     $.each(response[0], function (i, k) {
//         slowk.push(k);
//     });
//     $.each(response[1], function (i, k) {
//         slowd.push(k);
//     });
//     chart.data = generateChartData(slowk,slowd);
// });
//
// // Create axes
// var dateAxis = chart.xAxes.push(new am4charts.DateAxis());
// dateAxis.renderer.minGridDistance = 50;
//
// // Create series
// function createAxisAndSeries(field, name, opposite, bullet) {
//     var valueAxis = chart.yAxes.push(new am4charts.ValueAxis());
//
//     var series = chart.series.push(new am4charts.LineSeries());
//     series.dataFields.valueY = field;
//     series.dataFields.dateX = "date";
//     series.strokeWidth = 2;
//     series.yAxis = valueAxis;
//     series.name = name;
//     series.tooltipText = "{name}: [bold]{valueY}[/]";
//     series.tensionX = 0.8;
//
//     var interfaceColors = new am4core.InterfaceColorSet();
//
//     // var bullet = series.bullets.push(new am4charts.CircleBullet());
//     // bullet.circle.stroke = interfaceColors.getFor("background");
//     // bullet.circle.strokeWidth = 2;
//
//     valueAxis.renderer.line.strokeOpacity = 1;
//     valueAxis.renderer.line.strokeWidth = 2;
//     valueAxis.renderer.line.stroke = series.stroke;
//     valueAxis.renderer.labels.template.fill = series.stroke;
//     valueAxis.renderer.opposite = opposite;
//     valueAxis.renderer.grid.template.disabled = true;
//
// }
//
// createAxisAndSeries("slowk", "SlowK", false);
// createAxisAndSeries("slowd", "SlowD", false);
//
// // Add legend
// chart.legend = new am4charts.Legend();
//
// // Add cursor
// chart.cursor = new am4charts.XYCursor();
//
// // var views = 8700;
//
// // generate some random data, quite different range
// function generateChartData(slowk,slowd) {
//     var chartData = [];
//     var firstDate = new Date();
//     firstDate.setDate(firstDate.getDate());
//     firstDate.setHours(0, 30, 0, 0);
//
//     for (var i = 0; i < slowk.length; i++) {
//         // we create date objects here. In your data, you can have date strings
//         // and then set format of your dates using chart.dataDateFormat property,
//         // however when possible, use date objects, as this will speed up chart rendering.
//         var newDate = new Date(firstDate);
//         newDate.setDate(newDate.getDate() + i);
//
//         // visits += Math.round((Math.random()<0.5?1:-1)*Math.random()*10);
//         // hits += Math.round((Math.random()<0.5?1:-1)*Math.random()*10);
//         // console.log(visits[i]);
//
//         chartData.push({
//             date: newDate,
//             slowk: slowk[i],
//             slowd: slowd[i],
//         });
//     }
//     return chartData;
// }
function my_function() {
  $.ajax({
    type: 'GET',
    url: '/charts/data'
  }).done(function (data) {
    var chart = AmCharts.makeChart("chartdiv", {
      "type": "serial",
      "dataProvider": data,
      "valueAxes": [{
        "gridColor": "#FFFFFF",
        "gridAlpha": 0.2,
        "dashLength": 0
      }],
      "gridAboveGraphs": true,
      "graphs": [{
        "title": "Slow K",
        "lineColor": "#008800",
        "balloonText": "[[title]]: <b>[[value]]</b>",
        "bullet": "round",
        "bulletSize": 5,
        "bulletBorderColor": "#ffffff",
        "bulletBorderAlpha": 1,
        "bulletBorderThickness": 2,
        "valueField": "value1"
      }, {
        "title": "Slow D",
        "lineColor": "#ff0000",
        "balloonText": "[[title]]: <b>[[value]]</b>",
        "bullet": "round",
        "bulletSize": 5,
        "bulletBorderColor": "#ffffff",
        "bulletBorderAlpha": 1,
        "bulletBorderThickness": 2,
        "valueField": "value2"
      }, {
        "title": "ADX",
        "lineColor": "#0000FF",
        "balloonText": "[[title]]: <b>[[value]]</b>",
        "bullet": "round",
        "bulletSize": 5,
        "bulletBorderColor": "#ffffff",
        "bulletBorderAlpha": 1,
        "bulletBorderThickness": 2,
        "valueField": "value3"
      }],
      "categoryField": "category",
      "chartCursor": {
        "categoryBalloonEnabled": false,
        "cursorAlpha": 0,
        "zoomable": false
      },
      "legend": {}
    });
  });
}

setInterval("my_function();", 5000);
