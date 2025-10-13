<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Payslip;
use App\Models\WithholdingStatement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();                 // ← 標準Auth
        $emp  = $user->employee_number;       // ← 社員番号

        $type = $request->query('type');      // salary|bonus|null

        $query = Payslip::where('employee_number', $emp)
                        ->orderByDesc('updated_at');

        if (in_array($type, ['salary','bonus'], true)) {
            $query->where('pay_type', $type);
        }

        $payslips = $query->paginate(3)->withQueryString();

        $years = WithholdingStatement::where('employee_number', $emp)
                    ->select('year')->distinct()->orderByDesc('year')->pluck('year');

        return view('user.home', [
            'payslips'    => $payslips,
            'years'       => $years,
            'type'        => $type,
            'currentUser' => $user,
        ]);
    }

    public function showPayslip(Payslip $payslip)
    {
        $this->authorizeEmployee($payslip->employee_number);
        return view('user.payslip_show', compact('payslip'));
    }

    public function downloadPayslipPdf(Payslip $payslip)
    {
        $this->authorizeEmployee($payslip->employee_number);

        $currentUser = Auth::user();

        $pdf = Pdf::loadView('pdf.payslip', [
            'payslip'     => $payslip,
            'currentUser' => $currentUser,
        ])->setPaper('A4');

        return $pdf->download("payslip_{$payslip->year}_{$payslip->month}_{$payslip->pay_type}.pdf");
    }

    public function downloadWithholdingPdf(Request $request)
    {
        $user = Auth::user();
        $emp  = $user->employee_number;

        $year = (int) $request->query('year');

        $ws = WithholdingStatement::where('employee_number', $emp)
                ->where('year', $year)
                ->firstOrFail();

        $pdf = Pdf::loadView('pdf.withholding', [
            'ws'          => $ws,
            'currentUser' => $user,
        ])->setPaper('A4');

        return $pdf->download("withholding_{$year}.pdf");
    }

    private function authorizeEmployee(string $ownerEmployeeNumber): void
    {
        $current = Auth::user();
        abort_unless($current && $ownerEmployeeNumber === $current->employee_number, 403);
    }
}
