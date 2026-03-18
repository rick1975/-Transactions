@props(['post'])

@php(setup_postdata($post))
<article class="bg-white rounded-lg shadow overflow-hidden">  
  {{-- Foto --}}
  @if(has_post_thumbnail($post))
    <img src="{{ get_the_post_thumbnail_url($post->ID, 'medium') }}" alt="{{ get_the_title($post) }}" class="w-full h-[220px] object-cover">
  @else
    <img src="{{ Vite::asset('resources/images/teamlid-fallback.avif') }}" alt="Geen foto beschikbaar" class="w-full h-[220px] object-cover">
  @endif

  <div class="p-6">
    <h4>{{ get_the_title($post) }}</h4>
    @if(get_field('team_functie', $post->ID))
      <p class="text-sm text-gray-600">{{ get_field('team_functie', $post->ID) }}</p>
    @endif
    @if(has_excerpt($post))
      <p class="mt-3 text-sm text-gray-700">{{ get_the_excerpt($post) }}</p>
    @endif
  </div>
</article>
@php(wp_reset_postdata())