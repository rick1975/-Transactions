@props(['post'])

@php(setup_postdata($post))

<article class="card border-t border-gray-100 shadow hover:shadow-md transition-shadow duration-300">
  @if(has_post_thumbnail($post))
    <div class="mb-4 -mx-6 -mt-6 rounded-t-lg overflow-hidden">
      {!! wp_get_attachment_image(
          get_post_thumbnail_id($post->ID),
          'medium',
          false,
          [
            'class' => 'w-full h-48 object-cover',
            'loading' => 'lazy',
            'decoding' => 'async',
            'sizes' => '(min-width:1024px) 33vw, 100vw'
          ]
      ) !!}
    </div>
  @endif

  <h3 class="text-lg md:text-xl mb-2 font-semibold;">{{ get_the_title($post) }}</h3>

  <div class="space-y-3">
    @if($adres = get_field('locatie_adres', $post->ID))
      <div class="text-gray-700">
        {!! nl2br(esc_html($adres)) !!}
      </div>
    @endif

    @if($telefoon = get_field('locatie_telefoon', $post->ID))
      <div>
        <a href="tel:{{ preg_replace('/[^0-9+]/', '', $telefoon) }}" class="text-gray-700 hover:text-primary">
          {{ $telefoon }}
        </a>
      </div>
    @endif

    @if($email = get_field('locatie_email', $post->ID))
      <div>
        <a href="mailto:{{ $email }}" class="text-gray-700 hover:text-primary">
          {{ $email }}
        </a>
      </div>
    @endif

    @if($route = get_field('locatie_route', $post->ID))
      <div class="mt-5 pt-6 border-t border-gray-200">
        <a href="{{ $route }}" target="_blank" rel="noopener" class="btn inline-flex items-center">
          <span class="dashicons dashicons-location mr-1 text-gray-900"></span>
          Route
        </a>
      </div>
    @endif
  </div>
</article>

@php(wp_reset_postdata())
