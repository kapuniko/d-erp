<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

use App\Models\ArtefactCasePivot;


class ArtefactsCase extends Model
{

    protected $fillable = [
        'name',
        'user_id',
        'type',
        'calendar_date',
        'calendar_time',
        'sample_order',
        'case_cost',
        'case_profit',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function artefacts(): BelongsToMany
    {
        return $this->belongsToMany(Artefact::class, 'artefact_case', 'artefacts_case_id', 'artefact_id')
            ->using(ArtefactCasePivot::class);
    }
}
