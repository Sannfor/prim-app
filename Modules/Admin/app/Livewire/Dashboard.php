<?php

namespace Modules\Admin\Livewire;

use App\Enums\SubscriptionStatus;
use App\Enums\TransactionStatus;
use App\Models\Plan;
use App\Models\Provider;
use App\Models\Service;
use App\Models\Subscription;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * Dashboard pengelola PRIM dengan metrik yang dihitung langsung dari basis data.
 *
 * Menggantikan angka statis pada scaffold awal sehingga angka yang tampil
 * selalu mencerminkan kondisi data yang sebenarnya.
 */
#[Layout('layouts::app')]
#[Title('Dashboard Admin')]
class Dashboard extends Component
{
    public function render(): View
    {
        $monthStart = now()->startOfMonth();
        $lastMonthStart = $monthStart->copy()->subMonth();

        $revenueThisMonth = Transaction::query()
            ->where('status', TransactionStatus::Paid->value)
            ->where('paid_at', '>=', $monthStart)
            ->sum('amount');

        $revenueLastMonth = Transaction::query()
            ->where('status', TransactionStatus::Paid->value)
            ->whereBetween('paid_at', [$lastMonthStart, $monthStart])
            ->sum('amount');

        return view('admin::livewire.dashboard', [
            'metrics' => [
                'services' => [
                    'label' => 'Layanan Terdaftar',
                    'value' => Service::query()->count(),
                    'hint' => Service::query()->where('is_active', true)->count().' layanan aktif',
                    'icon' => 'squares-2x2',
                ],
                'plans' => [
                    'label' => 'Paket Langganan',
                    'value' => Plan::query()->count(),
                    'hint' => Plan::query()->where('is_active', true)->count().' paket aktif',
                    'icon' => 'list-bullet',
                ],
                'providers' => [
                    'label' => 'Penyedia Layanan',
                    'value' => Provider::query()->count(),
                    'hint' => 'Seluruh penyedia terdaftar',
                    'icon' => 'building-office',
                ],
                'users' => [
                    'label' => 'Pengguna Terdaftar',
                    'value' => User::query()->count(),
                    'hint' => User::query()->where('role', 'user')->count().' pelanggan',
                    'icon' => 'users',
                ],
                'active_subscriptions' => [
                    'label' => 'Langganan Aktif',
                    'value' => Subscription::query()->active()->count(),
                    'hint' => Subscription::query()
                        ->where('status', SubscriptionStatus::Active->value)
                        ->whereBetween('ends_at', [now(), now()->addDays(7)])
                        ->count().' akan berakhir',
                    'icon' => 'arrow-path-rounded-square',
                ],
                'pending_transactions' => [
                    'label' => 'Transaksi Menunggu',
                    'value' => Transaction::query()->awaitingPayment()->count(),
                    'hint' => Transaction::query()->count().' total transaksi',
                    'icon' => 'clock',
                ],
            ],
            'revenue' => [
                'this_month' => (int) $revenueThisMonth,
                'last_month' => (int) $revenueLastMonth,
                'growth' => $this->growthPercentage((int) $revenueLastMonth, (int) $revenueThisMonth),
            ],
            'statusBreakdown' => $this->statusBreakdown(),
            'dailyTransactions' => $this->dailyTransactions(),
            'recentTransactions' => Transaction::query()
                ->with(['user', 'plan.service'])
                ->latest()
                ->limit(6)
                ->get(),
            'expiringSubscriptions' => Subscription::query()
                ->active()
                ->with(['user', 'plan.service'])
                ->where('ends_at', '<=', now()->addDays(7))
                ->orderBy('ends_at')
                ->limit(5)
                ->get(),
            'topServices' => $this->topServices(),
        ]);
    }

    /**
     * Persentase pertumbuhan pendapatan dibanding bulan lalu.
     */
    private function growthPercentage(int $previous, int $current): ?float
    {
        if ($previous === 0) {
            return $current > 0 ? 100.0 : null;
        }

        return round((($current - $previous) / $previous) * 100, 1);
    }

    /**
     * Jumlah transaksi per status untuk diagram ringkas.
     *
     * @return list<array{label: string, value: int, color: string, percentage: float}>
     */
    private function statusBreakdown(): array
    {
        $total = Transaction::query()->count();

        $rows = [];

        foreach (TransactionStatus::cases() as $status) {
            $count = Transaction::query()->where('status', $status->value)->count();

            $rows[] = [
                'label' => $status->label(),
                'value' => $count,
                'color' => $status->badgeColor(),
                'percentage' => $total === 0 ? 0.0 : round(($count / $total) * 100, 1),
            ];
        }

        return $rows;
    }

    /**
     * Jumlah transaksi per hari selama 14 hari terakhir.
     *
     * @return list<array{date: Carbon, label: string, count: int, height: int}>
     */
    private function dailyTransactions(): array
    {
        $start = now()->subDays(13)->startOfDay();

        $counts = Transaction::query()
            ->where('created_at', '>=', $start)
            ->get(['created_at'])
            ->groupBy(fn (Transaction $transaction) => $transaction->created_at->toDateString())
            ->map->count();

        $max = max(1, $counts->max() ?? 1);

        $series = [];

        for ($i = 0; $i < 14; $i++) {
            $date = $start->copy()->addDays($i);
            $count = $counts->get($date->toDateString(), 0);

            $series[] = [
                'date' => $date,
                'label' => $date->translatedFormat('d M'),
                'count' => $count,
                'height' => (int) max(4, round(($count / $max) * 100)),
            ];
        }

        return $series;
    }

    /**
     * Layanan dengan pendapatan tertinggi dari transaksi lunas.
     *
     * @return Collection<int, object>
     */
    private function topServices(): Collection
    {
        return Transaction::query()
            ->where('transactions.status', TransactionStatus::Paid->value)
            ->join('plans', 'plans.id', '=', 'transactions.plan_id')
            ->join('services', 'services.id', '=', 'plans.service_id')
            ->groupBy('services.id', 'services.name')
            ->selectRaw('services.name as name, count(*) as total_transactions, sum(transactions.amount) as revenue')
            ->orderByDesc('revenue')
            ->limit(5)
            ->get();
    }
}
