<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="csrf-token" content="{{ csrf_token() }}">

	<title>Charts</title>

	<!-- Fonts -->
	{{--<link href="https://fonts.googleapis.com/css?family=Roboto:200,600" rel="stylesheet" type="text/css">--}}
	{{--<script src="https://www.amcharts.com/lib/4/core.js"></script>--}}
	{{--<script src="https://www.amcharts.com/lib/4/charts.js"></script>--}}
	{{--<script src="https://www.amcharts.com/lib/4/themes/animated.js"></script>--}}
	<script src="//www.amcharts.com/lib/3/amcharts.js"></script>
	<script src="//www.amcharts.com/lib/3/serial.js"></script>
	<script src="//www.amcharts.com/lib/3/themes/light.js"></script>
	<script src="{{ mix('js/app.js')}}"></script>

	<style>
		body { background-color: #30303d; color: #fff; }
		#chartdiv {
			width: 100%;
			height: 500px;
		}

	</style>
</head>

<body>
	<div id="chartdiv"></div>
</body>
<script src="{{ mix('js/charts.js')}}"></script>