@props([
  'label' => 'Kies...',
  'model' => 'value',     // alpine model name
  'items' => [],          // [['value'=>'', 'label'=>'...'], ...]
])

<div class="relative w-full" x-data="{
    open:false,
    get selectedLabel() {
      const v = this.{{ $model }};
      const i = {{ json_encode($items) }}.find(x => String(x.value) === String(v));
      return i ? i.label : '{{ $label }}';
    }
  }"
  @keydown.escape.window="open = false"
>
  <button type="button" class="w-full flex items-center justify-between gap-3 border border-stone-200 px-4 py-2 bg-white hover:bg-slate-50 transition rounded-lg" :class="open ? 'rounded-b-none border-b-0' : ''" @click="open = !open" :aria-expanded="open">
    <span class="text-sm transition" x-text="selectedLabel"></span>
    <svg class="w-4 h-4 transition" :class="[open ? 'rotate-180' : '', {{ $model }} ? 'text-trans-green' : 'text-gray-400']" viewBox="0 0 20 20" fill="currentColor">
      <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.06 1.06l-4.24 4.24a.75.75 0 01-1.06 0L5.21 8.29a.75.75 0 01.02-1.08z" clip-rule="evenodd"/>
    </svg>
  </button>

  <div x-show="open" x-transition @click.outside="open = false" class="absolute z-20 w-full mt-[3px] bg-white shadow-sm overflow-hidden border border-stone-200 rounded-b-lg" style="top: 100%;">
    <ul class="max-h-64 overflow-auto">
      @foreach($items as $it)
        <li>
          <button type="button" class="w-full text-left px-4 py-2 text-sm hover:bg-trans-green transition" :class="String({{ $model }}) === String('{{ $it['value'] }}') ? 'bg-gray-50 font-semibold' : ''" @click="{{ $model }}='{{ $it['value'] }}'; open=false; syncUrl()">
            {{ $it['label'] }}
          </button>
        </li>
      @endforeach
    </ul>
  </div>
</div>