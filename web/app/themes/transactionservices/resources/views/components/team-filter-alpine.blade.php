@php
  $afdelingen = get_terms(['taxonomy' => 'afdeling', 'hide_empty' => false]);
  $diensten = get_posts(['post_type' => 'dienst','posts_per_page' => -1,'orderby'=>'title','order'=>'ASC']);

  $afdItems = array_merge([['value'=>'', 'label'=>'Alle afdelingen']], array_map(fn($t) => [
    'value' => $t->slug,
    'label' => $t->name,
  ], $afdelingen));

  $dienstItems = array_merge([['value'=>'', 'label'=>'Alle specialismes']], array_map(fn($d) => [
    'value' => (string) $d->ID,   // matcht jouw relationship IDs
    'label' => $d->post_title,
  ], $diensten));
@endphp

<div class="mb-8">
  <p class="text-sm text-gray-600" x-text="filtered.length + ' resultaten'"></p>
  <div class="mt-3 grid gap-3 md:grid-cols-3">
    <x-ui.dropdown label="Alle afdelingen" model="afdeling" :items="$afdItems" />
    <x-ui.dropdown label="Alle specialismes" model="dienst" :items="$dienstItems" />
    <input type="text" x-model="s" placeholder="Zoek op naam…" class="border border-stone-200 rounded-lg px-4 py-2 text-sm placeholder-gray-600" @input.debounce.250ms="syncUrl(false)"/>
  </div>
</div>