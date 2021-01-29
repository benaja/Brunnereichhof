<?php

namespace App\Http\Requests;

use App\Tool;
use Illuminate\Foundation\Http\FormRequest;

class ToolRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $sometimes =  $this->method() === 'PATCH' ? 'sometimes' : '';

        return [
            'name' => ['required', 'string', $sometimes],
            'amount' => ['required', 'integer', $sometimes],
        ];
    }

    public function store() {
        $data = $this->validated();

        return Tool::create($data);
    }

    public function update(Tool $tool) {
        $data = $this->validated();

        $tool->update($data);

        return $tool;
    }
}
