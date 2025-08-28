<?php

namespace App\View\Components\DataEntry;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Select extends Component
{
    public $label;
    public $name;
    public $options;
    public $placeholder;
    public $selected;

    /**
     * Create a new component instance.
     *
     * @param string $name
     * @param array|\Illuminate\Database\Eloquent\Collection $options
     * @param string $placeholder
     * @param mixed $selected
     */
    public function __construct($name, $options = [], $placeholder = 'Select an option', $selected = null, $label = null)
    {
        $this->name = $name;
        $this->placeholder = $placeholder;
        $this->selected = $selected;
        $this->label = $label;

        // If the options are a model collection, format them for Select2
        if ($options instanceof \Illuminate\Database\Eloquent\Collection) {
            $this->options = $options->pluck('name', 'id')->toArray();
        } else {
            $this->options = $options;
        }
    }
    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.data-entry.select');
    }
}
