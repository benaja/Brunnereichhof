<?php

namespace App\Http\Controllers\Evaluation;

use App\Employee;
use App\Helpers\Pdf;
use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Http\Request;

class TransactionPdfController extends Controller
{
    private Pdf $pdf;

    public function saldo($userId)
    {
        auth()->user()->authorize(['superadmin'], ['evaluation_employee']);

        $this->pdf = new Pdf('P');

        if ($userId === 'all') {
            return $this->saldoOverviewForAllEmployees();
        } else {
            return $this->saldoForUser($userId);
        }
    }

    private function saldoOverviewForAllEmployees()
    {
        $this->pdf->documentTitle('Saldo Übersicht der aktiven Mitarbeiter');
        $this->pdf->newLine();
        $this->pdf->textToInsertOnPageBreak = 'Saldo Übersicht der aktiven Mitarbeiter';

        $employees = Employee::where('isActive', true)
            ->where('isGuest', false)
            ->with('transactions')
            ->get()
            ->sortBy('user.lastname', SORT_NATURAL | SORT_FLAG_CASE);

        $columns = $employees->map(function ($employee) {
            return [$employee->name(), $employee->transactions()->where('entered', false)->sum('amount')];
        });

        $this->pdf->table(['Mitarbeiter', 'Saldo in CHF'], $columns);

        return $this->pdf->export('Saldo Mitarbeiter.pdf');
    }

    private function saldoForUser($userId)
    {
        $user = User::with(['transactions' => function ($query) {
            $query->orderBy('date', 'desc');
            $query->with('type');
        }])->find($userId);

        $this->pdf->documentTitle("Saldo Übersicht für {$user->name}");
        $this->pdf->documentTitle("Saldo: {$user->transactions()->where('entered', false)->sum('amount')} CHF");
        $this->pdf->newLine();
        $this->pdf->textToInsertOnPageBreak = "Saldo Übersicht für {$user->name}";

        $columns = $user->transactions->map(function ($transaction) {
            return [
                $transaction->date->format('d.m.Y'),
                $transaction->type->name,
                $transaction->amount,
                $transaction->entered ? 'Ja' : 'Nein',
                $transaction->comment,
            ];
        });

        $this->pdf->table([
            'Datum',
            'Vorschuss Typ',
            'Menge in CHF',
            'Verbucht',
            'Kommentar',
        ], $columns, [0.6, 0.9, 0.7, 0.5, 1.5]);

        return $this->pdf->export("Saldo {$user->name}.pdf");
    }
}
