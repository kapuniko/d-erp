<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaxAmount extends Model
{
    use HasFactory;

    protected $table = 'tax_amounts';

    protected $fillable = [
        'pers_level',
        'gold_amount_month',
        'crystals_amount_month',
        'pages_amount_month',
        'gold_amount_year',
        'crystals_amount_year',
        'pages_amount_year'
    ];
}
