<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Artefact extends Model
{
    protected $fillable = [
        'game_id',
        'name',
        'type',
        'image',
        'description',
        'duration_sec',
        'level',
        'group',
        'price',
        'user_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function artefactsCases(): BelongsToMany
    {
        return $this->belongsToMany(ArtefactsCase::class, 'artefact_case', 'artefact_id', 'artefacts_case_id');
    }
}

