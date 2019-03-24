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
