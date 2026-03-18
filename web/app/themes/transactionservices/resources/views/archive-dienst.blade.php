@extends('layouts.app')

@section('content')
  <header class="mb-8 text-center">
    <h1>{{ $archive_title }}</h1>
  </header>

  @if($diensten->have_posts())
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      @while($diensten->have_posts())
        @php($diensten->the_post())
        
        <article class="relative rounded-lg overflow-hidden group">
          <a href="{{ get_permalink() }}" class="absolute inset-0 z-20"></a>
          @if(has_post_thumbnail())
            <img src="{{ get_the_post_thumbnail_url(get_the_ID(), 'large') }}" alt="{{ get_the_title() }}" class="absolute inset-0 w-full h-full object-cover">
            <div class="absolute inset-0 bg-black/20 group-hover:bg-black/40 transition"></div>
          @endif

          <div class="relative z-10 p-6 flex flex-col justify-end min-h-[320px] text-white">
            <h2 class="text-2xl font-semibold mb-2"><a href="{{ get_permalink() }}" class="text-white hover:underline">{{ get_the_title() }}</a></h2>

            @if(has_excerpt())
              <p class="mb-4 text-white">
                {!! get_the_excerpt() !!}
              </p>
            @endif

            <a href="{{ get_permalink() }}" class="btn w-fit group-hover:bg-trans-green">Lees meer</a>
          </div>
          </a>
        </article>
      @endwhile    
      @php(wp_reset_postdata())
    </div>
  @else
    <p>Geen diensten gevonden.</p>
  @endif

  @if($diensten->max_num_pages > 1)
    <div class="mt-8">
      {!! paginate_links([
        'total' => $diensten->max_num_pages,
        'prev_text' => '← Vorige',
        'next_text' => 'Volgende →'
      ]) !!}
    </div>
  @endif
@endsection