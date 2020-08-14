<?php

namespace App\Console\Commands;

use App\Models\Exchanges;
use App\Traits\DataProcessing;
use App\Utils\Strategies;
use Carbon\Carbon;
use Illuminate\Console\Command;

class RsiStrategy extends Command
{
    use DataProcessing;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'trade:rsi';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Simple strategy to trade on RSI, now just under test.';

    /**
     * @var Strategies
     */
    private $strategies;

    /**
     * Create a new command instance.
     *
     * @param Strategies $strategies
     */
    public function __construct(Strategies $strategies)
    {
        $this->strategies = $strategies;
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $exchangeId = Exchanges::where('slug', 'poloniex')->first()->id;

        while (1) {

            $timeFrames = [
                '5m',
                '15m',
                '30m',
                '1h',
            ];

            foreach ($timeFrames as $timeFrame) {
                $this->line('<bg=green>TimeFrame => ' . $timeFrame . '</>');
                $results = $this->getLatestData('BTC/USDT', 150, $timeFrame);

                if ($results) {
                    $response = $this->strategies->strategy_rsi($results[$exchangeId]);
                    if (is_array($response)) {
                        $isUp = $response['isUp'] ? '<fg=blue>isUp:true</>' : '<fg=red>isUp:false</>';
                        $isDown = $response['isDown'] ? '<fg=blue>isDown:true</>' : '<fg=red>isDown:false</>';
                        $this->line(
                            '<fg=green>[' . Carbon::now(
                            ) . '] [' . $response['previousRsi'] . '] [' . $response['latestRsi'] . ']</> - ' . $isUp . ' - ' . $isDown . ' -- ' . implode(
                                ",",
                                $response['rsi']
                            ) . "\n"
                        );

                        if ($response['isUp']) {
                            $this->error('Sell!');
                        } // if

                        if ($response['isDown']) {
                            $this->line('<bg=green>Buy!</>');
                        } // if

                    } else {
                        $this->error($response);
                        exit();
                    } // if
                } else {
                    $this->error('There are not enough data to show up calculations.');
                    exit();
                }
            } // foreach

            sleep(5);
        } // while
    }
}
