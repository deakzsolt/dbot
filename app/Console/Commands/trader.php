<?php

namespace App\Console\Commands;

use App\Ticker;
use Illuminate\Console\Command;
use App\Traits\DataProcessing;
use App\Traits\Strategies;

class trader extends Command
{
    use DataProcessing, Strategies;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'run:trader';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Basic strategy with SMA, Stochastic and RSI.';

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

        $this->info("------------------------------------------------------------------");
        $this->info("This is just an example when it should trade");
        $this->info("1 buy signal");
        $this->info("-1 sell signal");
        $this->info("0 do nothing");
        $this->info("------------------------------------------------------------------\n");

        while(1) {
            $this->line("Date: ".date('Y-m-d H:i:s'));
            $headers = array();
            $data = array();
            foreach(Ticker::getPairs() as $pairs) {
                $headers[] = $pairs['symbol'];
                $datas = $this->getLatestData($pairs['symbol'],200,'1h');
                $response = $this->strategy_sma_stoch_rsi($datas[111]);

                switch($response['state']) {
                    case 1:
                        $state = "<fg=green>".$response['state']."</>";
                        break;
                    case -1:
                        $state = "<fg=red>".$response['state']."</>";
                        break;
                    case 0:
                        $state = "<fg=yellow>".$response['state']."</>";
                        break;
                } // switch

                $data[0][] = $response['strategy'];
                $data[1][] = 'price: '.$response['price'];
                $data[2][] = 'SMA: '.$response['sma'];
                $data[3][] = '%K: '.$response['slowk'];
                $data[4][] = '%D: '.$response['slowd'];
                $data[5][] = 'RSI: '.$response['rsi'];
                $data[6][] = $response['side'];
                $data[7][] = $state;
            } // foreach

            $this->table($headers, $data);
            sleep(5);
        } // while
    }
}
