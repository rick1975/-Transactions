{{-- resources/views/components/faq.blade.php --}}
<section class="py-12 bg-slate-50 relative left-1/2 right-1/2 -ml-[50vw] -mr-[50vw] w-screen">
  <div class="mx-auto max-w-4xl px-6 sm:px-6 lg:px-8">
    <div class="mb-10">
      <h2>{{ $title ?? 'Veelgestelde vragen' }}</h2>
      @if(!empty($intro ?? null))
        <p class="mt-2 text-base text-gray-600">{{ $intro }}</p>
      @endif
    </div>

    <div x-data="{ open: -1 }" class="space-y-3">
      @foreach($faqs as $i => $item)
        <div class="rounded-xl bg-white shadow-xs transition-all duration-200" :class="open === {{ $i }} ? 'border border-trans-green ring-2 ring-trans-green/20' : 'border border-gray-200'">
          <button
            type="button"
            class="flex w-full items-center justify-between gap-4 px-5 py-4 text-left cursor-pointer"
            @click="open = (open === {{ $i }} ? -1 : {{ $i }})"
          >
            <span class="font-semibold text-gray-900">{{ $item['q'] }}</span>

            <span class="flex h-8 w-8 items-center justify-center rounded-full border border-gray-200 transition-all duration-200" 
              :class="open === {{ $i }} ? 'rotate-45 bg-trans-green text-white !border-[#9ccac5]' : 'bg-white text-gray-900'"
              aria-hidden="true"
            >
              <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 4a1 1 0 0 1 1 1v4h4a1 1 0 1 1 0 2h-4v4a1 1 0 1 1-2 0v-4H5a1 1 0 1 1 0-2h4V5a1 1 0 0 1 1-1z" clip-rule="evenodd"/>
              </svg>
            </span>
          </button>

          <div
            x-show="open === {{ $i }}"
            x-collapse.duration.250ms
            x-cloak
            class="px-5 pb-5 text-gray-600"
          >
            <div class="leading-relaxed [&>p]:mb-3 [&>p:last-child]:mb-0">
              {!! wp_kses_post($item['a']) !!}
            </div>
          </div>
        </div>
      @endforeach
    </div>

    @if(!empty($ctaText ?? null))
      <div class="mt-10 rounded-2xl bg-gray-50 p-6 border border-gray-200">
        <p class="text-gray-700">
          {{ $ctaText }}
          @if(!empty($ctaUrl ?? null))
            <a href="{{ $ctaUrl }}" class="font-semibold underline">{{ $ctaLabel ?? 'Neem contact op' }}</a>.
          @endif
        </p>
      </div>
    @endif
  </div>
</section>
