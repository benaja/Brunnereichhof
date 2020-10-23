<?php

namespace App\Http\Controllers;

use App\Employee;
use App\Http\Requests\TransactionBulkRequest;
use App\Http\Requests\TransactionRequest;
use App\Http\Resources\TransactionResource;
use App\Transaction;
use Illuminate\Http\Request;

class TransactionsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $transactions = Transaction::with(['employee', 'type'])
            ->orderBy('created_at', 'desc');

        if (request()->get('per_page') > 0) {
            $transactions = $transactions->paginate(request()->get('per_page'));
        } else {
            $transactions = $transactions->get();
        }
        return TransactionResource::collection($transactions);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(TransactionRequest $request)
    {
        return TransactionResource::make($request->store());
    }

    public function bulkCreate(TransactionBulkRequest $request) {
        return $request->store();
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(TransactionRequest $request, Transaction $transaction)
    {
        return TransactionResource::make($request->update($transaction));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Transaction $transaction)
    {
        auth()->user()->authorize(['superadmin'], ['transaction_write']);

        $transaction->delete();
    }

    public function getByEmployee(Employee $employee) {
        auth()->user()->authorize(['superadmin'], ['transaction_read']);

        $transactions = $employee->transactions()->with('type')
            ->orderBy('created_at', 'desc');

        if (request()->get('per_page') > 0) {
            $transactions = $transactions->paginate(request()->get('per_page'));
        } else {
            $transactions = $transactions->get();
        }
        return TransactionResource::collection($transactions);
    }

    public function saldo (Employee $employee) {
        auth()->user()->authorize(['superadmin'], ['transaction_read']);
        
        return [
            'data' => $employee->transactions()->where('entered', false)->sum('amount')
        ];
    }
}
