<x-layouts.admin title="Brands">
    <x-admin.page-header title="Brands" description="Clients and partners shown in the “Brands we've worked with” section. Upload each brand's logo; without one, its name is shown instead.">
        <a href="{{ route('home', ['locale' => config('app.locale')]) }}#brands" target="_blank" class="a-btn a-btn--ghost"><x-icon name="external-link" class="size-4" /> View section</a>
    </x-admin.page-header>

    @if ($errors->any())
        <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-800" role="alert">{{ $errors->first() }}</div>
    @endif

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="flex flex-col gap-3 lg:col-span-2">
            @forelse ($brands as $brand)
                <details class="a-card group">
                    <summary class="flex cursor-pointer list-none flex-wrap items-center gap-4 p-4">
                        <span class="grid h-14 w-28 shrink-0 place-items-center overflow-hidden rounded-xl bg-white p-2 ring-1 ring-line">
                            @if ($logo = $brand->logoUrl())
                                <img src="{{ $logo }}" alt="" class="max-h-full max-w-full object-contain" loading="lazy" onerror="this.remove()">
                            @else
                                <span class="text-center text-xs font-semibold text-muted">No logo</span>
                            @endif
                        </span>
                        <span class="min-w-0 flex-1">
                            <span class="block font-semibold">{{ $brand->name }}</span>
                            <span class="block truncate text-xs text-muted">{{ $brand->url ?: 'No link' }}</span>
                        </span>
                        <span @class(['rounded-full px-2.5 py-0.5 text-xs font-semibold', 'bg-emerald-100 text-emerald-800' => $brand->is_active, 'bg-neutral-200 text-neutral-700' => ! $brand->is_active])>{{ $brand->is_active ? 'Shown' : 'Hidden' }}</span>
                        <span class="flex gap-1">
                            @foreach (['up' => 'arrow-up', 'down' => 'arrow-down'] as $direction => $icon)
                                <button type="submit" form="move-{{ $brand->id }}-{{ $direction }}" class="a-btn a-btn--ghost a-btn--icon !size-8 disabled:opacity-30" title="Move {{ $direction }}" @disabled(($direction === 'up' && $loop->parent->first) || ($direction === 'down' && $loop->parent->last))>
                                    <x-icon :name="$icon" class="size-3.5" /><span class="sr-only">Move {{ $direction }}</span>
                                </button>
                            @endforeach
                        </span>
                        <span class="a-btn a-btn--ghost a-btn--sm"><x-icon name="pencil" class="size-3.5" /> Edit</span>
                    </summary>

                    <div class="border-t border-line p-5">
                        <form method="POST" action="{{ route('admin.brands.update', $brand) }}" enctype="multipart/form-data" class="flex flex-col gap-4">
                            @csrf
                            @method('PUT')
                            <div class="grid gap-4 sm:grid-cols-2">
                                <x-admin.input name="name" label="Brand name" :value="$brand->name" :prefix="'brand'.$brand->id" maxlength="120" required />
                                <x-admin.input name="url" type="url" label="Website (optional)" :value="$brand->url" :prefix="'brand'.$brand->id" placeholder="https://" />
                            </div>
                            <x-admin.image-input name="logo" label="Logo" :current="$brand->logoUrl()" :prefix="'brand'.$brand->id" fit="object-contain p-3 bg-white" hint="PNG or WebP with a transparent background works best, up to 2 MB." />
                            <x-admin.switch name="is_active" label="Show on the website" :checked="$brand->is_active" />
                            <div class="flex flex-wrap justify-between gap-2">
                                <button type="submit" class="a-btn a-btn--dark" data-busy-label="Saving…">Save</button>
                                <button type="submit" form="delete-brand-{{ $brand->id }}" class="a-btn a-btn--danger"><x-icon name="trash" class="size-4" /> Delete</button>
                            </div>
                        </form>
                    </div>
                </details>

                @foreach (['up', 'down'] as $direction)
                    <form id="move-{{ $brand->id }}-{{ $direction }}" method="POST" action="{{ route('admin.brands.move', $brand) }}" hidden>
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="direction" value="{{ $direction }}">
                    </form>
                @endforeach
                <form id="delete-brand-{{ $brand->id }}" method="POST" action="{{ route('admin.brands.destroy', $brand) }}" data-confirm="{{ $brand->name }} and its logo will be removed from the website." hidden>
                    @csrf
                    @method('DELETE')
                </form>
            @empty
                <div class="a-card"><x-admin.empty-state icon="badge-check" title="No brands yet" text="Add the clients and partners you have worked with. The section stays hidden until at least one brand is shown." /></div>
            @endforelse
        </div>

        <form method="POST" action="{{ route('admin.brands.store') }}" enctype="multipart/form-data" class="a-card flex h-fit flex-col gap-4 p-6">
            @csrf
            <h2 class="font-semibold">Add a brand</h2>
            <x-admin.input name="name" label="Brand name" prefix="new" maxlength="120" required />
            <x-admin.input name="url" type="url" label="Website (optional)" prefix="new" placeholder="https://" />
            <div>
                <label for="new-logo" class="a-label">Logo</label>
                <input type="file" id="new-logo" name="logo" accept="image/jpeg,image/png,image/webp" class="mt-2 block w-full text-sm file:me-3 file:cursor-pointer file:rounded-full file:border-0 file:bg-ink file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-orange hover:file:text-black">
                <p class="a-hint">PNG, WebP or JPG, up to 2 MB.</p>
            </div>
            <button type="submit" class="a-btn a-btn--primary" data-busy-label="Adding…"><x-icon name="plus" class="size-4" /> Add brand</button>
        </form>
    </div>
</x-layouts.admin>
