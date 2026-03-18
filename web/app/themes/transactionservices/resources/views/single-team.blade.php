@extends('layouts.app')

@section('content')

<section>
  <nav class="pb-6 md:pb-10 text-sm text-gray-500">
    <ol class="flex items-center gap-2">
      <li><a href="{{ home_url('/') }}" class="text-gray-500 hover:text-trans-orange transition">Home</a></li>
      <li>/</li>
      <li><a href="{{ get_post_type_archive_link('team') }}" class="text-gray-500 hover:text-trans-orange transition">Team</a></li>
      <li>/</li>
      <li class="text-gray-700">{{ get_the_title() }}</li>
    </ol>
  </nav>

  <div class="grid md:grid-cols-2 gap-10 items-start">
    {{-- Foto --}}
    @if(has_post_thumbnail())
      {!! get_the_post_thumbnail(get_the_ID(), 'large', [
          'class'         => 'w-full aspect-square object-cover rounded-lg',
          'alt'           => get_the_title(),
          'fetchpriority' => 'high',
          'loading'       => 'eager',
      ]) !!}
    @else
      <img src="{{ Vite::asset('resources/images/teamlid-fallback.avif') }}" alt="Geen foto beschikbaar" class="w-full aspect-square object-cover rounded-lg" fetchpriority="high" loading="eager">
    @endif

    {{-- Info --}}
    <div>
      <h1 class="mb-2 text-3xl">{{ get_the_title() }}</h1>

      @if(get_field('team_functie'))
        <p class="text-gray-600 mb-4"> {{ get_field('team_functie') }}</p>
      @endif

      {{-- Gekoppelde diensten --}}
      @php $diensten = get_field('team_diensten') ?: []; @endphp
      @if(!empty($diensten))
        <div class="border-y border-slate-200 py-3 my-3">
          <p class="text-sm font-semibold text-gray-950">Specialisme</p>
          <div class="flex flex-wrap gap-2">
            @foreach($diensten as $dienst)
              <a href="{{ get_permalink($dienst) }}" class="text-gray-600 hover:text-trans-green-dark transition">
                {{ get_the_title($dienst) }}
              </a>
            @endforeach
          </div>
        </div>
      @endif

      <div class="prose">
        {!! the_content() !!}
      </div>

      <div class="mt-6 flex items-center gap-4">
        {{-- LinkedIn button --}}
        @if(get_field('team_linkedin'))
          <a href="{{ get_field('team_linkedin') }}" target="_blank" rel="noopener noreferrer"
            class="shrink-0 bg-blue-500 hover:bg-blue-700 transition p-2 rounded-lg text-white">
            <svg fill="currentColor" viewBox="0 0 448 512" class="w-6 h-6" aria-hidden="true" xmlns="http://www.w3.org/2000/svg">
              <path d="M100.28 448H7.4V148.9h92.88zM53.79 108.1C24.09 108.1 0 83.5 0 53.8a53.79 53.79 0 0 1 107.58 0c0 29.7-24.1 54.3-53.79 54.3zM447.9 448h-92.68V302.4c0-34.7-.7-79.2-48.29-79.2-48.29 0-55.69 37.7-55.69 76.7V448h-92.78V148.9h89.08v40.8h1.3c12.4-23.5 42.69-48.3 87.88-48.3 94 0 111.28 61.9 111.28 142.3V448z"/>
            </svg>
            <span class="sr-only">LinkedIn</span>
          </a>
        @endif

        {{-- Mail + Telefoon gestapeld --}}
        <div class="flex flex-col gap-1">
          @if(get_field('team_mail'))
            <a href="mailto:{{ get_field('team_mail') }}" class="inline-flex items-center gap-1.5 text-sm text-gray-600 hover:text-gray-900 transition">
              <span>{{ get_field('team_mail') }}</span>
            </a>
          @endif

          @if(get_field('team_tel'))
            <a href="tel:{{ get_field('team_tel') }}" class="inline-flex items-center gap-1.5 text-sm text-gray-600 hover:text-gray-900 transition">
              <span>{{ get_field('team_tel') }}</span>
            </a>
          @endif
        </div>
      </div>
    </div>
  </div>
</section>
@endsection