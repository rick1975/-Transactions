<time class="dt-published text-sm text-gray-500" datetime="{{ get_post_time('c', true) }}">
  @php($primary_cat = get_the_category())
  @if(!empty($primary_cat))
    <a href="{{ get_category_link($primary_cat[0]->term_id) }}" class="bg-trans-green !text-gray-600 py-1 px-2 rounded-md hover:bg-trans-orange hover:!text-white transition-colors">{{ $primary_cat[0]->name }}</a> -
  @endif
  {{ get_the_date('d F Y') }}
</time>