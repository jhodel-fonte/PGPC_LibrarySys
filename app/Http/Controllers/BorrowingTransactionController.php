<?php

namespace App\Http\Controllers;

use App\Models\BorrowingTransaction;
use App\Http\Requests\StoreBorrowingTransactionRequest;
use App\Http\Requests\UpdateBorrowingTransactionRequest;

class BorrowingTransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreBorrowingTransactionRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(BorrowingTransaction $borrowingTransaction)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(BorrowingTransaction $borrowingTransaction)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateBorrowingTransactionRequest $request, BorrowingTransaction $borrowingTransaction)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(BorrowingTransaction $borrowingTransaction)
    {
        //
    }
}
