@extends('layouts.app')

@section('content')
@php($fallbackImg = Vite::asset('resources/images/teamlid-fallback.avif'))

  <div class="lg:grid lg:grid-cols-3 lg:gap-12 items-start mb-4">

    {{-- Hoofdinhoud --}}
    <div class="lg:col-span-2">
      <article @php(post_class('lg:mb-16'))>
        <header class="mb-8">
          <h1>{{ $title }}</h1>
        </header>

        <div class="prose max-w-none">
          {!! $content !!}
        </div>
      </article>

      @if(!empty($faqs))
        <x-faq :faqs="$faqs" />
      @endif
    </div>

    {{-- Sidebar: teamleden --}}
    @if(!empty($teamleden))
      <aside class="mt-12 lg:mt-0" x-data="{
       open: false,
       selected: null,
       teamleden: @js($teamleden),
       fallback: '{{ $fallbackImg }}'
       }" x-init="open = false">
        <h3 class="text-lg font-semibold my-4">Onze adviseurs</h3>

        <ul class="space-y-4 mb-0">
          @foreach($teamleden as $lid)
            <li>
              <button type="button" @click="selected = teamleden[{{ $loop->index }}]; open = true" class="w-full text-left flex border border-slate-300 items-center gap-4 p-3 rounded-lg hover:bg-gray-50 hover:border-trans-green transition group">
                {{-- Foto --}}
                <div class="shrink-0 w-14 h-14 rounded-full overflow-hidden bg-gray-100">
                  <img src="{{ $lid['thumbnail'] ?? $fallbackImg }}" alt="{{ $lid['title'] }}" class="w-full h-full object-cover">
                </div>

                {{-- Info --}}
                <div class="min-w-0 flex-1">
                  <p class="font-medium text-gray-900 transition truncate mb-0">{{ $lid['title'] }}</p>
                  @if($lid['functie'])
                    <p class="text-sm text-gray-500 truncate">{{ $lid['functie'] }}</p>
                  @endif
                </div>

                {{-- Pijl --}}
                <svg class="shrink-0 w-4 h-4 text-gray-400 group-hover:text-trans-green transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
              </button>
            </li>
          @endforeach
        </ul>

        {{-- Backdrop --}}
        <div x-show="open"
             x-cloak
             @click="open = false"
             class="fixed inset-0 bg-black/40 z-40"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0">
        </div>

        {{-- Slide-over drawer --}}
        <div x-show="open"
             x-cloak
             class="fixed right-0 top-0 h-full w-full max-w-lg bg-white shadow-xl z-[10000] flex flex-col"
             x-transition:enter="transition ease-out duration-500"
             x-transition:enter-start="translate-x-full"
             x-transition:enter-end="translate-x-0"
             x-transition:leave="transition ease-in duration-300"
             x-transition:leave-start="translate-x-0"
             x-transition:leave-end="translate-x-full">

          {{-- Header --}}
          <div class="bg-trans-green flex items-center justify-end px-4 py-3 shrink-0">
            <button type="button" @click="open = false" class="rounded-md p-1 text-gray-400 hover:text-gray-600 transition">
              <span class="sr-only">Sluiten</span>
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
              </svg>
            </button>
          </div>

          {{-- Scrollbare inhoud --}}
          <div class="flex-1 overflow-y-auto">

            {{-- Foto --}}
            <div class="px-6 pt-6 flex gap-4 items-start">
              <img :src="selected.thumbnail || fallback" :alt="selected.title" class="w-28 h-28 rounded-lg object-cover object-top shrink-0">
              <div class="min-w-0">
                <h2 class="text-xl font-semibold text-gray-900 mb-0" x-text="selected.title"></h2>
                <p class="text-gray-600 font-medium text-sm mb-0" x-text="selected.functie" x-show="selected.functie"></p>
                <div class="flex flex-wrap gap-x-4 gap-y-1 mt-1">
                  {{-- Mail --}}
                  <template x-if="selected.mail">
                    <a :href="'mailto:' + selected.mail"
                      class="inline-flex items-center gap-1.5 text-sm text-gray-600 hover:text-trans-green transition">
                      <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                      </svg>
                      <span x-text="selected.mail"></span>
                    </a>
                  </template>
                  {{-- Telefoon --}}
                  <template x-if="selected.telefoon">
                    <a :href="'tel:' + selected.telefoon"
                      class="inline-flex items-center gap-1.5 text-sm text-gray-600 hover:text-trans-green transition">
                      <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                      </svg>
                      <span x-text="selected.telefoon"></span>
                    </a>
                  </template>
                  {{-- LinkedIn --}}
                  <template x-if="selected.linkedin">
                    <a :href="selected.linkedin" target="_blank" rel="noopener noreferrer"
                      class="inline-flex items-center gap-1.5 text-sm text-gray-600 hover:text-trans-green transition">
                      <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433a2.062 2.062 0 01-2.063-2.065 2.064 2.064 0 112.063 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
                      </svg>
                      <span>LinkedIn</span>
                    </a>
                  </template>
                </div>
              </div>
            </div>

            <div class="px-6 py-6 space-y-4">
              {{-- Bio / excerpt --}}
              <p class="text-gray-600 text-sm leading-relaxed" x-html="selected.excerpt" x-show="selected.excerpt"></p>

              {{-- Link naar volledig profiel --}}
              <a :href="selected.permalink" class="btn block text-center text-sm mt-4">
                Bekijk volledig profiel
              </a>

            </div>
          </div>
        </div>

      </aside>
    @endif
  </div>
@endsection