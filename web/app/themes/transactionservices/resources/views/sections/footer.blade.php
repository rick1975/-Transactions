@if(!is_post_type_archive('dienst'))
<section class="max-w-[100rem] mx-auto md:px-6 xxl:px-0">
  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    @foreach($diensten->posts as $post)
      <x-dienst-card :post="$post" />
    @endforeach
  </div>
</section>
@endif

<x-ats-figures />
<x-cta-split />
{{-- 
<x-faq
  title="Veelgestelde vragen"
  intro="Antwoorden op de meest gestelde vragen over onze services."
/> --}}

<footer class="footer relative sm:mt-16">
  <div class="container">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
      {{-- Footer Column 1 --}}
      <div>
        <h3 class="text-lg font-semibold mb-4">{{ get_bloginfo('name') }}</h3>
        <p class="text-sm">
          {{ get_bloginfo('description') }}
        </p>
      </div>

      {{-- Footer Column 2 - Primary Menu --}}
      <div>
        <h3 class="text-lg font-semibold mb-4">Menu</h3>
        @if($navigation)
          <ul class="space-y-2 text-sm mb-0">
            @foreach($navigation as $item)
              <li>
                <a href="{{ $item->url }}" class="text-gray-950 hover:text-trans-orange" @if($item->target) target="{{ $item->target }}" @endif>
                  {{ html_entity_decode($item->label) }}
                </a>
              </li>
            @endforeach
          </ul>
        @endif
      </div>

      {{-- Footer Column 3 --}}
      <div>
        <h3 class="text-lg font-semibold mb-4">Contact</h3>
        <p class="text-sm">
          Keynes Building <br>
          John M. Keynesplein 10 <br>
          1066 EP Amsterdam<br>
          033 – 8100 245
        </p>
      </div>
    </div>
  </div>
  <div class="ampersand absolute right-0 bottom-0 h-full opacity-20 pointer-events-none">
    <svg class="h-full w-auto" xmlns="http://www.w3.org/2000/svg" version="1.1" viewBox="0 0 78.8 84.7">
      <defs>
        <style>
          .amp {fill: #fff;}
        </style>
      </defs>
      <path class="amp" d="M19,64.2c-7.7-8.9-.6-16.8,4.4-20.9,6.4,6.6,13.8,13.2,20.5,18.7-5.9,5-12.2,6.5-17.4,5.6-3.1-.5-5.8-1.8-7.6-3.5ZM68.7,68.2c-1-.7-5.3-3.7-11-8.2,4.1-6.5,7.3-14.6,9.6-25,2.9-.4,4.9-1,5.9-1.3h.6c0-.1.1-7.4.1-7.8h-.2c-6.5-.2-16.4,0-24.2,1.6v5.9l.5.2h.2c2.2,1,4.7,1.6,7.3,1.8-1.7,7.9-4.2,14-7.1,18.7-12-9.7-26-22.3-29.9-30.6-2-4.6,1.6-9,5.9-9.2,1.7-.1,3.1.1,4.2.7,1.9,1.2,2.8,3.2,2.8,5.6s-1.3,5.6-3.8,8.3l4.7,4.4h.2c2.9-2.2,7.8-8.6,7.8-13.9s-2.3-8.6-6.2-10.8c-2.3-1.3-5.2-2-8.5-2.2-11.2,0-17.6,6.8-17.4,14.9,0,4.1,4,10.6,8.4,16.2-3.1,2.6-10,7.4-12,14.3-2.3,7.4-.7,16.4,10.1,22.2,3.5,1.8,7.2,2.8,11,2.9,8.5.5,17.5-2.8,23.9-9,5.2,4.1,9,6.8,10,7.6h13.2v-6.2c-1-.2-1.9-.5-3-.6-1-.2-1.9-.4-2.9-.5Z"/>
    </svg>
  </div>
</footer>

{{-- Copyright Section (Outside primary color background) --}}
<div class="bg-white py-6">
  <div class="container">
    <div class="text-center text-xs md:text-sm text-gray-600">
      &copy; <?php echo date('Y'); ?> <?php echo get_bloginfo('name'); ?>. Alle rechten voorbehouden.
    </div>
  </div>
</div>