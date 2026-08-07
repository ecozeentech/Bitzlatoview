<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\InvestmentReward;
use App\Models\MiningReward;
use App\Models\SwapTransaction;
use App\Models\TaxReport;
use App\Models\Trade;
use App\Services\TransactionalMailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaxController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $year = (int) $request->query('year', now()->year);

        $miningIncome = MiningReward::whereHas('contract', fn ($q) => $q->where('user_id', $user->id))
            ->whereYear('credited_at', $year)->sum('amount');

        $investmentIncome = InvestmentReward::whereHas('subscription', fn ($q) => $q->where('user_id', $user->id))
            ->whereYear('credited_at', $year)->sum('amount');

        $tradingFees = Trade::whereHas('order', fn ($q) => $q->where('user_id', $user->id))
            ->whereYear('created_at', $year)->sum('fee');

        $swapFees = SwapTransaction::where('user_id', $user->id)->whereYear('created_at', $year)->sum('fee');

        $reports = TaxReport::where('user_id', $user->id)->orderByDesc('year')->get();

        return view('app.tax.index', [
            'year' => $year,
            'years' => range(now()->year, now()->year - 4),
            'miningIncome' => $miningIncome,
            'investmentIncome' => $investmentIncome,
            'tradingFees' => $tradingFees,
            'swapFees' => $swapFees,
            'reports' => $reports,
        ]);
    }

    public function generate(Request $request, TransactionalMailService $mailer)
    {
        $user = Auth::user();

        $data = $request->validate([
            'year' => ['required', 'integer', 'min:2020', 'max:'.now()->year],
            'country' => ['nullable', 'string', 'max:100'],
            'cost_basis_method' => ['required', 'in:fifo,lifo,hifo,average'],
        ]);

        $income = MiningReward::whereHas('contract', fn ($q) => $q->where('user_id', $user->id))->whereYear('credited_at', $data['year'])->sum('amount')
            + InvestmentReward::whereHas('subscription', fn ($q) => $q->where('user_id', $user->id))->whereYear('credited_at', $data['year'])->sum('amount');

        $fees = Trade::whereHas('order', fn ($q) => $q->where('user_id', $user->id))->whereYear('created_at', $data['year'])->sum('fee')
            + SwapTransaction::where('user_id', $user->id)->whereYear('created_at', $data['year'])->sum('fee');

        $report = TaxReport::create([
            'user_id' => $user->id,
            'year' => $data['year'],
            'country' => $data['country'] ?? $user->country,
            'cost_basis_method' => $data['cost_basis_method'],
            'realized_gain' => 0,
            'unrealized_gain' => 0,
            'income_total' => $income,
            'fees_paid' => $fees,
            'generated_at' => now(),
        ]);

        $mailer->send($user, 'tax_report_ready', ['name' => $user->name, 'year' => (string) $data['year']]);

        return back()->with('success', "Tax report generated for {$data['year']}.");
    }

    public function export(TaxReport $report)
    {
        abort_unless($report->user_id === Auth::id(), 403);

        $csv = "Year,Country,Cost Basis Method,Realized Gain,Unrealized Gain,Income,Fees Paid\n";
        $csv .= "{$report->year},{$report->country},{$report->cost_basis_method},{$report->realized_gain},{$report->unrealized_gain},{$report->income_total},{$report->fees_paid}\n";

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=bitzlatoview-tax-report-{$report->year}.csv",
        ]);
    }
}
