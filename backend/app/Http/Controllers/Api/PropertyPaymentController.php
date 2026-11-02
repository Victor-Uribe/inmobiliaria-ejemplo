<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;


use App\Models\Property;
use App\Models\PropertyBuyer;
use App\Models\PropertyPayment;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PropertyPaymentController extends Controller
{
    //
    public function index(Property $property)
    {
        $buyer = $property->buyer()->first();

        if (!$buyer) {
            return response()->json([
                'buyer' => null,
                'payments' => [],
                'summary' => null,
                'periods' => [],
            ]);
        }

        $payments = $buyer->payments()
            ->orderByDesc('paid_at')
            ->orderByDesc('id')
            ->get();

        return response()->json([
            'buyer' => $buyer,
            'payments' => $payments,
            'summary' => $this->summary($buyer),
            'periods' => $this->periods($buyer),
        ]);
    }

    public function store(Request $request, Property $property)
    {
        $buyer = $property->buyer()->firstOrFail();

        $data = $request->validate([
            'paid_at' => ['required', 'date'],
            'amount' => ['required', 'numeric', 'min:1'],

            // El front puede mandar applied_period o month.
            // month puede venir como 2026-07.
            'applied_period' => ['nullable', 'date'],
            'month' => ['nullable', 'string', 'max:7'],

            // monthly, down_payment, adjustment
            'payment_type' => ['nullable', 'string', 'in:monthly,down_payment,adjustment'],

            'payment_method' => ['nullable', 'string', 'max:100'],
            'reference' => ['nullable', 'string', 'max:150'],
            'folio' => ['nullable', 'string', 'max:150'],
            'notes' => ['nullable', 'string'],
        ]);

        $data['payment_type'] = $data['payment_type'] ?? 'monthly';

        return DB::transaction(function () use ($data, $buyer) {
            if ($data['payment_type'] === 'monthly') {
                $periodInput = $data['applied_period'] ?? null;

                if (!$periodInput && !empty($data['month'])) {
                    $periodInput = $data['month'] . '-01';
                }

                $appliedPeriod = $periodInput
                    ? Carbon::parse($periodInput)->startOfMonth()
                    : $this->nextPendingPeriod($buyer);

                $data['applied_period'] = $appliedPeriod->toDateString();

                $data['applied_due_date'] = $appliedPeriod->copy()->day(
                    min((int) $buyer->payment_due_day, $appliedPeriod->daysInMonth)
                )->toDateString();
            } else {
                $data['applied_period'] = null;
                $data['applied_due_date'] = null;
            }

            unset($data['month']);

            $payment = $buyer->payments()->create([
                ...$data,
                'status' => 'applied',
                'created_by' => auth()->id(),
            ]);

            return response()->json([
                'message' => 'Pago registrado correctamente.',
                'payment' => $payment,
                'summary' => $this->summary($buyer->fresh()),
                'periods' => $this->periods($buyer->fresh()),
            ], 201);
        });
    }

    public function destroy(PropertyPayment $payment)
    {
        $buyer = $payment->buyer;

        $payment->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'cancelled_by' => auth()->id(),
        ]);

        return response()->json([
            'message' => 'Pago cancelado correctamente.',
            'summary' => $this->summary($buyer->fresh()),
            'periods' => $this->periods($buyer->fresh()),
        ]);
    }

    private function summary(PropertyBuyer $buyer): array
    {
        $monthlyPayment = (float) $buyer->monthly_payment_amount;

        $monthlyPaid = (float) $buyer->payments()
            ->where('status', 'applied')
            ->where('payment_type', 'monthly')
            ->sum('amount');

        $totalPaid = (float) $buyer->payments()
            ->where('status', 'applied')
            ->sum('amount');

        $periods = $this->periods($buyer);

        $overdueMonths = collect($periods)
            ->where('is_overdue', true)
            ->count();

        $color = 'green';

        if ($overdueMonths >= 1 && $overdueMonths <= 2) {
            $color = 'yellow';
        }

        if ($overdueMonths >= 3) {
            $color = 'red';
        }

        return [
            'sale_price' => (float) $buyer->sale_price,
            'discount_amount' => (float) $buyer->discount_amount,
            'down_payment_amount' => (float) $buyer->down_payment_amount,
            'financed_amount' => (float) $buyer->financed_amount,

            'monthly_payment_amount' => $monthlyPayment,
            'monthly_paid' => $monthlyPaid,
            'total_paid' => $totalPaid,

            'balance' => max(0, (float) $buyer->sale_price - (float) $buyer->discount_amount - $totalPaid),
            'financed_balance' => max(0, (float) $buyer->financed_amount - $monthlyPaid),

            'overdue_months' => $overdueMonths,
            'color' => $color,
        ];
    }

    private function periods(PropertyBuyer $buyer): array
    {
        if (!$buyer->first_payment_period || (float) $buyer->monthly_payment_amount <= 0) {
            return [];
        }

        $monthlyPayment = (float) $buyer->monthly_payment_amount;
        $financedAmount = (float) $buyer->financed_amount;

        if ($financedAmount <= 0) {
            return [];
        }

        $totalMonths = (int) ceil($financedAmount / $monthlyPayment);

        $paidByPeriod = $buyer->payments()
            ->where('status', 'applied')
            ->where('payment_type', 'monthly')
            ->whereNotNull('applied_period')
            ->selectRaw('applied_period, SUM(amount) as total')
            ->groupBy('applied_period')
            ->pluck('total', 'applied_period');

        $today = now()->startOfDay();
        $start = Carbon::parse($buyer->first_payment_period)->startOfMonth();

        $periods = [];

        for ($i = 0; $i < $totalMonths; $i++) {
            $period = $start->copy()->addMonths($i)->startOfMonth();

            $dueDate = $period->copy()->day(
                min((int) $buyer->payment_due_day, $period->daysInMonth)
            );

            $remainingBeforeThisMonth = $financedAmount - ($monthlyPayment * $i);
            $amountDue = min($monthlyPayment, $remainingBeforeThisMonth);

            $periodKey = $period->toDateString();
            $amountPaid = (float) ($paidByPeriod[$periodKey] ?? 0);

            $balance = max(0, $amountDue - $amountPaid);

            $status = 'pending';

            if ($amountPaid >= $amountDue) {
                $status = 'paid';
            } elseif ($amountPaid > 0) {
                $status = 'partial';
            }

            $isOverdue = $balance > 0 && $today->gt($dueDate);

            $periods[] = [
                'period' => $periodKey,
                'period_label' => $period->translatedFormat('F Y'),
                'due_date' => $dueDate->toDateString(),
                'amount_due' => round($amountDue, 2),
                'amount_paid' => round($amountPaid, 2),
                'balance' => round($balance, 2),
                'status' => $status,
                'is_overdue' => $isOverdue,
            ];
        }

        return $periods;
    }

    private function nextPendingPeriod(PropertyBuyer $buyer): Carbon
    {
        $periods = $this->periods($buyer);

        foreach ($periods as $period) {
            if ($period['status'] !== 'paid') {
                return Carbon::parse($period['period'])->startOfMonth();
            }
        }

        return Carbon::parse($buyer->first_payment_period)
            ->startOfMonth()
            ->addMonths(count($periods));
    }
}
