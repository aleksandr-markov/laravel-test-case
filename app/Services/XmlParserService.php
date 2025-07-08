<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Parameter;
use Illuminate\Support\Str;
use App\Models\ParameterValue;
use App\Models\ProductPicture;
use Illuminate\Support\Facades\DB;

class XmlParserService
{


    public function handle($fileName)
    {
        $xml = $this->getXmlStringFromFile($fileName);

        foreach ($xml->shop->offers->offer as $offer) {
            $preparedProduct = $this->getPreparedProductForSave($offer);

            $this->saveProduct($preparedProduct);
        }
    }

    private function saveProduct($preparedOffer)
    {
        try {
            DB::beginTransaction();

            $product = Product::create($preparedOffer['product']);

            $valuesIds = $this->saveParamsAndValues($preparedOffer['params']);
            $product->parameterValues()->sync($valuesIds);


            $pictures = collect($preparedOffer['pictures'])->map(function ($imgSrc) {
                return new ProductPicture(['img_src' => $imgSrc]);
            });
            $product->pictures()->saveMany($pictures);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            logger()->critical($e);
        }
    }

    private function saveParamsAndValues($params)
    {
        $savedValuesIds = [];
        foreach ($params as $paramName => $paramValue) {
            $parameter = Parameter::firstOrCreate(
                ['name' => $paramName],
                ['slug' => Str::slug($paramName, '_')]
            );

            $value = ParameterValue::firstOrCreate([
                'parameter_id' => $parameter->id,
                'value' => $paramValue,
            ]);

            $savedValuesIds[] = $value->id;
        }

        return $savedValuesIds;
    }

    private function getXmlStringFromFile(string $fileName)
    {
        $xmlString = file_get_contents(storage_path($fileName));

        return simplexml_load_string($xmlString);
    }

    private function getPreparedProductForSave($offer)
    {
        return [
            'product' => $this->getProductInfo($offer),
            'pictures' => $this->getProductPictures($offer->picture),
            'params' => $this->getProductParams($offer->param)
        ];
    }

    private function getProductInfo($offer)
    {
        return [
            'external_id' => (string) $offer['id'],
            'currency' => (string) $offer->currencyId,
            'name' => (string) $offer->name,
            'description' => trim((string) $offer->description),
            'price' => (float) $offer->price,
            'stock_quantity' => (int) $offer->stock_quantity,
        ];
    }

    private function getProductPictures($offerPictures)
    {
        $preparedPictures = [];
        foreach ($offerPictures as $img) {
            $preparedPictures[] = (string) Str::of($img)->squish();
        }

        return $preparedPictures;
    }

    private function getProductParams($params)
    {
        $preparedParams = [];

        foreach ($params as $param) {
            $paramName = (string) $param['name'];
            $paramValue = (string) $param;
            $preparedParams[$paramName] = $paramValue;
        }

        return $preparedParams;
    }

}
