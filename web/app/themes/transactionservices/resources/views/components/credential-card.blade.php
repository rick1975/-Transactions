@props(['credential'])

<article class="bg-white rounded-lg shadow overflow-hidden hover:shadow-lg transition border border-slate-100 h-full">
    <div class="p-6">
        <strong class="block text-base font-semibold">{{ $credential['partij1'] }}</strong>
        <p class="text-base text-gray-700 mt-1">{{ $credential['omschrijving1'] }}</p>

        <div class="border-t border-slate-200 pt-4 mt-4">
            {{-- <strong class="block text-base font-semibold">{{ $credential['partij2'] }}</strong> --}}
            <p class="text-sm text-gray-500 mt-1">{{ $credential['omschrijving2'] }}</p>
        </div>

        @if(!empty($credential['datum']))
            <p class="text-xs text-gray-400 mt-4">
                {{ \Carbon\Carbon::parse($credential['datum'])->format('Y') }}
            </p>
        @endif
    </div>
</article>