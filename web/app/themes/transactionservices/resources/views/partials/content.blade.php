<article class="relative group">
  @if(has_post_thumbnail())
    <img src="{{ get_the_post_thumbnail_url(get_the_ID(), 'medium_large') }}"
      alt="{{ get_the_title() }}"
      class="w-full h-48 object-cover rounded-lg mb-4">
  @endif
  <div class="p-2 pt-0">
    <header>
      <h2 class="text-xl mb-1">
        <a href="{{ get_permalink() }}" class="text-gray-800 group-hover:text-primary transition-colors after:content-[''] after:absolute after:inset-0">
          {!! $title !!}
        </a>
      </h2>
    </header>

    <div class="entry-summary text-gray-600 text-sm">
      @php(the_excerpt())
    </div>
  </div>
</article>