{{-- The logo uploaded in the dashboard settings, or the KLIQ wordmark. --}}
@if ($url = $site->imageUrl('logo'))
    <img src="{{ $url }}" alt="KLIQ" {{ $attributes->merge(['class' => 'h-[1em] w-auto']) }}>
@else
    <x-logo {{ $attributes }} />
@endif
