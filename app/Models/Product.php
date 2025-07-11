<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{

    protected $guarded = [];
    public function pictures()
    {
        return $this->hasMany(ProductPicture::class);
    }

    public function parameterValues()
    {
        return $this->belongsToMany(ParameterValue::class, 'product_parameters');
    }
}
