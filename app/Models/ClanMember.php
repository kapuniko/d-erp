<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use App\Models\Clan;
use MoonShine\Laravel\Models\MoonshineUser;


class ClanMember extends Model
{
    use HasFactory;

    protected $table = 'clan_members';

    protected $fillable = [
        'id',
        'name',
        'level',
        'taxes',
        'real_name',
        'birthday',
        'gender',
        'date_of_joining'
    ];

    protected $casts = [
        'taxes' => 'array'
    ];

    public function clan(): BelongsTo
    {
        return $this->belongsTo(Clan::class);
    }

    public function moonshine_user():BelongsTo
    {
        return $this->belongsTo(MoonshineUser::class, 'moonshine_user_id', 'id');
    }
}
