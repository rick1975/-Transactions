<div class="grid gap-3 md:grid-cols-3 w-full md:w-auto">
      <select x-model="afdeling" @change="syncUrl()" class="border rounded px-4 py-2">
        <option value="">Alle afdelingen</option>
        @foreach($afdelingen as $afd)
          <option value="{{ $afd->slug }}">{{ $afd->name }}</option>
        @endforeach
      </select>

      <select x-model="dienst" @change="syncUrl()" class="border rounded px-4 py-2">
        <option value="">Alle specialismes</option>
        @foreach($diensten as $d)
          <option value="{{ $d->ID }}">{{ $d->post_title }}</option>
        @endforeach
      </select>

      <input
        type="text"
        x-model="s"
        placeholder="Zoek op naam…"
        class="border rounded px-4 py-2"
        @input.debounce.250ms="syncUrl(false)"
      />
    </div>

    <div class="flex gap-3">
      <button type="button" class="px-4 py-2 rounded border" @click="reset()">Reset</button>
    </div>
  </div>