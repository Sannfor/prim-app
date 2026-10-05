<?php

namespace App\Livewire;

use App\Enums\TransactionStatus;
use App\Models\Transaction;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Carbon;
use Livewire\Component;

/**
 * Lonceng notifikasi pada kepala panel pengelola.
 *
 * Menampilkan jumlah dan daftar pesanan yang perlu ditangani, yaitu pesanan yang
 * masih menunggu pembayaran, sedang diproses, menunggu proses, atau perlu
 * ditindak lanjuti. Daftar dibatasi lima pesanan terbaru.
 */
class NotificationBell extends Component
{
    /**
     * Status pesanan yang dianggap perlu tindakan pengelola.
     *
     * @return list<string>
     */
    public static function actionStatuses(): array
    {
        return [
            TransactionStatus::Pending->value,
            TransactionStatus::Processed->value,
            TransactionStatus::WaitingProcess->value,
            TransactionStatus::FollowUp->value,
        ];
    }

    public function render(): View
    {
        $statuses = self::actionStatuses();

        return view('livewire.admin.notification-bell', [
            'count' => Transaction::query()->whereIn('status', $statuses)->count(),
            'items' => Transaction::query()
                ->with(['user', 'plan.service'])
                ->whereIn('status', $statuses)
                ->oldest()
                ->take(5)
                ->get(),
            'today' => Carbon::now()->locale('id')->translatedFormat('l, j F Y'),
        ]);
    }
}
