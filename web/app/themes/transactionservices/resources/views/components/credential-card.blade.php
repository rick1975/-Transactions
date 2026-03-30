@props(['credential'])

<article class="bg-white rounded-lg shadow overflow-hidden hover:shadow-lg transition border border-slate-100 h-full">
    <div class="p-6">

        @if($credential['logo1'])
            <div class="h-14 flex items-center mb-2">
                <img src="{{ home_url('/app/uploads/' . $credential['logo1']) }}" alt="{{ $credential['partij1'] }}" class="max-w-[90px] max-h-20 w-auto object-contain mix-blend-multiply">            </div>
        @else
            <div class="h-14 flex items-center mb-2">
                <span class="text-base font-semibold text-gray-800">{{ $credential['partij1'] }}</span>
            </div>
        @endif

        <p class="text-gray-700 mt-1">{{ $credential['omschrijving1'] }}</p>

        <div class="border-t border-slate-200 pt-4 mt-2">
            @if($credential['logo2'])
                <div class="h-14 flex items-center mb-2"><img src="{{ home_url('/app/uploads/' . $credential['logo2']) }}" alt="{{ $credential['partij2'] }}" class="max-w-[90px] max-h-20 w-auto object-contain mix-blend-multiply"></div>
            @elseif($credential['partij2'])
                <div class="h-14 flex items-center mb-2">
                    <span class="text-base font-semibold text-gray-800">{{ $credential['partij2'] }}</span>
                </div>
            @else
                <div class="h-14 mb-2">
                    <span class="text-base font-semibold text-gray-800">{{ $credential['partij2'] }}</span>
                </div>
            @endif

            <p class="text-sm text-gray-500 mt-1">{{ $credential['omschrijving2'] }}</p>
        </div>

        @if(!empty($credential['datum']))
            <p class="text-xs text-gray-400 mt-4">
                {{ \Carbon\Carbon::parse($credential['datum'])->format('Y') }}
            </p>
        @endif
    </div>
</article>