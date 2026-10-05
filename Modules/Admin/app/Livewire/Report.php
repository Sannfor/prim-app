<?php

namespace Modules\Admin\Livewire;

use App\Enums\TransactionStatus;
use App\Models\Service;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Carbon;
use Illuminate\Support\Number;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * Laporan pengelola.
 *
 * Mengikuti frame "ADMIN - Laporan": kartu insight, diagram pendapatan
 * bulanan, komposisi status pesanan, produk terlaris, dan pesanan terbaru.
 */
#[Layout('layouts::admin')]
#[Title('Laporan Admin')]
class Report extends Component
{
    public function render(): View
    {
        $successful = [TransactionStatus::Paid->value, TransactionStatus::Accepted->value];

        /*
         | Pendapatan dihitung dari kolom paid_at, yaitu saat pembayaran benar-benar
         | diterima. Dasar ini dipakai bersama oleh halaman Dashboard dan halaman
         | Laporan agar kedua halaman menampilkan angka yang sama.
         */
        $awalBulanIni = now()->startOfMonth();
        $awalBulanLalu = now()->subMonthNoOverflow()->startOfMonth();

        $thisMonth = (int) Transaction::query()
            ->whereIn('status', $successful)
            ->where('paid_at', '>=', $awalBulanIni)
            ->sum('amount');

        $lastMonth = (int) Transaction::query()
            ->whereIn('status', $successful)
            ->whereBetween('paid_at', [$awalBulanLalu, $awalBulanIni])
            ->sum('amount');

        $revenueGrowth = $lastMonth > 0
            ? round((($thisMonth - $lastMonth) / $lastMonth) * 100, 1)
            : null;

        $newUsersThisMonth = User::query()
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        $newUsersLastMonth = User::query()
            ->whereMonth('created_at', now()->subMonthNoOverflow()->month)
            ->whereYear('created_at', now()->subMonthNoOverflow()->year)
            ->count();

        $userGrowth = $newUsersLastMonth > 0
            ? round((($newUsersThisMonth - $newUsersLastMonth) / $newUsersLastMonth) * 100, 1)
            : null;

        $cancelledThisMonth = Transaction::query()
            ->where('status', TransactionStatus::Cancelled->value)
            ->whereMonth('created_at', now()->month)
            ->count();

        $cancelledLastMonth = Transaction::query()
            ->where('status', TransactionStatus::Cancelled->value)
            ->whereMonth('created_at', now()->subMonthNoOverflow()->month)
            ->count();

        $cancelDelta = $cancelledLastMonth > 0
            ? round((($cancelledThisMonth - $cancelledLastMonth) / $cancelledLastMonth) * 100, 1)
            : null;

        // Diagram pendapatan enam bulan terakhir.
        $monthly = [];

        for ($offset = 5; $offset >= 0; $offset--) {
            $month = Carbon::now()->subMonthsNoOverflow($offset);

            $monthly[] = [
                'label' => $month->locale('id')->translatedFormat('M'),
                'value' => (int) Transaction::query()
                    ->whereIn('status', $successful)
                    ->whereMonth('created_at', $month->month)
                    ->whereYear('created_at', $month->year)
                    ->sum('amount'),
            ];
        }

        $peak = max(array_column($monthly, 'value')) ?: 1;

        // Komposisi status pesanan sebagai persentase.
        $statusOrder = [
            TransactionStatus::Paid,
            TransactionStatus::Processed,
            TransactionStatus::WaitingProcess,
            TransactionStatus::Pending,
            TransactionStatus::Cancelled,
        ];

        $totalForShare = max(1, Transaction::query()->count());

        $composition = [];

        foreach ($statusOrder as $status) {
            $count = Transaction::query()->where('status', $status->value)->count();

            $composition[] = [
                'label' => $status->label(),
                'count' => $count,
                'percentage' => round(($count / $totalForShare) * 100, 1),
                'tone' => $status->tone(),
            ];
        }

        // Diagram lingkaran memakai nilai status utama saja agar mudah dibaca.
        $pie = [];
        $sudutAwal = 0.0;

        foreach (array_slice($composition, 0, 4) as $row) {
            if ($row['count'] <= 0) {
                continue;
            }

            $bagian = $row['percentage'];

            $pie[] = [
                'label' => $row['label'],
                'count' => $row['count'],
                'percentage' => $bagian,
                'dari' => $sudutAwal,
                'sampai' => $sudutAwal + $bagian,
                'tone' => $row['tone'],
            ];

            $sudutAwal += $bagian;
        }

        // Statistik bulan berjalan.
        //
        // Jumlah pesanan berhasil memakai dasar waktu yang sama dengan nilai
        // pendapatan ($thisMonth), yaitu kolom paid_at, agar rata-rata nilai
        // pesanan konsisten dengan nilai pendapatan yang ditampilkan.
        $paidThisMonth = Transaction::query()
            ->whereIn('status', $successful)
            ->where('paid_at', '>=', $awalBulanIni)
            ->count();

        $averageOrderThisMonth = $paidThisMonth > 0 ? (int) round($thisMonth / $paidThisMonth) : 0;

        $cancelledAll = Transaction::query()->where('status', TransactionStatus::Cancelled->value)->count();
        $successRate = $totalForShare > 0
            ? round((Transaction::query()->whereIn('status', $successful)->count() / $totalForShare) * 100, 1)
            : 0.0;

        // Hari tersibuk pada 30 hari terakhir.
        $busiest = Transaction::query()
            ->where('created_at', '>=', now()->subDays(30))
            ->get(['created_at'])
            ->groupBy(fn ($row) => $row->created_at->toDateString())
            ->map->count()
            ->sortDesc();

        $busiestDay = $busiest->keys()->first();
        $busiestCount = $busiest->first() ?? 0;

        $statistics = [
            [
                'label' => 'Rata-rata Nilai Pesanan',
                'value' => 'Rp'.Number::format($averageOrderThisMonth, locale: 'id'),
                'hint' => 'Dari '.$paidThisMonth.' pesanan berhasil bulan ini',
                'icon' => 'calculator',
            ],
            [
                'label' => 'Tingkat Keberhasilan',
                'value' => $successRate.'%',
                'hint' => 'Perbandingan pesanan berhasil terhadap seluruh pesanan',
                'icon' => 'check-badge',
            ],
            [
                'label' => 'Total Pembatalan',
                'value' => Number::format($cancelledAll, locale: 'id'),
                'hint' => 'Sepanjang waktu, '.$cancelledThisMonth.' di antaranya bulan ini',
                'icon' => 'x-circle',
            ],
            [
                'label' => 'Hari Tersibuk',
                'value' => $busiestDay ? Carbon::parse($busiestDay)->locale('id')->translatedFormat('d M') : '—',
                'hint' => $busiestCount.' pesanan dalam 30 hari terakhir',
                'icon' => 'calendar-days',
            ],
        ];

        $topServices = Service::query()
            ->withCount(['transactions as total_transactions'])
            ->withSum(['transactions as revenue' => fn ($q) => $q->whereIn('status', $successful)], 'amount')
            ->orderByDesc('total_transactions')
            ->take(5)
            ->get();

        $topServicePeak = max(1, (int) $topServices->max('total_transactions'));

        return view('admin::livewire.report', [
            'cards' => [
                [
                    'label' => 'Total Pesanan',
                    'value' => Number::format(Transaction::query()->count(), locale: 'id'),
                    'hint' => 'Seluruh waktu',
                ],
                [
                    'label' => 'Pendapatan Bulan Ini',
                    'value' => 'Rp'.Number::format($thisMonth, locale: 'id'),
                    'hint' => $revenueGrowth === null
                        ? 'Belum ada pembanding'
                        : (($revenueGrowth >= 0 ? '+' : '').$revenueGrowth.'% dibanding bulan lalu'),
                ],
                [
                    'label' => 'Pengguna Baru',
                    'value' => Number::format($newUsersThisMonth, locale: 'id'),
                    'hint' => $userGrowth === null
                        ? 'Belum ada pembanding'
                        : (($userGrowth >= 0 ? '+' : '').$userGrowth.'% dibanding bulan lalu'),
                ],
                [
                    'label' => 'Pembatalan Bulan Ini',
                    'value' => Number::format($cancelledThisMonth, locale: 'id'),
                    'hint' => $cancelDelta === null
                        ? 'Belum ada pembanding'
                        : (($cancelDelta >= 0 ? '+' : '').$cancelDelta.'% dibanding bulan lalu'),
                ],
            ],
            'insights' => [
                [
                    'label' => 'Pendapatan',
                    'value' => $revenueGrowth,
                    'text' => 'Pendapatan naik',
                    'positive' => ($revenueGrowth ?? 0) >= 0,
                ],
                [
                    'label' => 'Pengguna baru',
                    'value' => $userGrowth,
                    'text' => 'Pengguna baru naik',
                    'positive' => ($userGrowth ?? 0) >= 0,
                ],
                [
                    'label' => 'Pembatalan',
                    'value' => $cancelDelta,
                    'text' => $cancelDelta === null
                        ? $cancelledThisMonth.' pembatalan bulan ini'
                        : 'Pembatalan turun',
                    'positive' => ($cancelDelta ?? 0) <= 0,
                ],
            ],
            'monthly' => $monthly,
            'monthlyPeak' => $peak,
            'composition' => $composition,
            'pie' => $pie,
            'statistics' => $statistics,
            'topServices' => $topServices,
            'topServicePeak' => $topServicePeak,
            'recent' => Transaction::query()->with(['user', 'plan.service'])->latest()->take(8)->get(),
        ]);
    }
}
