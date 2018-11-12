<?php

namespace App\Console\Commands;

use App\Models\Exchanges;
use Illuminate\Console\Command;

class HistoryDataImport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:history {pair} {exchange=poloniex}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'When adding a new pair this script should be used to import fresh data.';

    /**
     * This is in seconds
     *
     * @var int
     */
    protected $period = 1800;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {

        $this->info("--------------------------------------------------------------------------------------------");
        $this->info("This script by default loads 7 day data from selected exchange");
        $this->info("");
        $this->info("--------------------------------------------------------------------------------------------\n");

        $symbols = explode(',',$this->argument('pair'));
        $exchange = $this->argument('exchange');
        $exchangeId = Exchanges::where('slug',$exchange)->first()->id;

        try {
            foreach ($symbols as $symbol) {
                $this->comment("Updating $symbol data...");

                $startDate = date('Y-m-d h:i:s',strtotime('-7 days'));
                $startTimestamp = strtotime($startDate);

                $endDate = date('Y-m-d h:i:s');
                $endTimestamp = strtotime($endDate);

                $this->line("start=$startDate($startTimestamp) end=$endDate($endTimestamp)");

                $url = "https://poloniex.com/public?command=returnChartData&currencyPair=$symbol&start=$startTimestamp&end=$endTimestamp&period=$this->period";
                $json = file_get_contents($url);
                $data = json_decode($json);

                if (isset($data->error)) {
                    $this->error("ERROR: ".$data->error);
                    exit();
                } // if

                $bar = $this->output->createProgressBar(count($data));
                $bar->start();

                foreach ($data as $val) {
//                    TODO insert into db finish here
                    $bar->advance();
                    usleep(10000);
                } // foreach

                $bar->finish();
            } // foreach
        }
        catch (\Exception $e) {
            $this->error($e);
        }

        $this->info("\n\n--------------------------------------------------------------------------------------------");
        $this->info("Exiting: all data imported.");
        $this->info("Have a great day.");
        $this->info("Bye.");

        /*
         *  returnChartData
         * https://poloniex.com/support/api/#reference_currencypairs

Returns candlestick chart data.
        Required GET parameters are "currencyPair", "period"
        (candlestick period in seconds; valid values are 300, 900, 1800, 7200, 14400, and 86400),
        "start", and "end". "Start" and "end" are given in UNIX timestamp format and used to specify the date range
        for the data returned. Sample output:

[
  {
    "date": 1405699200,
    "high": 0.0045388,
    "low": 0.00403001,
    "open": 0.00404545,
    "close": 0.00427592,
    "volume": 44.11655644,
    "quoteVolume": 10259.29079097,
    "weightedAverage": 0.00430015
  },
  ...
]

Call: https://poloniex.com/public?command=returnChartData&currencyPair=BTC_XMR&start=1405699200&end=9999999999&period=14400

         */
    }
}
