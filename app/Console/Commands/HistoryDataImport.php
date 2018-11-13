<?php

namespace App\Console\Commands;

use App\Models\Exchanges;
use ccxt\Exchange;
use Illuminate\Console\Command;
use App\Ticker;

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
                $this->comment("\nUpdating $symbol data...");

                $startDate = date('Y-m-d h:i:s',strtotime('-7 days'));
                $startTimestamp = strtotime($startDate);

                $endDate = date('Y-m-d h:i:s');
                $endTimestamp = strtotime($endDate);

                $this->line("start=$startDate($startTimestamp) end=$endDate($endTimestamp)\n");

                $url = sprintf(__(config('dbot.'.$exchange.'.chart_data')),$symbol,$startTimestamp,$endTimestamp,$this->period);
                $json = file_get_contents($url);
                $response = json_decode($json);

                if (isset($response->error)) {
                    $this->error("ERROR: ".$response->error);
                    exit();
                } // if

                $bar = $this->output->createProgressBar(count($response));
                $bar->start();

                foreach ($response as $data) {
                    $change = number_format($data->close-$data->open,16);
                    $percentage = $change/$data->close*100;
                    $average = ($data->close+$data->open)/2;

                    $ccxt = new Exchange();
                    list ($quoteId, $baseId) = explode ('_', $symbol);
                    $base = $ccxt->common_currency_code($baseId);
                    $quote = $ccxt->common_currency_code($quoteId);
                    $ccxtSymbol = $base . '/' . $quote;

                    $ticker = new Ticker();
                    $ticker::updateOrCreate(
                        array(
                            'exchange_id' => $exchangeId,
                            'symbol' => $ccxtSymbol,
                            'timestamp' => $data->date,
                            'datetime' => date('Y-m-d H:i:s', $data->date),
                            'high' => $data->high,
                            'low' => $data->low,
                            'vwap' => NULL,
                            'open' => $data->open,
                            'close' => $data->close,
                            'last' => $data->close,
                            'change' => $change,
                            'percentage' => $percentage,
                            'average' => $average,
                            'baseVolume' => $data->volume,
                            'quoteVolume' => $data->quoteVolume,
                            'created_at' => date('Y-m-d H:i:s', $data->date)
                        )
                    );
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
    }
}
