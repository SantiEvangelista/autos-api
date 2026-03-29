<?php

namespace App\Http\Requests\Concerns;

trait HandlesRelationsParam
{
    protected function prepareRelationsParam(): void
    {
        if ($this->has('relations') && is_string($this->input('relations'))) {
            $this->merge(['relations' => array_filter(array_map('trim', explode(',', $this->input('relations'))))]);
        }
    }
}
