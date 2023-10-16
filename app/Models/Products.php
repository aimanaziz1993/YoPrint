<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Products extends Model
{
    protected $fillable = [ 'UNIQUE_KEY', 'PRODUCT_TITLE', 'PRODUCT_DESCRIPTION', 'STYLE#', 'SANMAR_MAINFRAME_COLOR', 'SIZE', 'COLOR_NAME', 'PIECE_PRICE' ];

    protected $guarded = [];


    public function setProductTitleAttribute($value)
    {
        $this->attributes['PRODUCT_TITLE'] = $this->cleanNonUtf8($value);
    }

    private function cleanNonUtf8($string)
    {
        $a = preg_replace("/(&#[0-9]+;)/", "", $string);
        $b = str_replace("  ", "", $a);
        return $b;
    }

}
