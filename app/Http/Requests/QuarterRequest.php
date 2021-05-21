<?php

namespace App\Http\Requests;

use App\Quarter;
use Illuminate\Foundation\Http\FormRequest;

class QuarterRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $required = request()->method() === 'POST' ? 'required' : '';

        return [
            'family_allowance_id' => ['integer', 'exists:family_allowances,id', $required],
            'type' => [$required, 'integer'],
            'value' => ['boolean', 'nullable'],
            'expiration_date' => ['date', 'nullable'],
        ];
    }

    public function store()
    {
        $data = $this->validated();

        return Quarter::create($data);
    }

    public function update(Quarter $quarter)
    {
        $data = $this->validated();

        $quarter->update($data);

        return $quarter;
    }
}
