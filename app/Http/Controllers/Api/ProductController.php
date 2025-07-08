<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Parameter;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;

class ProductController extends Controller
{
    public function products(Request $request)
    {
        $limit = $request->get('limit');
        $query = Product::query()->with('pictures');

        $keys = [];
        $productIds = 0;
        if ($filter = $request->get('filter')) {
            foreach ($request->get('filter') as $parameter => $values) {
                foreach ($values as $item) {
                    $keys[] = "filter:$parameter:$item";
                }
            }

            $productIds = Redis::sunion(...$keys);
        }

        if ($productIds) {
            $query->whereIn('id', $productIds);
        }

        $products = $query->paginate($limit);

        return response()->json($products);
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
