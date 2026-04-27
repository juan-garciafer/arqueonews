<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class AppLayout extends Component
{
    /**
     * Get the view / contents that represents the component.
     */
    public $markers;

    public function __construct($markers = null)
    {
        $this->markers = $markers;
    }
    public function render(): View
    {
        return view('layouts.app');
    }
}
