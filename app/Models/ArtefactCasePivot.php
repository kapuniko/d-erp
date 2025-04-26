<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Pivot;

class ArtefactCasePivot extends Pivot
{
    protected static function booted(): void
    {
        static::created(function (ArtefactCasePivot $pivot) {
            $pivot->recalculateCaseCost();
        });

        static::updated(function (ArtefactCasePivot $pivot) {
            $pivot->recalculateCaseCost();
        });

        static::deleted(function (ArtefactCasePivot $pivot) {
            $pivot->recalculateCaseCost();
        });
    }

    public function recalculateCaseCost(): void
    {
        $case = ArtefactsCase::find($this->artefacts_case_id);

        if (!$case) return;

        $total = $case->artefacts->sum(function ($artefact) {
            $count = $artefact->pivot?->artefact_in_case_count ?? 1;
            return $count * $artefact->price;
        });

        $case->update(['case_cost' => $total]);
    }
}
