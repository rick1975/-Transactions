@php
  $hasAcf = function_exists('get_field');
  $title = $hasAcf ? get_field('figures_title', 'option') : null;
  $text  = $hasAcf ? get_field('figures_text', 'option') : null;
@endphp

@if($title || $text)
<section class="bg-trans-green py-8 lg:py-16 sm:my-16 max-w-6xl mx-auto px-6 md:px-0">
  <div class="max-w-3xl mx-auto">
    @if($title)
      <h2>{!! $title !!}</h2>
    @endif

    @if($text)
      <div class="prose prose-invert max-w-none">
        {!! wp_kses_post($text) !!}
      </div>
    @endif
  </div>
</section>
@endif