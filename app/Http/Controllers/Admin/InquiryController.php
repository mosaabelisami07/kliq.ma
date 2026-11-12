<?php

namespace App\Http\Controllers\Admin;

use App\Enums\InquiryStatus;
use App\Http\Controllers\Controller;
use App\Models\Inquiry;
use App\Models\Service;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class InquiryController extends Controller
{
    public function index(Request $request): View
    {
        return view('admin.inquiries.index', [
            'inquiries' => $this->filtered($request)->with('service')->paginate(15)->withQueryString(),
            'services' => Service::query()->orderBy('sort_order')->get(),
            'statuses' => InquiryStatus::cases(),
            'filters' => $request->only(['q', 'status', 'service', 'sort']),
        ]);
    }

    public function show(Inquiry $inquiry): View
    {
        if ($inquiry->isUnread()) {
            $inquiry->update(['read_at' => now()]);
        }

        $inquiry->load('service');

        return view('admin.inquiries.show', [
            'inquiry' => $inquiry,
            'statuses' => InquiryStatus::cases(),
        ]);
    }

    public function update(Request $request, Inquiry $inquiry): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::enum(InquiryStatus::class)],
            'notes' => ['nullable', 'string', 'max:10000'],
        ]);

        $inquiry->update($validated);

        return back()->with('success', 'Inquiry updated.');
    }

    public function destroy(Inquiry $inquiry): RedirectResponse
    {
        $inquiry->delete();

        return redirect()->route('admin.inquiries.index')->with('success', "Inquiry from {$inquiry->name} deleted.");
    }

    /**
     * Download the inquiries matching the current filters as CSV.
     */
    public function export(Request $request): StreamedResponse
    {
        $query = $this->filtered($request)->with('service');

        return response()->streamDownload(function () use ($query): void {
            $output = fopen('php://output', 'w');
            fwrite($output, "\xEF\xBB\xBF");
            fputcsv($output, ['ID', 'Date', 'Status', 'Name', 'Company', 'Email', 'Phone', 'Service', 'Budget', 'Preferred contact', 'Message', 'Internal notes']);

            $query->chunk(500, function ($inquiries) use ($output): void {
                foreach ($inquiries as $inquiry) {
                    fputcsv($output, array_map($this->csvSafe(...), [
                        $inquiry->id,
                        $inquiry->created_at->toDateTimeString(),
                        $inquiry->status->label(),
                        $inquiry->name,
                        $inquiry->company,
                        $inquiry->email,
                        $inquiry->phone,
                        $inquiry->serviceLabel(),
                        $inquiry->budget,
                        $inquiry->preferred_contact,
                        $inquiry->message,
                        $inquiry->notes,
                    ]));
                }
            });

            fclose($output);
        }, 'kliq-inquiries-'.now()->format('Y-m-d').'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /**
     * @return Builder<Inquiry>
     */
    private function filtered(Request $request): Builder
    {
        $status = InquiryStatus::tryFrom((string) $request->query('status'));
        $service = $request->query('service');

        return Inquiry::query()
            ->search($request->query('q'))
            ->when($status, fn (Builder $query) => $query->where('status', $status))
            ->when($service === 'none', fn (Builder $query) => $query->whereNull('service_id'))
            ->when(ctype_digit((string) $service), fn (Builder $query) => $query->where('service_id', (int) $service))
            ->orderBy('created_at', $request->query('sort') === 'oldest' ? 'asc' : 'desc')
            ->orderBy('id', $request->query('sort') === 'oldest' ? 'asc' : 'desc');
    }

    /**
     * Neutralise values a spreadsheet would run as a formula.
     */
    private function csvSafe(mixed $value): string
    {
        $value = (string) $value;

        return preg_match('/^[=+\-@\t\r]/', $value) ? "'".$value : $value;
    }
}
