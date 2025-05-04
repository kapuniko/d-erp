<?php

namespace App\Livewire;

use App\Models\Artefact;
use Livewire\Component;
// УДАЛЯЕМ: use Livewire\Features\SupportFileUploads\WithFileUploads;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ArtefactForm extends Component
{

    public $name = '';
    public $type = 'buf';
    public $price = null;
    public string $image = '';

    // Правила валидации
    protected $rules = [
        'name' => 'required|string|max:255',
        'type' => 'required|string|in:buf,pot',
        'price' => 'nullable|numeric|min:0',
        'image' => 'nullable|string|max:255',
    ];

    public function mount()
    {
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->reset([
            'name',
            'type',
            'price',
            'image', // <-- Сбрасываем строку
        ]);
        $this->type = 'buf';
        $this->price = null;
        $this->image = ''; // <-- Убедимся, что сбрасывается в пустую строку
    }

    public function store()
    {
        Log::info('ArtefactForm: store called');

        $this->validate();
        Log::info('ArtefactForm: validation passed');

        // Создаем новый артефакт в базе данных
        Artefact::create([
            'name' => $this->name,
            'type' => $this->type,
            'price' => $this->price,
            'image' => $this->image,
            'user_id' => Auth::id(),

            // Добавьте другие поля
        ]);


        $this->resetForm();

        $this->dispatch('artefact-added');
        $this->dispatch('close-artefact-modal');

        // session()->flash('message', 'Артефакт успешно добавлен!');
    }

    public function render()
    {
        return view('livewire.artefact-form');
    }
}
