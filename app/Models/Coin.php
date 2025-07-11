<?php

declare(strict_types=1);

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Coin extends Model
{
    protected $fillable = [
		'name',
		'image',
		'description',
		'type',
    ];
}
