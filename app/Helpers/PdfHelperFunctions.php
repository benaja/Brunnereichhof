<?php

namespace App\Helpers;

use App\User;

class PdfHelperFunctions
{
    public static function openTransactionsTable(Pdf $pdf, User $user)
    {
        $transactions = $user->transactions;

        if (count($transactions) > 0) {
            $lines = [];
            foreach ($transactions as $transaction) {
                array_push($lines, [$transaction->amount, $transaction->date->format('d.m.Y'), $transaction->name, $transaction->comment]);
            }
            if (count($lines) > 1) {
                $openAmount = $transactions->sum('amount');
                array_push($lines, ['Total: '.$openAmount]);
            }
            $pdf->SetX(10);
            $pdf->documentTitle('Unverbuchte Vorschüsse');
            $pdf->table(['Menge', 'Datum', 'Vorschuss Typ', 'Bemerkung'], $lines, [1, 1, 1, 2], ['lastLineBold' => count($lines) > 1]);
        }
    }

    public static function openTransactionsQuery($query)
    {
        return $query->where('entered', 0)
            ->join('transaction_types', 'transaction_types.id', '=', 'transactions.transaction_type_id')
            ->orderBy('transactions.date');
    }
}
