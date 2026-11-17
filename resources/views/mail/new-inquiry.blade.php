{{--
    Email sent to the agency for every contact form submission.
    The HTML is kept flush left: indented lines would become Markdown code blocks.
--}}
@php
    $details = [
        'Full name' => $inquiry->name,
        'Business / company' => $inquiry->company,
        'Email address' => $inquiry->email,
        'Phone number' => $inquiry->phone,
        'Service of interest' => $inquiry->serviceLabel(),
        'Budget' => $inquiry->budget ? __("site.contact.budgets.{$inquiry->budget}", [], 'en') : null,
        'Preferred contact' => $inquiry->preferred_contact ? __("site.contact.methods.{$inquiry->preferred_contact}", [], 'en') : null,
        'Submitted' => $inquiry->created_at?->timezone(config('app.timezone'))->format('d M Y, H:i'),
        'Site language' => $inquiry->locale ? strtoupper($inquiry->locale) : null,
    ];
@endphp
<x-mail::message>
# New website inquiry

A visitor sent the contact form on the KLIQ website. Reply to this email to answer **{{ $inquiry->name }}** directly.

<table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="border-collapse: collapse; margin: 0 0 24px;">
@foreach ($details as $label => $value)
<tr>
<td style="padding: 8px 12px 8px 0; border-bottom: 1px solid #e8e5ef; color: #718096; font-size: 14px; white-space: nowrap; vertical-align: top;">{{ $label }}</td>
<td style="padding: 8px 0; border-bottom: 1px solid #e8e5ef; color: #2d3748; font-size: 15px; vertical-align: top;">@if ($label === 'Email address')<a href="mailto:{{ $value }}">{{ $value }}</a>@elseif ($label === 'Phone number' && filled($value))<a href="{{ $inquiry->phoneHref() }}">{{ $value }}</a>@else{{ filled($value) ? $value : '—' }}@endif</td>
</tr>
@endforeach
</table>

## Project details

<div style="padding: 16px; background: #f4f4f7; border-radius: 6px; color: #2d3748; font-size: 15px; line-height: 1.6;">{!! str_replace(["\r\n", "\r", "\n"], '<br>', e($inquiry->message)) !!}</div>

<x-mail::button :url="route('admin.inquiries.show', $inquiry)">
Open in dashboard
</x-mail::button>

— KLIQ website
</x-mail::message>
