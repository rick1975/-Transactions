<form role="search" method="get" class="search-form" action="{{ home_url('/') }}">
  <div class="flex items-start mb-6">
    <label>
      <span class="sr-only">
        {{ _x('Zoeken voor:', 'label', 'sage') }}
      </span>

      <input
        class="text-sm focus:ring-blue-500 focus:border-blue-500 block w-52 md:w-96 p-[0.65rem] border border-stone-200 rounded-none xl:w-[25rem] xl:rounded-l-md"
        type="search"
        placeholder="{!! esc_attr_x('Zoeken &hellip;', 'placeholder', 'sage') !!}"
        value="{{ get_search_query() }}"
        name="s"
      >
    </label>

    <button 
      type="submit"
      class="text-white text-sm w-full sm:w-auto px-5 py-2.5 xl:py-[0.7rem] text-center xl:rounded-r-md transition-colors duration-200"
      style="background-color: var(--primary);"
      onmouseover="this.style.backgroundColor='var(--primary-dark)'"
      onmouseout="this.style.backgroundColor='var(--primary)'">
      {{ _x('Zoek', 'submit button', 'sage') }}
    </button>
  </div>
</form>
