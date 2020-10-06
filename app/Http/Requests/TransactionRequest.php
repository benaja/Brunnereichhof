<?php

namespace App\Http\Requests;

use App\Transaction;
use Illuminate\Foundation\Http\FormRequest;

class TransactionRequest extends FormRequest
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
            'amount' => ['required', 'numeric'],
            'date' => ['required','date'],
            'comment' => ['nullable', 'string'],
            'transaction_type_id' => ['required', 'exists:transaction_types,id'],
            'employee_id' => ['required', 'exists:employee,id']
        ];
    }

    public function store() {
        $data = $this->validated();

        return Transaction::create($data);
    }

    public function update(Transaction $transaction) {
        $data = $this->validated();

        $transaction->update($data);
        return $transaction;
    }
}
