<?php

namespace App\View\Components;


use Illuminate\Support\Str;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class SearchableSelect extends Component
{


    public function __construct(
        public ?string $label = null,
        public string $placeholder = 'Selecciona una opción...',
        public $value = null, // valor inicial (opcional)
    ) {}

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.searchable-select');
    }
}
