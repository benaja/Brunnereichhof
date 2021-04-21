<?php

namespace App\Http\Requests;

use App\TransactionType;
use Illuminate\Foundation\Http\FormRequest;

class TransactionTypeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->user()->authorize(['superadmin'], ['transaction_write']);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => ['required', 'string'],
            'is_positive' => ['required', 'boolean'],
        ];
    }

    public function store()
    {
        $data = $this->validated();

        return TransactionType::create($data);
    }

    public function update(TransactionType $transactionType)
    {
        $data = $this->validated();

        $transactionType->update($data);

        return $transactionType;
    }
}
