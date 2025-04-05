<?php

declare(strict_types=1);

namespace App\MoonShine\Resources;

use App\Models\User;

use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Text;

/**
 * @extends ModelResource<User>
 */
class UserResource extends ModelResource
{
    protected string $model = User::class;
    protected string $column = 'name';

    public function indexFields(): iterable
    {
        // TODO correct labels values
        return [
			ID::make('id'),
			Text::make('name', 'name'),
			Text::make('email', 'email'),
			Text::make('email_verified_at', 'email_verified_at'),
			Text::make('password', 'password'),
			Text::make('remember_token', 'remember_token'),
			Text::make('telegram_id', 'telegram_id'),
        ];
    }

    public function formFields(): iterable
    {
        return [
            Box::make([
                ...$this->indexFields()
            ])
        ];
    }

    public function detailFields(): iterable
    {
        return [
            ...$this->indexFields()
        ];
    }

    public function filters(): iterable
    {
        return [
        ];
    }

    public function rules(mixed $item): array
    {
        // TODO change it to your own rules
        return [
			'name' => ['string', 'required'],
			'email' => ['email', 'required'],
			'email_verified_at' => ['string', 'nullable'],
			'password' => ['password', 'required'],
			'remember_token' => ['string', 'nullable'],
			'telegram_id' => ['string', 'nullable'],
        ];
    }
}
