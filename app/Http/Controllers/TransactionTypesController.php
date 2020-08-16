<?php

namespace App\Http\Controllers;

use App\Http\Requests\TransactionTypeRequest;
use App\Http\Resources\TransactionTypeResource;
use App\TransactionType;
use Illuminate\Http\Request;

class TransactionTypesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return TransactionTypeResource::collection(TransactionType::withTrashed()->get());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(TransactionTypeRequest $request)
    {
        return TransactionTypeResource::make($request->store());
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(TransactionTypeRequest $request, TransactionType $transactionType)
    {
        return TransactionTypeResource::make($request->update($transactionType));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(TransactionType $transactionType)
    {
        $transactionType->delete();
    }
}
