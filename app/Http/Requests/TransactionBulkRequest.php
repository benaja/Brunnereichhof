<?php

namespace App\Http\Requests;

use App\Transaction;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class TransactionBulkRequest extends FormRequest
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
            'transactions.*.amount' => ['required', 'numeric'],
            'transactions.*.date' => ['required','date'],
            'transactions.*.comment' => ['nullable', 'string'],
            'transactions.*.transaction_type_id' => ['required', 'exists:transaction_types,id'],
            'transactions.*.employee_id' => ['required', 'exists:employee,id']
        ];
    }

    public function store() {
        $data = $this->validated();

        $transactions = array_map(function ($transaction) {
            $transaction['created_at'] = Carbon::now();
            $transaction['updated_at'] = Carbon::now();
            return $transaction;
        }, $data['transactions']);

        return Transaction::insert($transactions);
    }
}
