<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use MoonShine\Laravel\Models\MoonshineUser;

class Clan extends Model
{
    use HasFactory;
    protected $table = 'clans';
    protected $fillable = [
        'id',
        'name',
        'owner',
        'token'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(MoonshineUser::class, 'owner', 'id');
    }
}
