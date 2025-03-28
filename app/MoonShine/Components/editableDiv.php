<?php

declare(strict_types=1);

namespace App\MoonShine\Components;

use MoonShine\UI\Components\MoonShineComponent;

/**
 * @method static static make(int $clan_id)
 */
final class EditableDiv extends MoonShineComponent
{
    protected string $view = 'admin.components.editable-div';

    protected int $clan_id;

    public function __construct(int $clan_id)
    {
        parent::__construct();

        $this->clan_id = $clan_id;
    }

    protected function viewData(): array
    {
        return [
            'clan_id' => $this->clan_id,
        ];
    }
}
