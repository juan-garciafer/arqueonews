<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class formularioNoticia extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct(
        public $noticia = null,
        public $method = 'POST',
        public $action = '',
    )
    {
        
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.formulario-noticia');
    }
}
