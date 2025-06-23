<?php

namespace App\Console\Commands;

use App\Models\Parameter;
use Illuminate\Console\Command;
use App\Services\XmlParserService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;


class ParseXmlProducts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:parse-xml-products';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $service = new XmlParserService();

        $service->handle('app/private/feed.xml');


        $items = DB::table('product_parameters as pp')
            ->join('parameter_values as pv', 'pp.parameter_value_id', '=', 'pv.id')
            ->join('parameters as p', 'pv.parameter_id', '=', 'p.id')
            ->select('pp.product_id', 'p.slug as parameter', 'pv.value')
            ->get();

        foreach ($items as $item) {
            $key = "filter:{$item->parameter}:{$item->value}";
            Redis::sadd($key, $item->product_id);
        }


        $request = [
            'priznacennia' => 'Бородьба',
        ];

        $keys = [];

        foreach ($request as $parameter => $value) {
            $keys[] = "filter:$parameter:$value";
        }

        $productIds = Redis::sinter($keys);
    }
}
