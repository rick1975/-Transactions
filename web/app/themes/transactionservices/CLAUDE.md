# CLAUDE.md — Transaction Services Theme

WordPress thema gebouwd op [Sage (Roots)](https://roots.io/sage/) met Blade templates, Tailwind CSS, Alpine.js en Vite.

---

## Technische stack

- **Framework:** Sage / Acorn (Roots)
- **Templates:** Laravel Blade (`resources/views/`)
- **CSS:** Tailwind CSS v4
- **JS:** Alpine.js (interactie), Vite (bundler)
- **ACF:** Advanced Custom Fields Pro (veldgroepen deels via UI, deels via `app/setup.php`)

---

## Custom Post Types

Geregistreerd in `app/setup.php`:

| CPT | Slug | Archive |
|-----|------|---------|
| `dienst` | `/diensten/` | ja |
| `team` | `/team/` | ja |
| `locatie` | `/locaties/` | ja |

**Taxonomy:** `afdeling` (gekoppeld aan `team`, hierarchisch)

---

## ACF velden per CPT

### Teamlid (`team`)
| Veld | Type | Omschrijving |
|------|------|-------------|
| `team_functie` | text | Functietitel |
| `team_diensten` | relationship | Gekoppelde diensten (slaat dienst-IDs op als geserialiseerde array) |
| `team_mail` | email | E-mailadres |
| `team_telefoon` | text | Telefoonnummer (geregistreerd in `app/setup.php`) |
| `team_linkedin` | url | LinkedIn URL (geregistreerd in `app/setup.php`) |

### Dienst (`dienst`)
| Veld | Type | Omschrijving |
|------|------|-------------|
| `dienst_faq` | repeater | FAQ blok met `q` (vraag) en `a` (antwoord) |
| `dienst_contactpersonen` | relationship | Gekoppelde teamleden als contactpersoon (geregistreerd in `app/setup.php`) |
| `hero_image` | image | Hero afbeelding (optioneel) |
| `hero_title` | text | Hero titel (optioneel) |
| `hero_intro` | textarea | Hero intro tekst (optioneel) |

### Pagina / overige singular
| Veld | Type | Omschrijving |
|------|------|-------------|
| `hero_image` | image | Hero afbeelding |
| `hero_title` | text | Hero titel |
| `hero_intro` | textarea | Hero intro tekst |

---

## View Composers

Liggen in `app/View/Composers/`. Koppelen data aan Blade views:

| Composer | View | Data |
|----------|------|------|
| `SingleDienst` | `single-dienst` | title, content, thumbnail, faqs, **teamleden** |
| `ArchiveDienst` | `archive-dienst` | diensten (WP_Query), archive_title |
| `Team` | `archive-team` | team posts met filter op afdeling/dienst/naam |
| `SingleLocatie` | `single-locatie` | title, thumbnail, adres, telefoon, email, route |
| `ArchiveLocatie` | `archive-locatie` | locaties (WP_Query) |

---

## Relatie team ↔ diensten

De koppeling is **eenrichtingsverkeer**: teamleden slaan hun diensten op in `team_diensten`.

- **Archive team** (`archive-team.blade.php`): Alpine.js filtering op dienst via `team_diensten`
- **Single dienst** (`single-dienst.blade.php`): sidebar toont teamleden gekoppeld aan de dienst
- **Single dienst** contactpersonen: `get_field('dienst_contactpersonen', $dienstID)` geeft direct WP_Post-objecten terug (geen LIKE-query meer)

---

## Hero afbeelding logica (`sections/header.blade.php`)

| Context | Hero |
|---------|------|
| `single post` | geen hero |
| `single team` | altijd default afbeelding |
| `archive dienst / post / home` | altijd default afbeelding |
| `overige singular` | hero_image → post thumbnail → default |
| `overige` | default afbeelding |

Default afbeelding: `resources/images/drie-mensen-in-vergadering.avif`

---

## Logo in header

Het logo bestaat uit een SVG-icoon + sitenaam met:
- Oranje streep (3px, `#fd6400`) onder de sitenaam via absoluut gepositioneerd element
- Subtext: "powered by de Jong & Laan" (`text-[12px]`)

---

## Afbeeldingen (Vite assets)

| Bestand | Gebruik |
|---------|---------|
| `resources/images/drie-mensen-in-vergadering.avif` | Default hero |
| `resources/images/teamlid-fallback.avif` | Fallback teamlid foto |

Aanroepen via: `Vite::asset('resources/images/...')`

---

## CSS & Tailwind

### Setup
- **Tailwind CSS v4** — configuratie via `@theme` in `resources/css/app.css`, **niet** via `tailwind.config.js`
- `tailwind.config.js` bevat alleen `primary` kleur en `Poppins` font (legacy, v4 negeert dit grotendeels)
- Tailwind scant: `../views/**` en `../../app/**`

### Custom kleuren (`@theme` in app.css)

| Klasse | Hex | Gebruik |
|--------|-----|---------|
| `text/bg/border-primary` | `#fd6400` | Oranje, hoofdkleur |
| `text/bg/border-primary-dark` | `#e35a00` | Oranje hover |
| `text/bg/border-trans-orange` | `#fd6400` | Alias voor primary |
| `text/bg/border-trans-green` | `#c1e5e1` | Mintgroen, accentkleur |
| `text/bg/border-trans-green-dark` | `#addbd6` | Mintgroen hover |
| `text/bg/border-trans-yellow` | `#ffd42f` | Geel, CTA-kleur |
| `text/bg/border-trans-yellowlight` | `#ebcc91` | Geel licht |

### Custom component classes (app.css)

| Klasse | Omschrijving |
|--------|-------------|
| `.btn` | Gele knop (`bg-trans-yellow`), hover → mintgroen + schaduw |
| `.btn-outline` | Outline knop in primary oranje |
| `.card` | Witte kaart met shadow, hover → shadow-lg |
| `.section` | Sectie padding (`py-12 md:py-16 lg:py-20`) |
| `.container` | Max-breedte wrapper (`max-w-7xl`, responsive padding) |

### Typografie (base styles)
- **Font:** Poppins (self-hosted woff2, gewichten 400/500/600/700/900 in `resources/fonts/`)
- `h1` → `text-3xl md:text-[2.6rem] mb-5`
- `h2` → `text-3xl md:text-4xl mb-5`
- `h3` → `text-2xl md:text-3xl mb-4`
- Links: `text-primary`, hover `text-primary-dark`, geen underline
- `[x-cloak]` → `display: none` (voor Alpine.js)

### Derde partijen
- **Gravity Forms:** formuliervelden gestyled via `.gform_wrapper` selectors in `app.css`
- **TranslatePress:** `.trp-shortcode-switcher` gestyled voor header integratie
- **Editor styles:** `resources/css/editor.css` (WordPress blok-editor)

---

## Navigatie

De header ondersteunt:
- Reguliere WordPress-menu children (dropdown)
- Automatische dropdown voor "Diensten" gevuld met CPT `dienst` posts
- Mobielmenu met Alpine.js toggle
- TranslatePress taalswitcher (`[language-switcher]` shortcode)

---

## Belangrijke implementatienoten

### Vite::asset() in PHP vs Blade
`Vite::asset()` werkt **alleen** in Blade templates, niet in PHP-klassen (View Composers).
In Composers: gebruik `null` teruggeven en de fallback in de Blade afhandelen via `??`.

### Thumbnail buiten de WordPress loop
`has_post_thumbnail()` en `get_the_post_thumbnail_url()` zijn onbetrouwbaar in Blade buiten de main loop.
**Oplossing:** haal thumbnail-URL's op in de View Composer via `get_the_post_thumbnail_url($id, 'size')` en geef `null` terug als er geen thumbnail is.

### ACF relationship veld query
ACF slaat relationship-veld waarden op als PHP-geserialiseerde array (bijv. `a:1:{i:0;s:2:"42";}`).
De `LIKE`-vergelijking werkt correct: WordPress voegt automatisch `%` wildcards toe, waardoor `'"42"'` matcht op `"42"` in de geserialiseerde string.

---

## Layout breedte per context (`layouts/app.blade.php`)

| Context | Max-breedte klasse |
|---------|--------------------|
| `is_post_type_archive('dienst')` | `lg:max-w-[100rem]` |
| `is_singular('dienst')` | `lg:max-w-6xl` |
| `is_singular/archive('team')`, `is_singular/archive('locatie')`, `is_home()`, `is_category('kennisbank')` | `lg:max-w-6xl xxl:px-0` |
| overige pagina's | `md:max-w-[768px] lg:max-w-4xl` |

---

## Alpine.js patterns

### Slide-over drawer (single-dienst sidebar)
Teamleden in sidebar openen een slide-over drawer bij klikken. Geen page navigation.

- `x-data` staat op `<aside>` met: `open: false`, `selected: null`, `teamleden` (JSON array via `@js()`), `fallback` (URL)
- `x-init="open = false"` is vereist om te garanderen dat de drawer gesloten start
- `selected: null` (niet `{}`) voorkomt dat Alpine het als truthy interpreteert
- Teamleden-data wordt via `@js($teamleden)` server-side ingesloten als JS-array
- Knoppen selecteren via index: `selected = teamleden[{{ $loop->index }}]; open = true`
- Drawer sluit via close-knop, backdrop-klik of Escape: `@keydown.escape.window="open = false"`
- Fallback-afbeelding in Alpine: `selected.thumbnail || fallback` (fallback als JS-variabele, niet als Blade in `:src`)
- `x-cloak` op backdrop én drawer (CSS: `[x-cloak] { display: none !important }` staat in `app.css`)
- **Nooit** `style="display:none"` op elementen met `x-transition` — dit conflicteert met Alpine's show/hide systeem

### Excerpt in Alpine drawer
- `x-html` gebruiken (niet `x-text`) voor velden met HTML-entiteiten zoals `&#8217;` of `&hellip;`
- Excerpt-lengte: `wp_trim_words()` met 100 woorden in de Composer; gebruikt handmatige excerpt bij voorkeur, valt terug op post content (`strip_tags`)

---

## Gewijzigde bestanden (sessie feb 2025)

| Bestand | Wijziging |
|---------|-----------|
| `resources/views/sections/header.blade.php` | Hero: `is_singular('team')` valt terug op default; logo oranje streep + "powered by de Jong & Laan" |
| `resources/views/single-team.blade.php` | Foto: native `get_the_post_thumbnail()` met srcset, `fetchpriority="high"`, `loading="eager"` |
| `app/View/Composers/SingleDienst.php` | `teamleden()` toegevoegd: query op `team_diensten` LIKE, retourneert array met id/title/permalink/thumbnail/functie/mail/excerpt (100 woorden via `wp_trim_words`) |
| `resources/views/single-dienst.blade.php` | Sidebar (`lg:grid-cols-3`) met Alpine.js slide-over drawer; foto compact naast naam/functie; `x-html` voor excerpt; `x-init="open = false"` fix |
| `CLAUDE.md` | Aangemaakt en bijgewerkt |

## Gewijzigde bestanden (sessie feb 2026)

| Bestand | Wijziging |
|---------|-----------|
| `resources/views/single-team.blade.php` | Contactgegevens (mail, telefoon, LinkedIn) toegevoegd met gestylede icoon-links; zelfde opmaak als sidebar in `single-dienst` |

---

## Veldnamen in `single-team.blade.php`

Let op: de veldnamen in de Blade template wijken af van de ACF-registratie in `CLAUDE.md`:

| ACF-veldnaam (werkelijk) | Gebruikt in template | Opmerking |
|--------------------------|----------------------|-----------|
| `team_functie` | `get_field('team_functie')` | Bevestigd via Composer |
| `team_mail` | `get_field('team_mail')` | Bevestigd via Composer |
| `team_tel` | `get_field('team_tel')` | **Niet** `team_telefoon` — bevestigd via Composer |
| `team_linkedin` | `get_field('team_linkedin')` | Bevestigd via Composer |
