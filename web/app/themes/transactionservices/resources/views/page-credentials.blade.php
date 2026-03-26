@extends('layouts.app')

@section('content')
    <header class="mb-8 text-center">
        <h1>Credentials</h1>
    </header>

    <div x-data="credentialsFilter()">

        {{-- Filters --}}
        <div class="flex flex-wrap gap-4 mb-8 text-sm">
            <button @click="resetFilters()" class="btn btn-gray">Reset</button>

            <select x-model="activeType" @change="currentPage = 1" class="border border-gray-300 rounded px-4 py-2">
                <option value="">Selecteer Type</option>
                <option value="aankoop">Aankoop</option>
                <option value="verkoop">Verkoop</option>
                <option value="financiering">Financiering</option>
            </select>

            <select x-model="activeSector" @change="currentPage = 1" class="border border-gray-300 rounded px-4 py-2">
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

        {{-- Cards --}}
        <div class="grid md:grid-cols-3 gap-6">
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

        {{-- Geen resultaten --}}
        <p x-show="filteredCount() === 0" class="text-gray-500 mt-8">Geen credentials gevonden.</p>

        {{-- Pagination --}}
        <div x-show="totalPages() > 1" class="flex justify-center items-center gap-2 mt-12">
            <button
                @click="prevPage()"
                :disabled="currentPage === 1"
                :class="currentPage === 1 ? 'opacity-40 cursor-not-allowed' : ''"
                class="btn btn-gray-outline">
                ← Vorige
            </button>

            <template x-for="page in totalPages()" :key="page">
                <button
                    @click="currentPage = page"
                    :class="currentPage === page ? 'btn btn-primary' : 'btn btn-gray-outline'"
                    x-text="page">
                </button>
            </template>

            <button
                @click="nextPage()"
                :disabled="currentPage === totalPages()"
                :class="currentPage === totalPages() ? 'opacity-40 cursor-not-allowed' : ''"
                class="btn btn-gray-outline">
                Volgende →
            </button>
        </div>

    </div>

    <script>
        function credentialsFilter() {
            return {
                activeType: '',
                activeSector: '',
                currentPage: 1,
                perPage: 24,

                allCredentials: @json($credentials),

                filtered() {
                    return this.allCredentials.filter(c => {
                        const typeMatch = !this.activeType || c.type === this.activeType;
                        const sectorMatch = !this.activeSector || c.sector === this.activeSector;
                        return typeMatch && sectorMatch;
                    });
                },

                filteredCount() {
                    return this.filtered().length;
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