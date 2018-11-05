<?php

use Illuminate\Database\Seeder;

class insertOptions extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = array(
            'DATA_IMPORTER' => array(
                'poloniex' => array(
                    'BTC/USDT',
                    'ETH/USDT',
                    'ETC/USDT',
                    'ZEC/USDT',
                    'ZEC/BTC',
                    'ZRX/USDT'
                )
            )
        );

        foreach ($data as $key => $value) {
            $option = new \App\Models\Options();
            $option->item = $key;
            $option->value = serialize($value);
            $option->save();
        } // foreach
    }
}
