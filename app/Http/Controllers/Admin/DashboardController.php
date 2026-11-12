<?php

namespace App\Http\Controllers\Admin;

use App\Enums\InquiryStatus;
use App\Http\Controllers\Controller;
use App\Models\Inquiry;
use App\Models\Project;
use App\Models\Service;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Days covered by the inquiries chart.
     */
    private const CHART_DAYS = 14;

    public function __invoke(): View
    {
        $byStatus = Inquiry::query()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $since = today()->subDays(self::CHART_DAYS - 1);
        $perDay = Inquiry::query()
            ->where('created_at', '>=', $since)
            ->pluck('created_at')
            ->countBy(fn (Carbon $createdAt): string => $createdAt->toDateString());

        return view('admin.dashboard', [
            'stats' => [
                'total' => $byStatus->sum(),
                'new' => (int) $byStatus->get(InquiryStatus::New->value, 0),
                'in_progress' => (int) $byStatus->get(InquiryStatus::InProgress->value, 0),
                'replied' => (int) $byStatus->get(InquiryStatus::Replied->value, 0),
                'published_projects' => Project::query()->where('is_published', true)->count(),
                'active_services' => Service::query()->where('is_active', true)->count(),
            ],
            'statusCounts' => collect(InquiryStatus::cases())->mapWithKeys(fn (InquiryStatus $status): array => [
                $status->value => (int) $byStatus->get($status->value, 0),
            ]),
            'chart' => collect(range(0, self::CHART_DAYS - 1))->mapWithKeys(function (int $offset) use ($since, $perDay): array {
                $date = $since->copy()->addDays($offset);

                return [$date->toDateString() => ['label' => $date->format('M j'), 'total' => $perDay->get($date->toDateString(), 0)]];
            }),
            'recent' => Inquiry::query()->with('service')->latest()->limit(6)->get(),
        ]);
    }
}
