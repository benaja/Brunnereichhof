<?php

namespace App\Http\Controllers;

use App\Employee;
use App\Http\Requests\TransactionBulkRequest;
use App\Http\Requests\TransactionRequest;
use App\Http\Resources\TransactionResource;
use App\Transaction;
use App\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
        $transactions = Transaction::with(['user', 'type'])
            ->select('transactions.*')
            ->leftJoin('user', 'user.id', '=', 'transactions.user_id')
            ->leftJoin('transaction_types', 'transaction_types.id', '=', 'transactions.transaction_type_id')
            ->when(
                $request->has('sort_by') &&
                Str::is('user.lastname', $request->get('sort_by')),
                function (Builder $query) use ($dir) {
                    return $query->orderByRaw("user.lastname $dir");
                }
            )
            ->when(
                $request->has('sort_by') &&
                ! Str::is('user.lastname', $request->get('sort_by')) &&
                $request->get('sort_by') !== 'type.name',
                function (Builder $query) use ($request, $dir) {
                    return $query->orderBy($request->get('sort_by'), $dir);
                }
            )
            ->when($request->get('sort_by') === 'type.name', function (Builder $query) use ($dir) {
                return $query->orderByRaw("transaction_types.name $dir");
            })
            ->when(! $request->has('sort_by'), function (Builder $query) {
                return $query->orderBy('created_at', 'desc');
            })
            ->when($request->has('search'), function (Builder $query) use ($request) {
                $query->where(function (Builder $query) use ($request) {
                    $search = $request->get('search');
                    $query->where('user.lastname', 'like', "%$search%")
                        ->orWhere('user.firstname', 'like', "%$search%")
                        ->orWhere(DB::raw("concat(user.lastname, ' ', user.firstname)"), 'like', "%$search%")
                        ->orWhere('transactions.comment', 'like', "%$search%")
                        ->orWhere('transaction_types.name', 'like', "%$search%")
                        ->orWhere('transactions.amount', 'like', "%$search%");
                });
            });

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

    public function bulkCreate(TransactionBulkRequest $request)
    {
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

    public function getByUser(User $user)
    {
        auth()->user()->authorize(['superadmin'], ['transaction_read']);

        $transactions = $user->transactions()->with('type')
            ->orderBy('created_at', 'desc');

        if (request()->get('per_page') > 0) {
            $transactions = $transactions->paginate(request()->get('per_page'));
        } else {
            $transactions = $transactions->get();
        }

        return TransactionResource::collection($transactions);
    }

    public function saldo(User $user)
    {
        auth()->user()->authorize(['superadmin'], ['transaction_read']);

        return [
            'data' => $user->transactions()->where('entered', false)->sum('amount'),
        ];
    }

    public function stats()
    {
        auth()->user()->authorize(['superadmin'], ['transaction_read']);

        return [
            'data' => [
                'positive' => Transaction::where('entered', false)->where('amount', '>', 0)->sum('amount'),
                'negative' => Transaction::where('entered', false)->where('amount', '<', 0)->sum('amount'),
                'total' => Transaction::where('entered', false)->sum('amount'),
            ],
        ];
    }
}
