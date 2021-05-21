<?php

namespace App\Http\Requests;

use App\Child;
use Illuminate\Foundation\Http\FormRequest;

class ChildRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'family_allowance_id' => ['integer', 'exists:family_allowances,id', $this->method() === 'POST' ? 'required' : 'nullable'],
            'firstname' => ['string', 'nullable'],
            'lastname' => ['string', 'nullable'],
            'birthdate' => ['date', 'nullable'],
        ];
    }

    public function store()
    {
        $data = $this->validated();

        return Child::create($data);
    }

    public function update(Child $child)
    {
        $data = $this->validated();

        $child->update($data);

        return $child;
    }
}
