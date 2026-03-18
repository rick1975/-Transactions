@extends('layouts.app')

@section('content')
  <div class="locations">
    <header class="mb-6">
      <h1>{{ $archive_title }}</h1>
      <p class="max-w-3xl">Heb je een vraag? We helpen je graag verder. </p>
    </header>

    @if($locaties->have_posts())
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 max-w-6xl mx-auto">
        @foreach($locaties->posts as $post)
          <x-locatie-card :post="$post" />
        @endforeach
      </div>
    @else
      <p class="text-center text-gray-600">Geen locaties gevonden.</p>
    @endif
  </div>
@endsection