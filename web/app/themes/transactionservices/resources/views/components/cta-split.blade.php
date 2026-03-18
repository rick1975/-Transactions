@php
  $hasAcf = function_exists('get_field');

  $title    = $hasAcf ? get_field('cta_title', 'option') : null;
  $subtitle = $hasAcf ? get_field('cta_subtitle', 'option') : null;
  $text     = $hasAcf ? get_field('cta_text', 'option') : null;
  $button   = $hasAcf ? get_field('cta_button', 'option') : null; // ACF link field (array)
  $image    = $hasAcf ? get_field('cta_image', 'option') : null;  // return_format: array

  // cta_image kan array of ID zijn (maakt niet uit)
  $image_id = is_array($image) ? (int)($image['ID'] ?? 0) : (int)$image;
@endphp

@if($title || $text || $image_id)
<section class="relative overflow-hidden max-w-6xl lg:max-w-[1400px] mx-auto">
  <div class="grid lg:grid-cols-2 min-h-[420px]">
    <div class="relative bg-trans-orange text-white px-6 py-12 sm:p-12 flex items-center z-10 order-2 lg:order-1">
      <div class="absolute top-0 right-[-17px] h-full w-[80px] bg-trans-orange z-30 pointer-events-none transform scale-y-[-1]"
           style="clip-path: polygon(0 0, 100% 0, 80% 100%, 0 100%);" aria-hidden="true"></div>

      <div class="relative max-w-xl z-40">
        @if($title)
          <h2 class="text-3xl md:text-4xl font-extrabold leading-[1.1] uppercase mb-0 text-white">
            {!! $title !!}
            @if($subtitle)
              <br><span class="text-trans-yellow uppercase">{!! $subtitle !!}</span>
            @endif
          </h2>
        @endif

        @if($text)
          <div class="text-lg my-6 text-white">
            {!! nl2br(e($text)) !!}
          </div>
        @endif

        @if(is_array($button) && !empty($button['url']))
          <a href="{{ $button['url'] }}"
             target="{{ $button['target'] ?? '_self' }}"
             rel="{{ ($button['target'] ?? '') === '_blank' ? 'noopener noreferrer' : '' }}"
             class="inline-flex items-center gap-2 font-semibold border-b-2 border-white pb-1 hover:opacity-80 transition text-white">
            {{ $button['title'] ?? 'Ontdek onze aanpak' }} →
          </a>
        @endif
      </div>
    </div>

    <div class="relative z-0 order-1 lg:order-2 h-[260px] lg:h-auto">
      @if($image_id)
        {!! wp_get_attachment_image(
          $image_id,
          'large',
          false,
          [
            'class' => 'absolute inset-0 w-full h-full object-cover',
            'loading' => 'lazy',
            'decoding' => 'async',
            'sizes' => '(min-width:1024px) 50vw, 100vw',
          ]
        ) !!}
      @endif
    </div>
  </div>
</section>
@endif