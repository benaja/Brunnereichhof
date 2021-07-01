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
        $sometimes = request()->method() === 'PATCH';

        return [
            'amount' => ['required', 'numeric', $sometimes ? 'sometimes' : ''],
            'date' => ['required', 'date', $sometimes ? 'sometimes' : ''],
            'comment' => ['nullable', 'string', $sometimes ? 'sometimes' : ''],
            'transaction_type_id' => ['required', 'exists:transaction_types,id', $sometimes ? 'sometimes' : ''],
            'user_id' => ['required', 'exists:user,id', $sometimes ? 'sometimes' : ''],
            'entered' => ['nullable', 'boolean', $sometimes ? 'sometimes' : ''],
        ];
    }

    public function store()
    {
        $data = $this->validated();

        return Transaction::create($data);
    }

    public function update(Transaction $transaction)
    {
        $data = $this->validated();

        $transaction->update($data);

        return $transaction;
    }
}
