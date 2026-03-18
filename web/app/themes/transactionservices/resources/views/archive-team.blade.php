@extends('layouts.app')

@section('content')
@php
  $fallback = Vite::asset('resources/images/teamlid-fallback.avif');
  $teamData = collect($teamleden->posts)->map(function ($p) use ($fallback) {
    $dienstIds = get_field('team_diensten', $p->ID) ?: [];
    $dienstIds = array_map('intval', (array) $dienstIds);

    return [
      'id' => (int) $p->ID,
      'name' => get_the_title($p),
      'functie' => get_field('team_functie', $p->ID) ?: '',
      'afdelingen' => wp_get_post_terms($p->ID, 'afdeling', ['fields' => 'slugs']),
      'diensten' => $dienstIds,
      'diensten_data' => array_map(fn($id) => ['id' => $id, 'title' => get_the_title($id), 'url' => get_permalink($id)], $dienstIds),
      'image' => get_the_post_thumbnail_url($p->ID, 'medium') ?: $fallback,
      'url' => get_permalink($p),
    ];
  })->values();
@endphp

<section class="container" 
    x-data="teamFilter({
    team: @js($teamData),
    initial: {
      afdeling: new URLSearchParams(location.search).get('afdeling') || '',
      dienst: new URLSearchParams(location.search).get('dienst') || '',
      s: new URLSearchParams(location.search).get('s') || '',
    }
  })"
>
  <h1>Ons team</h1>

  @include('components.team-filter-alpine')

  <div class="grid md:grid-cols-3 gap-8">
    <template x-for="m in filtered" :key="m.id">
      <a :href="m.url" class="block bg-white rounded-lg shadow overflow-hidden hover:shadow-lg transition">
        <img :src="m.image" :alt="m.name" class="w-full h-[280px] object-cover">
        <div class="p-6">
          <h5 x-text="m.name"></h5>
          <template x-if="m.functie">
            <p class="text-sm text-gray-600" x-text="m.functie"></p>
          </template>
          <template x-if="m.diensten_data && m.diensten_data.length">
            <div class="border-t border-slate-200 pt-2 mt-2">
              <div class="flex flex-wrap gap-2">
                <template x-for="dienst in m.diensten_data" :key="dienst.id">
                  <span class="flex items-center gap-1 text-sm text-gray-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-trans-green shrink-0" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                    <span x-text="dienst.title"></span>
                  </span>
                </template>
              </div>
            </div>
          </template>
        </div>
      </a>
    </template>
  </div>

  <template x-if="filtered.length === 0">
    <div class="mt-6 text-gray-600">Geen teamleden gevonden.</div>
  </template>
</section>
@endsection

<script>
document.addEventListener('alpine:init', () => {
  Alpine.data('teamFilter', (config) => ({
    team: config.team || [],
    afdeling: config.initial?.afdeling || '',
    dienst: config.initial?.dienst || '',
    s: config.initial?.s || '',

    get filtered() {
      const afd = (this.afdeling || '').trim();
      const dienstId = this.dienst ? parseInt(this.dienst, 10) : null;
      const q = (this.s || '').trim().toLowerCase();

      return this.team.filter(m => {
        const matchAfdeling = !afd || (m.afdelingen || []).includes(afd);
        const matchDienst = !dienstId || (m.diensten || []).includes(dienstId);
        const matchSearch = !q || (m.name || '').toLowerCase().includes(q);
        return matchAfdeling && matchDienst && matchSearch;
      });
    },

    syncUrl(push = true) {
      const p = new URLSearchParams();
      if (this.afdeling) p.set('afdeling', this.afdeling);
      if (this.dienst) p.set('dienst', this.dienst);
      if (this.s) p.set('s', this.s);

      const url = `${location.pathname}${p.toString() ? '?' + p.toString() : ''}`;
      push ? history.pushState({}, '', url) : history.replaceState({}, '', url);
    },

    reset() {
      this.afdeling = '';
      this.dienst = '';
      this.s = '';
      this.syncUrl();
    },
  }));
});
</script>