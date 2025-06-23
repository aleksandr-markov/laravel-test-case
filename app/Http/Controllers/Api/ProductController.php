<?php

namespace App\Http\Controllers\Api;

use App\Models\Product;
use App\Models\Parameter;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Redis;

class ProductController extends Controller
{

    public function products(Request $request)
    {



        $products = Product::query();


        // $request = [
        //     'page' => 1,
        //     'limit' => 1,
        //     'sort_by' => 'price_asc',
        //     'filter' => 'filter'
        // ];

        $products = Product::query()->paginate();

        return response()->json($products, 200);
    }

    public function filters(Request $request)
    {
        $activeFilters = $request->input('filter', []);
        foreach ($activeFilters as $k => $v) {
            if (!is_array($v))
                $activeFilters[$k] = [$v];
        }

        $parameters = Parameter::with('values')->get();

        $result = [];

        foreach ($parameters as $parameter) {
            $paramSlug = $parameter->slug;
            $paramName = $parameter->name;

            $valuesArr = [];

            foreach ($parameter->values as $value) {
                $valueName = $value->value;

                $redisKeys = [];

                foreach ($activeFilters as $filterSlug => $filterValues) {
                    if ($filterSlug === $paramSlug)
                        continue;

                    foreach ($filterValues as $filterValue) {
                        $redisKeys[] = "filter:$filterSlug:$filterValue";
                    }
                }

                $redisKeys[] = "filter:$paramSlug:$valueName";

                if (count($redisKeys) === 1) {
                    $count = Redis::scard($redisKeys[0]);
                } else {
                    $count = Redis::sinter($redisKeys);
                    $count = count($count);
                }

                $active = isset($activeFilters[$paramSlug]) && in_array($valueName, $activeFilters[$paramSlug]);

                $valuesArr[] = [
                    'value' => $valueName,
                    'count' => $count,
                    'active' => $active,
                ];
            }

            $result[] = [
                'name' => $paramName,
                'slug' => $paramSlug,
                'values' => $valuesArr,
            ];
        }

        return response()->json($result);
    }
}
