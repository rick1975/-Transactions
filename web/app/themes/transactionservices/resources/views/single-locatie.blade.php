@extends('layouts.app')

@section('content')
<div class="single-location">
  <nav class="pb-6 md:pb-10 text-sm text-gray-500">
    <ol class="flex items-center gap-2">
      <li><a href="{{ home_url('/') }}" class="text-gray-500 hover:text-trans-orange transition">Home</a></li>
      <li>/</li>
      <li><a href="{{ get_post_type_archive_link('locatie') }}" class="text-gray-500 hover:text-trans-orange transition">Locaties</a></li>
      <li>/</li>
      <li class="text-gray-700">{{ $title }}</li>
    </ol>
  </nav>

  <div class="grid md:grid-cols-2 gap-10 items-start">
    {{-- Foto --}}
    @if($thumbnail)
      <div class="rounded-lg overflow-hidden shadow-lg">
        <img
          src="{{ $thumbnail }}"
          alt="{{ $title }}"
          class="w-full aspect-video object-cover"
          loading="lazy"
        >
      </div>
    @endif

    {{-- Info --}}
    <div>
      <h1 class="mb-6">{{ $title }}</h1>

      <div class="space-y-4 mb-8">
        @if($adres)
          <div class="flex items-start">
            <span class="dashicons dashicons-location text-primary text-2xl mr-3 mt-1"></span>
            <div>
              <h4 class="text-lg font-semibold mb-1">Adres</h4>
              <div class="text-gray-700">
                {!! nl2br(esc_html($adres)) !!}
              </div>
            </div>
          </div>
        @endif

        @if($telefoon)
          <div class="flex items-start">
            <span class="dashicons dashicons-phone text-primary text-2xl mr-3 mt-1"></span>
            <div>
              <h4 class="text-lg font-semibold mb-1">Telefoon</h4>
              <a href="tel:{{ preg_replace('/[^0-9+]/', '', $telefoon) }}" class="text-gray-700 hover:text-primary transition">
                {{ $telefoon }}
              </a>
            </div>
          </div>
        @endif

        @if($email)
          <div class="flex items-start">
            <span class="dashicons dashicons-email text-primary text-2xl mr-3 mt-1"></span>
            <div>
              <h4 class="text-lg font-semibold mb-1">E-mail</h4>
              <a href="mailto:{{ $email }}" class="text-gray-700 hover:text-primary transition">
                {{ $email }}
              </a>
            </div>
          </div>
        @endif
      </div>

      @if($route)
        <div class="pt-6 border-t border-gray-200">
          <a href="{{ $route }}" target="_blank" rel="noopener" class="btn inline-flex items-center">
            <span class="dashicons dashicons-location mr-2"></span>
            Bekijk route op Google Maps
          </a>
        </div>
      @endif
    </div>
  </div>
</div>
@endsection