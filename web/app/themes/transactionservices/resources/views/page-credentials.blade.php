@extends('layouts.app')

@section('content')
    <header class="mb-8 text-center">
        <h1>Credentials</h1>
    </header>

    <div x-data="credentialsFilter()">

        {{-- Filters + Toggle --}}
        <div class="flex flex-wrap items-center justify-between gap-4 mb-8">
            <div class="flex flex-wrap gap-4 text-sm">
                <button @click="resetFilters()" class="btn-gray-outline">Reset</button>

                <select x-model="activeType" @change="currentPage = 1" class="btn-gray-outline">
                    <option value="">Selecteer Type</option>
                    <option value="aankoop">Aankoop</option>
                    <option value="verkoop">Verkoop</option>
                    <option value="financiering">Financiering</option>
                </select>

                <select x-model="activeSector" @change="currentPage = 1" class="btn-gray-outline">
                    <option value="">Selecteer Sector</option>
                    <option value="agriculture">Agriculture</option>
                    <option value="business-services">Business services</option>
                    <option value="education">Education</option>
                    <option value="financial-services">Financial services</option>
                    <option value="food-and-Consumer-goods">Food and Consumer goods</option>
                    <option value="healthcare">Healthcare</option>
                    <option value="industrial-services-Construction-Utilities">Industrial services & Construction & Utilities</option>
                    <option value="information-Technology">Information Technology</option>
                    <option value="leisure">Leisure</option>
                    <option value="logistics">Logistics</option>
                    <option value="media">Media</option>
                    <option value="production">Production</option>
                    <option value="software">Software</option>
                </select>
            </div>

            {{-- View toggle --}}
            <div class="flex gap-2">
                <button @click="view = 'grid'"
                        :class="view === 'grid' ? 'btn' : 'btn-gray-outline'"
                        title="Grid weergave">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                    </svg>
                </button>
                <button @click="view = 'list'"
                        :class="view === 'list' ? 'btn' : 'btn-gray-outline'"
                        title="Lijst weergave">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
            </div>
        </div>

        {{-- Grid view --}}
        <div x-show="view === 'grid'"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-1"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="grid md:grid-cols-3 gap-8">
            @foreach($credentials as $index => $credential)
                <div
                    x-show="isVisible({{ $index }}, '{{ $credential['type'] }}', '{{ $credential['sector'] }}')"
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 scale-95"
                    x-transition:enter-end="opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-200"
                    x-transition:leave-start="opacity-100 scale-100"
                    x-transition:leave-end="opacity-0 scale-95"
                >
                    <x-credential-card :credential="$credential" />
                </div>
            @endforeach
        </div>

        {{-- List view --}}
        <div x-show="view === 'list'"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-1"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="flex flex-col divide-y divide-slate-200">
            @foreach($credentials as $index => $credential)
                <div
                    x-show="isVisible({{ $index }}, '{{ $credential['type'] }}', '{{ $credential['sector'] }}')"
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100"
                    class="flex items-center gap-6 py-5"
                >
                    {{-- Logo 1 --}}
                    <div class="w-24 shrink-0">
                        @if($credential['logo1'])
                            <img src="{{ home_url('/app/uploads/' . $credential['logo1']) }}"
                                 alt="{{ $credential['partij1'] }}"
                                 class="max-h-10 max-w-[96px] w-auto object-contain">
                        @else
                            <span class="text-sm font-semibold text-gray-800">{{ $credential['partij1'] }}</span>
                        @endif
                    </div>

                    {{-- Omschrijving 1 --}}
                    <div class="flex-1">
                        <p class="text-sm font-medium text-gray-800">{{ $credential['omschrijving1'] }}</p>
                    </div>

                    {{-- Logo 2 --}}
                    <div class="w-24 shrink-0">
                        @if($credential['logo2'])
                            <img src="{{ home_url('/app/uploads/' . $credential['logo2']) }}"
                                 alt="{{ $credential['partij2'] }}"
                                 class="max-h-10 max-w-[96px] w-auto object-contain">
                        @elseif($credential['partij2'])
                            <span class="text-sm font-semibold text-gray-800">{{ $credential['partij2'] }}</span>
                        @endif
                    </div>

                    {{-- Omschrijving 2 --}}
                    <div class="flex-1">
                        <p class="text-sm text-gray-500">{{ $credential['omschrijving2'] }}</p>
                    </div>

                    {{-- Jaar --}}
                    <div class="w-12 shrink-0 text-right">
                        <span class="text-xs text-gray-400">{{ \Carbon\Carbon::parse($credential['datum'])->format('Y') }}</span>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Geen resultaten --}}
        <p x-show="noResults()" class="text-gray-500 mt-8">Geen credentials gevonden.</p>

        {{-- Pagination --}}
        <div x-show="totalPages() > 1" class="flex justify-center items-center gap-2 mt-12">
            <button @click="prevPage()" :disabled="currentPage === 1"
                    :class="currentPage === 1 ? 'opacity-40 cursor-not-allowed' : ''"
                    class="btn-gray-outline px-4 py-1.5">← Vorige</button>

            <template x-for="page in totalPages()" :key="page">
                <button @click="currentPage = page"
                        :class="currentPage === page ? 'btn px-4 py-1.5' : 'btn-gray-outline px-4 py-1.5'"
                        x-text="page"></button>
            </template>

            <button @click="nextPage()" :disabled="currentPage === totalPages()"
                    :class="currentPage === totalPages() ? 'opacity-40 cursor-not-allowed' : ''"
                    class="btn-gray-outline px-4 py-1.5">Volgende →</button>
        </div>

    </div>

    <script>
        function credentialsFilter() {
            return {
                activeType: '',
                activeSector: '',
                currentPage: 1,
                perPage: 24,
                view: 'grid',

                allCredentials: @json($credentials),

                filtered() {
                    return this.allCredentials.filter(c => {
                        const typeMatch = !this.activeType || c.type === this.activeType;
                        const sectorMatch = !this.activeSector || c.sector === this.activeSector;
                        return typeMatch && sectorMatch;
                    });
                },

                totalPages() {
                    return Math.ceil(this.filtered().length / this.perPage);
                },

                isVisible(index, type, sector) {
                    const typeMatch = !this.activeType || type === this.activeType;
                    const sectorMatch = !this.activeSector || sector === this.activeSector;
                    if (!typeMatch || !sectorMatch) return false;

                    const filteredIndexes = this.allCredentials
                        .map((c, i) => {
                            const tm = !this.activeType || c.type === this.activeType;
                            const sm = !this.activeSector || c.sector === this.activeSector;
                            return tm && sm ? i : -1;
                        })
                        .filter(i => i !== -1);

                    const positionInFiltered = filteredIndexes.indexOf(index);
                    const start = (this.currentPage - 1) * this.perPage;
                    const end = start + this.perPage;

                    return positionInFiltered >= start && positionInFiltered < end;
                },

                noResults() {
                    return this.filtered().length === 0;
                },

                prevPage() {
                    if (this.currentPage > 1) this.currentPage--;
                },

                nextPage() {
                    if (this.currentPage < this.totalPages()) this.currentPage++;
                },

                resetFilters() {
                    this.activeType = '';
                    this.activeSector = '';
                    this.currentPage = 1;
                }
            }
        }
    </script>
@endsection