<?php


namespace App\Console\Commands;


use App\Http\Models\Alerts;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * Class WhaleAlert
 * @package App\Console\Commands
 */
class WhaleAlert extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'import:whalealert';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Whale Alert API, created for cron job to import the data.';

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();
	}

	public function handle()
	{
		$apiKey = env('WHALE_ALERT_KEY');
		$whaleAlert = 'https://api.whale-alert.io/v1/transactions';

		try {
			$response = $this->connect($whaleAlert, $apiKey);
			if ($response['result'] == 'success') {

				if ($response['count'] > 0) {
					foreach ($response['transactions'] as $transaction) {
						$alert = new Alerts();
						$alert::updateOrCreate(
							[
								'cursor'     => $response['cursor'],
								'blockchain' => $transaction['blockchain'],
								'symbol'     => $transaction['symbol'],
								'hash'       => $transaction['hash'],
							],
							[
								'transaction_id'    => $transaction['id'],
								'transaction_type'  => $transaction['transaction_type'],
								'timestamp'         => $transaction['timestamp'],
								'from_address'      => $transaction['from']['address'],
								'from_owner_type'   => $transaction['from']['owner_type'],
								'from_owner'        => $transaction['from']['owner'] ?? 'unknown',
								'to_address'        => $transaction['to']['address'],
								'to_owner_type'     => $transaction['to']['owner_type'],
								'to_owner'          => $transaction['to']['owner'] ?? 'unknown',
								'amount'            => $transaction['amount'],
								'amount_usd'        => $transaction['amount_usd'],
								'transaction_count' => $transaction['transaction_count'],
							]
						);
					} // foreach
				} // if

			} else {
				$this->error($response['message']);
			} // if

		} catch (Exception $exception) {
			Log::error('[Error] ' . $exception->getMessage());
		}
	}

	/**
	 * Make CURL request as POST
	 *
	 * @return mixed
	 */
	private function connect($url, $apiKey)
	{
		$date = date("Y-m-d H:i:s");
		$time = strtotime($date);
		$time = $time - (10 * 60);
		$date = date("Y-m-d H:i:s", $time);
		$startTimestamp = strtotime($date);
		$endTimestamp = strtotime(date("Y-m-d H:i:s"));

		// TODO try to use TimeWarp for the start and end timestamp

		$query = http_build_query(
			[
				'api_key'   => $apiKey,
				'min_value' => 500000,
				'start'     => $startTimestamp,
				'end'       => $endTimestamp,
				'cursor'    => '',
			]
		);

		$url = $url . "?" . $query;

		$curl = curl_init();

		curl_setopt_array(
			$curl,
			[
				CURLOPT_URL            => $url,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_TIMEOUT        => 30,
				CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
				CURLOPT_CUSTOMREQUEST  => "GET",
				CURLOPT_HTTPHEADER     => [
					"cache-control: no-cache",
				],
			]
		);

		$response = curl_exec($curl);
		curl_close($curl);

		return json_decode($response, true);
	} // call
}
