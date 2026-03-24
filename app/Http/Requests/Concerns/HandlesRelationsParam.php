<?php

namespace App\Http\Requests\Concerns;

trait HandlesRelationsParam
{
    protected function prepareRelationsParam(): void
    {
        if ($this->has('relations') && is_string($this->input('relations'))) {
            $this->merge(['relations' => [$this->input('relations')]]);
        }
    }
}
