<?php

namespace App\Http\Controllers;

use App\Employee;
use App\Http\Requests\TransactionBulkRequest;
use App\Http\Requests\TransactionRequest;
use App\Http\Resources\TransactionResource;
use App\Transaction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TransactionsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $dir = $request->get('sort_desc') === 'true' ? 'desc' : 'asc';
        $transactions = Transaction::with(['employee', 'type'])
            ->when(
                $request->has('sort_by') &&
                Str::is('employee.lastname', $request->get('sort_by')),
                function (Builder $query) use ($dir) {
                    return $query->select('transactions.*')
                        ->leftJoin('employee', 'employee.id', '=', 'transactions.employee_id')
                        ->leftJoin('user', 'user.id', '=', 'employee.user_id')
                        ->orderByRaw("user.lastname $dir");
                }
            )
            ->when(
                $request->has('sort_by') &&
                !Str::is('employee.lastname', $request->get('sort_by')) && 
                $request->get('sort_by') !== 'type.name',
                function (Builder $query) use ($request, $dir) {
                    return $query->orderBy($request->get('sort_by'), $dir);
                }
            )
            ->when($request->get('sort_by') === 'type.name', function (Builder $query) use ($dir){
                return $query->select('transactions.*')
                    ->leftJoin('transaction_types', 'transaction_types.id', '=', 'transactions.transaction_type_id')
                    ->orderByRaw("transaction_types.name $dir");
            })
            ->when(!$request->has('sort_by'), function (Builder $query) {
                return $query->orderBy('created_at', 'desc');
            });

        // if ($request->get('sort_by')) {
        //     $transactions->oderBy()
        // }
        //     ->orderBy('created_at', 'desc');

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
