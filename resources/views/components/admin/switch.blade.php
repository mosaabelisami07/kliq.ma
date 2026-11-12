@props(['name', 'label', 'checked' => false, 'hint' => null])

<div {{ $attributes }}>
    <input type="hidden" name="{{ $name }}" value="0">
    <label class="switch">
        <input type="checkbox" name="{{ $name }}" value="1" @checked(old($name, $checked))>
        <span>{{ $label }}</span>
    </label>
    @if ($hint)
        <p class="a-hint">{{ $hint }}</p>
    @endif
</div>
