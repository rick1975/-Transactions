# Project context

## Stack
- WordPress / WooCommerce
- Sage 11 (Roots) met Bedrock
- Acorn v4 (Laravel integratie)
- Blade templates in `resources/views/`
- Tailwind CSS v4
- Vite v6 (via @roots/vite-plugin + laravel-vite-plugin)
- Alpine.js v3 + @alpinejs/intersect
- PHP 8.2+
- Node >= 20

## Workflow
- Git via GitHub, na elke wijziging commit + push
- Build: `yarn build` / dev: `yarn dev`

## Conventies
- Blade templates voor alle views
- Controllers via Acorn
- Alpine.js v3 voor interactiviteit (@alpinejs/intersect beschikbaar)

## Git
- Nooit Co-Authored-By regels toevoegen aan commits
- Commit messages bevatten alleen een beschrijvende tekst, geen attributie metadata

## CSS & Styling
- Styling via Tailwind CSS v4
- Gebruik bij voorkeur `@apply` in plaats van inline utility classes
- CSS bestanden staan in `resources/css/` (hoofdbestand: `app.css`)
- Vóór een CSS fix: eerst diagnose uitleggen, geen bestanden aanpassen totdat bevestigd
- Na wijzigingen `yarn build` draaien om te controleren

## Instructies voor Claude
- Geef antwoorden in het Nederlands
- Houd rekening met de Sage/Bedrock structuur, geen standaard WordPress aanpak
- Geef alleen code als ik dat vraag, anders eerst uitleg
- Stel voor om te committen na afgeronde wijzigingen