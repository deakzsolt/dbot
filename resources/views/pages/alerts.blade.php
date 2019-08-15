<!doctype html>
<html lang="{{ app()->getLocale() }}">
	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">

		<title>Alerts</title>

		<!-- Fonts -->
		<link href="https://fonts.googleapis.com/css?family=Roboto:200,600" rel="stylesheet" type="text/css">
		<link href="{{ mix('css/whale-alerts.css') }}" rel="stylesheet">
	</head>

	<body>
		<div class="container">
			<h1>Whale Alerts</h1>
			<div class="whale-alerts">
				@foreach($alerts as $alert)
{{--					{{dd($alert)}}--}}
					<div><span class="date-time">{{\Illuminate\Support\Carbon::createFromTimestamp($alert['timestamp'])}}</span> <span>{{$alert['amount']}}</span> <span class="symbol">{{$alert['symbol']}}</span> <span class="transfers">({{$alert['amount_usd']}}USD) transferred from {{$alert['from_owner']}} wallet transferred to {{$alert['to_owner']}} wallet.</span></div>
				@endforeach
			</div>
		</div>
	</body>
