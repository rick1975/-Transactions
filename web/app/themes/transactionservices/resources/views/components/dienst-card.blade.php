@props(['post'])

@php(setup_postdata($post))

<article x-data="{ show: false }" x-intersect.once="show = true" :class="show ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-6'" class="relative md:rounded-lg overflow-hidden group min-h-[320px] transition-all duration-700 ease-out">

  @if(has_post_thumbnail($post))
  {!! wp_get_attachment_image(
      get_post_thumbnail_id($post->ID),
      'medium_large', 
      false,
      [
        'class' => 'absolute inset-0 w-full h-full object-cover',
        'loading' => 'lazy',
        'decoding' => 'async',
        'sizes' => '(min-width:1024px) 33vw, 100vw'
      ]
  ) !!}
    <img src="{{ get_the_post_thumbnail_url($post->ID, 'large') }}" alt="{{ get_the_title($post) }}" class="absolute inset-0 w-full h-full object-cover" loading="lazy">
    <div class="absolute inset-0 bg-black/20 group-hover:bg-black/40 transition"></div>
  @endif

  <div class="relative z-10 p-6 flex flex-col justify-end h-full ">
    <h2 class="text-2xl font-semibold mb-2 text-white">{{ get_the_title($post) }}</h2>

    @if(has_excerpt($post))
      <p class="mb-4 text-white/90">{!! get_the_excerpt($post) !!}</p>
    @endif

    <span class="btn w-fit group-hover:bg-trans-green">Lees meer</span>
  </div>

  <a href="{{ get_permalink($post) }}" class="absolute inset-0 z-20" aria-label="{{ get_the_title($post) }}"></a>
</article>

@php(wp_reset_postdata())