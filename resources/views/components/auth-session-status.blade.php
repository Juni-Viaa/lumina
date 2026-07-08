@props(['status'])

@if ($status)
    <div
        x-data="{ show: true }"
        x-init="setTimeout(() => show = false, 3500)"
        x-show="show"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 -translate-y-2"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 -translate-y-2"
        {{ $attributes->merge([
            'class' => 'mb-4 flex items-start justify-between gap-3 rounded-2xl border border-emerald-300/40 bg-emerald-500/15 px-4 py-3 text-sm font-medium text-emerald-900 backdrop-blur-md shadow'
        ]) }}
    >
        <span>{{ $status }}</span>

        <button
            type="button"
            @click="show = false"
            class="text-emerald-900/70 hover:text-emerald-900"
        >
            ✕
        </button>
    </div>
@endif