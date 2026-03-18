#!/bin/bash

# ============================================
# Studio-Pit WordPress Project Setup Script
# Automated Bedrock + Sage + Acorn installer
# ============================================

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Whitelabel Configuration
COMPANY_NAME="Studio-Pit"
THEME_AUTHOR="Pit Development"
THEME_AUTHOR_URI="https://studio-pit.nl"
DEFAULT_PLUGINS=("autodescription" "w3-total-cache")
DEFAULT_ADMIN_EMAIL="rick@studio-pit.nl"
DEFAULT_DB_HOST="127.0.0.1"

# ============================================
# Functions
# ============================================

print_header() {
    echo -e "${BLUE}========================================${NC}"
    echo -e "${BLUE}$1${NC}"
    echo -e "${BLUE}========================================${NC}"
}

print_success() {
    echo -e "${GREEN}✓ $1${NC}"
}

print_error() {
    echo -e "${RED}✗ $1${NC}"
}

print_info() {
    echo -e "${YELLOW}ℹ $1${NC}"
}

check_command() {
    if ! command -v $1 &> /dev/null; then
        print_error "$1 is niet geïnstalleerd"
        return 1
    fi
    return 0
}

# Generate secure password (WITHOUT problematic characters)
generate_password() {
    # Generate 32 character password without %, /, \, quotes (causes issues)
    # Allowed: A-Z, a-z, 0-9, !@#$^&*_=+
    LC_ALL=C tr -dc 'A-Za-z0-9!@#$^&*_=+' < /dev/urandom | head -c 32
}

# ============================================
# User Input
# ============================================

print_header "Studio-Pit WordPress Project Setup"

read -p "Project naam (bijv. mijn-website): " PROJECT_NAME
if [ -z "$PROJECT_NAME" ]; then
    print_error "Project naam is verplicht"
    exit 1
fi

read -p "Theme naam (bijv. mijn-theme): " THEME_NAME
if [ -z "$THEME_NAME" ]; then
    print_error "Theme naam is verplicht"
    exit 1
fi

read -p "Database naam: " DB_NAME
if [ -z "$DB_NAME" ]; then
    print_error "Database naam is verplicht"
    exit 1
fi

read -p "Database gebruiker: " DB_USER
if [ -z "$DB_USER" ]; then
    print_error "Database gebruiker is verplicht"
    exit 1
fi

read -sp "Database wachtwoord: " DB_PASSWORD
echo ""
if [ -z "$DB_PASSWORD" ]; then
    print_error "Database wachtwoord is verplicht"
    exit 1
fi

read -p "Database host [${DEFAULT_DB_HOST}]: " DB_HOST
DB_HOST=${DB_HOST:-$DEFAULT_DB_HOST}

read -p "WordPress admin email [${DEFAULT_ADMIN_EMAIL}]: " ADMIN_EMAIL
ADMIN_EMAIL=${ADMIN_EMAIL:-$DEFAULT_ADMIN_EMAIL}

# Generate admin password
ADMIN_PASSWORD=$(generate_password)

read -p "WordPress URL (bijv. https://example.com): " WP_HOME
if [ -z "$WP_HOME" ]; then
    print_error "WordPress URL is verplicht"
    exit 1
fi

# Admin username
ADMIN_USERNAME="StudioPit-Admin-${PROJECT_NAME}"

# ============================================
# Color Configuration
# ============================================

echo ""
print_header "Kleur configuratie"
echo ""
print_info "Primary color wordt gebruikt voor:"
echo "  - Login button"
echo "  - Footer achtergrond"
echo "  - Links en accenten"
echo "  - Tailwind utilities (bg-primary, text-primary, etc.)"
echo ""

read -p "Primary kleur (hex, bijv. #eca319) [#eca319]: " PRIMARY_COLOR
PRIMARY_COLOR=${PRIMARY_COLOR:-#eca319}

# Validate hex color format
if [[ ! $PRIMARY_COLOR =~ ^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$ ]]; then
    print_error "Ongeldige hex kleur. Gebruik formaat: #eca319"
    PRIMARY_COLOR="#eca319"
    print_info "Fallback naar default: ${PRIMARY_COLOR}"
fi

# Calculate darker variant (hover state - 10% darker)
PRIMARY_DARK=$(printf "#%02x%02x%02x" \
    $(( 16#${PRIMARY_COLOR:1:2} * 90 / 100 )) \
    $(( 16#${PRIMARY_COLOR:3:2} * 90 / 100 )) \
    $(( 16#${PRIMARY_COLOR:5:2} * 90 / 100 )))

# Calculate lighter variant (15% lighter)
PRIMARY_LIGHT=$(printf "#%02x%02x%02x" \
    $(( (16#${PRIMARY_COLOR:1:2} + (255 - 16#${PRIMARY_COLOR:1:2}) * 15 / 100) )) \
    $(( (16#${PRIMARY_COLOR:3:2} + (255 - 16#${PRIMARY_COLOR:3:2}) * 15 / 100) )) \
    $(( (16#${PRIMARY_COLOR:5:2} + (255 - 16#${PRIMARY_COLOR:5:2}) * 15 / 100) )))

print_success "Primary color: ${PRIMARY_COLOR}"
print_info "Variants: dark=${PRIMARY_DARK}, light=${PRIMARY_LIGHT}"

echo ""
print_header "BELANGRIJK - Admin Credentials"
echo ""
print_success "Admin username: ${ADMIN_USERNAME}"
print_success "Admin password: ${ADMIN_PASSWORD}"
echo ""
print_info "⚠️  KOPIEER DIT WACHTWOORD NU! ⚠️"
echo ""
read -p "Druk op Enter om door te gaan nadat je het wachtwoord hebt gekopieerd..."

echo ""
print_info "Samenvatting:"
echo "  - Project: ${PROJECT_NAME}"
echo "  - Theme: ${THEME_NAME}"
echo "  - Database: ${DB_NAME} @ ${DB_HOST}"
echo "  - Admin: ${ADMIN_USERNAME}"
echo "  - Email: ${ADMIN_EMAIL}"
echo "  - URL: ${WP_HOME}"
echo "  - Primary color: ${PRIMARY_COLOR}"
echo ""
read -p "Doorgaan met installatie? (y/n): " CONFIRM
if [ "$CONFIRM" != "y" ]; then
    print_info "Installatie geannuleerd"
    exit 1
fi

# Save the project root directory
PROJECT_ROOT=$(pwd)

# ============================================
# Pre-installation Checks
# ============================================

print_header "Controleren vereisten"

check_command composer || exit 1
print_success "Composer gevonden"

check_command php || exit 1
print_success "PHP gevonden"

# Test database connection
print_info "Testen database connectie..."
if mysql -h"${DB_HOST}" -u"${DB_USER}" -p"${DB_PASSWORD}" -e "USE ${DB_NAME};" 2>/dev/null; then
    print_success "Database connectie succesvol"
else
    print_error "Kan niet verbinden met database. Check je credentials."
    read -p "Toch doorgaan? (y/n): " CONTINUE
    if [ "$CONTINUE" != "y" ]; then
        exit 1
    fi
fi

# ============================================
# Install Bedrock
# ============================================

print_header "Installeren Bedrock"

# Installeer Bedrock in tijdelijke directory
TEMP_DIR="bedrock_temp_$$"

print_info "Downloaden Bedrock..."
composer create-project roots/bedrock ${TEMP_DIR} --quiet

if [ $? -eq 0 ]; then
    print_success "Bedrock gedownload"
    
    # Verplaats alle bestanden (inclusief hidden files)
    print_info "Installeren bestanden..."
    shopt -s dotglob
    mv ${TEMP_DIR}/* . 2>/dev/null
    rmdir ${TEMP_DIR}
    
    print_success "Bedrock geïnstalleerd"
else
    print_error "Bedrock installatie mislukt"
    rm -rf ${TEMP_DIR}
    exit 1
fi

# ============================================
# Configure Environment
# ============================================

print_header "Configureren environment"

cat > .env << EOF
DB_NAME='${DB_NAME}'
DB_USER='${DB_USER}'
DB_PASSWORD='${DB_PASSWORD}'
DB_HOST='${DB_HOST}'

WP_ENV='development'
WP_HOME='${WP_HOME}'
WP_SITEURL="\${WP_HOME}/wp"

# Generate unique salts at: https://roots.io/salts.html
AUTH_KEY='$(openssl rand -base64 64 | tr -d '\n')'
SECURE_AUTH_KEY='$(openssl rand -base64 64 | tr -d '\n')'
LOGGED_IN_KEY='$(openssl rand -base64 64 | tr -d '\n')'
NONCE_KEY='$(openssl rand -base64 64 | tr -d '\n')'
AUTH_SALT='$(openssl rand -base64 64 | tr -d '\n')'
SECURE_AUTH_SALT='$(openssl rand -base64 64 | tr -d '\n')'
LOGGED_IN_SALT='$(openssl rand -base64 64 | tr -d '\n')'
NONCE_SALT='$(openssl rand -base64 64 | tr -d '\n')'
EOF

print_success "Environment geconfigureerd"

# ============================================
# Install Acorn & Prettify
# ============================================

print_header "Installeren Acorn & Prettify"

print_info "Installeren Acorn..."
composer require roots/acorn --quiet
if [ $? -eq 0 ]; then
    print_success "Acorn geïnstalleerd"
else
    print_error "Acorn installatie mislukt"
    exit 1
fi

print_info "Installeren Acorn Prettify..."
composer require roots/acorn-prettify --quiet
if [ $? -eq 0 ]; then
    print_success "Acorn Prettify geïnstalleerd"
else
    print_error "Acorn Prettify installatie mislukt"
fi

# ============================================
# Install Sage Theme
# ============================================

print_header "Installeren Sage Theme"

cd web/app/themes || exit 1
composer create-project roots/sage ${THEME_NAME} --quiet
if [ $? -eq 0 ]; then
    print_success "Sage theme geïnstalleerd"
else
    print_error "Sage installatie mislukt"
    exit 1
fi

cd ${THEME_NAME}

# Update theme style.css with whitelabel info
cat > style.css << EOF
/*
Theme Name:         ${THEME_NAME}
Theme URI:          ${THEME_AUTHOR_URI}
Description:        Custom WordPress theme built with Sage
Version:            1.0.0
Author:             ${THEME_AUTHOR}
Author URI:         ${THEME_AUTHOR_URI}
Text Domain:        ${THEME_NAME}
*/
EOF

print_success "Theme geconfigureerd met ${COMPANY_NAME} branding"

# ============================================
# Install Navi (Navigation)
# ============================================

print_header "Installeren Navi (navigatie library)"

print_info "Installeren Log1x/Navi..."
composer require log1x/navi --quiet
if [ $? -eq 0 ]; then
    print_success "Navi geïnstalleerd"
else
    print_error "Navi installatie mislukt"
fi

# ============================================
# Modify app.blade.php - Add Google Fonts
# ============================================

print_info "Toevoegen Google Fonts aan app.blade.php..."

APP_BLADE_PATH="${PROJECT_ROOT}/web/app/themes/${THEME_NAME}/resources/views/layouts/app.blade.php"

if [ -f "$APP_BLADE_PATH" ]; then
    sed -i '/<head>/a\
    <link rel="preconnect" href="https://fonts.googleapis.com">\
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>\
    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">' "$APP_BLADE_PATH"
    
    print_success "Google Fonts (Poppins) toegevoegd aan app.blade.php"
else
    print_error "app.blade.php niet gevonden op: $APP_BLADE_PATH"
fi

# ============================================
# Update app.blade.php main classes
# ============================================

print_info "Updaten app.blade.php main classes..."

if [ -f "$APP_BLADE_PATH" ]; then
    sed -i 's/<main id="main" class="main">/<main id="main" class="main mx-auto max-w-4xl px-6 py-10 md:py-16">/' "$APP_BLADE_PATH"
    print_success "app.blade.php main classes bijgewerkt"
else
    print_error "app.blade.php niet gevonden"
fi

# ============================================
# Configure Tailwind CSS app.css
# ============================================

print_header "Configureren Tailwind CSS"

cd ${PROJECT_ROOT}/web/app/themes/${THEME_NAME}

print_info "Aanmaken custom app.css met primary color..."

if [ -f "resources/css/app.css" ]; then
    cp resources/css/app.css resources/css/app.css.backup
fi

cat > resources/css/app.css << CSSEOF
@import "tailwindcss" theme(static);
@source "../views/";
@source "../../app/";

html { scroll-behavior: smooth; }

@layer base {
  :root {
    --primary: ${PRIMARY_COLOR};
    --primary-dark: ${PRIMARY_DARK};
    --primary-light: ${PRIMARY_LIGHT};
    --text-dark: #1f2937;
    --text-light: #6b7280;
    --gray-50: #f9fafb;
    --gray-100: #f3f4f6;
    --gray-200: #e5e7eb;
  }

  body {
    @apply bg-white text-gray-700 antialiased;
    font-family: 'Poppins', sans-serif;
    font-weight: 400;
    line-height: 1.75;
    font-size: 1rem;
  }

  /* Typography */
  h1, h2, h3, h4, h5, h6 {
    @apply font-bold text-pretty text-gray-800 leading-tight;
  }

  h1 { @apply text-4xl md:text-5xl mb-6; }
  h2 { @apply text-3xl md:text-4xl mb-5; }
  h3 { @apply text-2xl md:text-3xl mb-4; }
  h4 { @apply text-xl md:text-2xl mb-3 font-semibold; }
  h5 { @apply text-lg md:text-xl mb-2 font-semibold; }
  h6 { @apply text-base md:text-lg mb-2 font-semibold; }

  p {
    @apply mb-4 text-gray-700;
  }

  /* Links */
  a {
    @apply no-underline transition-colors duration-200;
    color: var(--primary);
  }

  a:hover {
    color: var(--primary-dark);
  }

  a:where(:not(.wp-element-button)) {
    text-decoration: none !important;
  }

  /* Lists - Remove default list styles */
  ul, ol {
    @apply mb-4 ml-0;
    list-style: none;
  }

  li {
    @apply mb-1;
  }

  /* Navigation menus - override margin and list style */
  nav ul,
  .footer ul {
    @apply mb-0;
    list-style: none;
  }

  nav li,
  .footer li {
    @apply mb-0;
  }

  /* Blockquotes */
  blockquote {
    @apply border-l-4 pl-4 italic my-4;
    border-color: var(--primary);
  }

  /* Images */
  img {
    @apply max-w-full h-auto;
  }

  /* Buttons */
  .btn {
    @apply inline-block px-6 py-3 rounded-lg font-medium transition-all duration-200;
    background-color: var(--primary);
    color: white;
  }

  .btn:hover {
    background-color: var(--primary-dark);
    transform: translateY(-1px);
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
  }

  .btn-outline {
    @apply border-2 bg-transparent;
    border-color: var(--primary);
    color: var(--primary);
  }

  .btn-outline:hover {
    background-color: var(--primary);
    color: white;
  }
}

@layer components {
  /* Container */
  .container {
    @apply mx-auto px-4 sm:px-6 lg:px-8 max-w-7xl;
  }

  /* Section spacing */
  .section {
    @apply py-12 md:py-16 lg:py-20;
  }

  /* Footer */
  .footer {
    @apply py-12 md:py-16;
    background-color: var(--primary);
    color: white;
  }

  .footer a {
    @apply text-white hover:text-gray-100;
  }

  .footer h3 {
    @apply text-white mb-4;
  }

  .footer p {
    @apply text-white;
  }

  /* Card */
  .card {
    @apply bg-white rounded-lg shadow-md p-6 transition-shadow duration-200;
  }

  .card:hover {
    @apply shadow-lg;
  }
}

@layer utilities {
  .text-balance {
    text-wrap: balance;
  }

  .text-pretty {
    text-wrap: pretty;
  }
}
CSSEOF

print_success "Tailwind app.css geconfigureerd met primary color ${PRIMARY_COLOR}"

# ============================================
# Create editor.css
# ============================================

print_info "Aanmaken editor.css met Poppins en typography..."

cat > resources/css/editor.css << CSSEOF
@import url("https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap");

body {
    @apply bg-white text-gray-700 antialiased;
    font-family: 'Poppins', sans-serif;
    font-weight: 400;
    line-height: 1.75;
    font-size: 1rem;
}

h1, h2, h3, h4, h5, h6 {
    @apply font-bold text-pretty text-gray-800 leading-tight;
}

h1 { @apply text-4xl md:text-5xl mb-6; }
h2 { @apply text-3xl md:text-4xl mb-5; }
h3 { @apply text-2xl md:text-3xl mb-4; }
h4 { @apply text-xl md:text-2xl mb-3 font-semibold; }
h5 { @apply text-lg md:text-xl mb-2 font-semibold; }
h6 { @apply text-base md:text-lg mb-2 font-semibold; }

p {
    @apply mb-4 text-gray-700;
}
CSSEOF

print_success "editor.css aangemaakt met consistente typography"

# ============================================
# Update Tailwind Config
# ============================================

print_info "Updaten tailwind.config.js..."

if [ -f "tailwind.config.js" ]; then
    cp tailwind.config.js tailwind.config.js.backup
fi

cat > tailwind.config.js << JSEOF
/** @type {import('tailwindcss').Config} config */
const config = {
  content: ['./app/**/*.php', './resources/**/*.{php,vue,js}'],
  theme: {
    extend: {
      colors: {
        primary: {
          DEFAULT: '${PRIMARY_COLOR}',
          dark: '${PRIMARY_DARK}',
          light: '${PRIMARY_LIGHT}',
        },
      },
      fontFamily: {
        sans: ['Poppins', 'sans-serif'],
      },
    },
  },
  plugins: [],
};

export default config;
JSEOF

print_success "Tailwind config bijgewerkt met primary color"

# ============================================
# Configure Custom Login Page
# ============================================

print_header "Configureren custom login page"

cd ${PROJECT_ROOT}/web/app/themes/${THEME_NAME}

print_info "Aanmaken login.css met primary color..."
mkdir -p resources/css

cat > resources/css/login.css << CSSEOF
body.login { 
    background-color: #F9FAFB; 
}

body {
    font-family: 'Poppins', sans-serif;
    font-weight: 400;
    line-height: 1.75;
    font-size: 1rem;
    color: #374151;
}

h1, h2, h3, h4, h5, h6 {
    font-weight: 700;
    color: #1f2937;
    line-height: 1.2;
}

h1 { font-size: 2.25rem; margin-bottom: 1.5rem; }
h2 { font-size: 1.875rem; margin-bottom: 1.25rem; }
h3 { font-size: 1.5rem; margin-bottom: 1rem; }
h4 { font-size: 1.25rem; margin-bottom: 0.75rem; font-weight: 600; }
h5 { font-size: 1.125rem; margin-bottom: 0.5rem; font-weight: 600; }
h6 { font-size: 1rem; margin-bottom: 0.5rem; font-weight: 600; }

p {
    margin-bottom: 1rem;
    color: #374151;
}

@media (min-width: 992px) {
    #login {
        width: 620px !important;
    }
    
    h1 { font-size: 3rem; }
    h2 { font-size: 2.25rem; }
}

.login h1 a {
    background-image: none !important;
    text-indent: 0 !important;
    width: auto !important;
    height: auto !important;
    font-weight: 700 !important;
    color: #111827 !important;
    line-height: 1.2 !important;
    font-size: 0 !important;
}

.login h1 a::before {
    font-size: 32px !important;
    display: block !important;
}

#loginform { 
    border-radius: 6px; 
    box-shadow: 0 1px 2px 0 rgba(0,0,0,0.05); 
    border: 1px solid #E5E7EB; 
    padding: 46px 34px;
}

.wp-core-ui .button-primary {
    background-color: ${PRIMARY_COLOR} !important; 
    border-color: ${PRIMARY_COLOR} !important;
}

.wp-core-ui .button-primary:hover,
.wp-core-ui .button-primary:focus {
    background-color: ${PRIMARY_DARK} !important;
    border-color: ${PRIMARY_DARK} !important;
}

#backtoblog,
.privacy-policy-page-link,
.language-switcher {
    display: none !important; 
}
CSSEOF

if [ -f "resources/css/login.css" ]; then
    print_success "login.css aangemaakt met primary color ${PRIMARY_COLOR}"
else
    print_error "login.css niet correct aangemaakt!"
fi

# Update vite.config
print_info "Updaten vite config..."

VITE_CONFIG=""
if [ -f "vite.config.js" ]; then
    VITE_CONFIG="vite.config.js"
elif [ -f "vite.config.ts" ]; then
    VITE_CONFIG="vite.config.ts"
fi

if [ -n "$VITE_CONFIG" ]; then
    print_info "Gevonden: ${VITE_CONFIG}"
    
    if grep -q "resources/css/login.css" "$VITE_CONFIG"; then
        print_success "login.css al aanwezig in ${VITE_CONFIG}"
    else
        cp "$VITE_CONFIG" "${VITE_CONFIG}.backup"
        
        if grep -q "editor.css" "$VITE_CONFIG"; then
            sed -i "/editor\.css/a\        'resources/css/login.css'," "$VITE_CONFIG"
            
            if grep -q "resources/css/login.css" "$VITE_CONFIG"; then
                print_success "login.css toegevoegd aan ${VITE_CONFIG}"
                rm "${VITE_CONFIG}.backup"
            else
                mv "${VITE_CONFIG}.backup" "$VITE_CONFIG"
                
                awk '
                    /editor\.css/ {
                        print $0
                        print "        '"'"'resources/css/login.css'"'"',"
                        next
                    }
                    {print}
                ' "$VITE_CONFIG" > "${VITE_CONFIG}.tmp"
                mv "${VITE_CONFIG}.tmp" "$VITE_CONFIG"
                
                if grep -q "resources/css/login.css" "$VITE_CONFIG"; then
                    print_success "login.css toegevoegd (via awk)"
                    rm "${VITE_CONFIG}.backup"
                else
                    print_error "Kon login.css niet automatisch toevoegen"
                    print_info "Voeg handmatig toe aan input array in ${VITE_CONFIG}:"
                    print_info "  'resources/css/login.css',"
                fi
            fi
        else
            print_error "Kon editor.css niet vinden in ${VITE_CONFIG}"
            print_info "Voeg handmatig 'resources/css/login.css' toe aan input array"
        fi
    fi
else
    print_error "Geen vite config gevonden!"
fi

# Add login page functions to setup.php
print_info "Toevoegen login page functies aan setup.php..."
cat >> app/setup.php << 'PHPEOF'

/**
 * Custom Login Page Styling
 */
add_action('login_enqueue_scripts', function () {
    $manifest_path = get_theme_file_path('public/build/manifest.json');
    if (!file_exists($manifest_path)) {
        return;
    }

    $manifest = json_decode(file_get_contents($manifest_path), true);
    $entry = 'resources/css/login.css';

    if (empty($manifest[$entry]['file'])) {
        return;
    }

    $href = get_theme_file_uri('public/build/' . $manifest[$entry]['file']);
    echo '<link rel="stylesheet" href="' . esc_url($href) . '" />';
});

/**
 * Custom Login Logo URL
 */
add_filter('login_headerurl', function () {
    return home_url();
});

/**
 * Custom Login Logo Title
 */
add_filter('login_headertext', function () {
    return get_bloginfo('name');
});

/**
 * Replace WordPress Logo with Site Name (CSS)
 */
add_action('login_head', function () {
    ?>
    <style>
        .login h1 a {
            font-size: 0 !important;
        }
        .login h1 a::before {
            content: "<?php echo esc_js(get_bloginfo('name')); ?>";
            font-size: 32px !important;
            display: block !important;
        }
    </style>
    <?php
});
PHPEOF

print_success "Login page functies toegevoegd aan setup.php"

# ============================================
# Add Custom Filters
# ============================================

print_info "Toevoegen custom filters (excerpt length + archive titles)..."

cat >> app/filters.php << 'PHPEOF'

/**
 * Change excerpt length to 18 words
 */
add_filter('excerpt_length', function () {
    return 18;
});

/**
 * Remove archive title prefixes (Category:, Tag:)
 */
add_filter('get_the_archive_title', function ($title) {
    if (is_category()) {
        $title = single_cat_title('', false);
    } elseif (is_tag()) {
        $title = single_tag_title('', false);
    }
    return $title;
});
PHPEOF

print_success "Custom filters toegevoegd aan filters.php"

# ============================================
# Update 404.blade.php
# ============================================

print_info "Updaten 404.blade.php met Nederlandse tekst..."

cat > resources/views/404.blade.php << 'BLADEEOF'
@extends('layouts.app')

@section('content')
  @include('partials.page-header')

  @if (! have_posts())
    <div class="mb-2">
      {!! __('Sorry, de pagina die je zoekt bestaat helaas niet.', 'sage') !!}
    </div>

    <div class="mt-6">
      {!! get_search_form(false) !!}
    </div>
  @endif
@endsection
BLADEEOF

print_success "404.blade.php bijgewerkt met Nederlandse tekst"

# ============================================
# Create Header Composer for Navi
# ============================================

print_header "Configureren Navi navigatie"

print_info "Aanmaken Header Composer..."

mkdir -p app/View/Composers

cat > app/View/Composers/Header.php << 'PHPEOF'
<?php

namespace App\View\Composers;

use Roots\Acorn\View\Composer;
use Log1x\Navi\Navi;

class Header extends Composer
{
    protected static $views = [
        'sections.header',
    ];

    public function with()
    {
        return [
            'navigation' => $this->navigation(),
        ];
    }

    public function navigation()
    {
        if (!has_nav_menu('primary_navigation')) {
            return [];
        }

        return (new Navi())
            ->build('primary_navigation')
            ->toArray();
    }
}
PHPEOF

print_success "Header Composer aangemaakt"

# ============================================
# Create Footer Composer for Navi
# ============================================

print_info "Aanmaken Footer Composer..."

cat > app/View/Composers/Footer.php << 'PHPEOF'
<?php

namespace App\View\Composers;

use Roots\Acorn\View\Composer;
use Log1x\Navi\Navi;

class Footer extends Composer
{
    protected static $views = [
        'sections.footer',
    ];

    public function with()
    {
        return [
            'navigation' => $this->navigation(),
        ];
    }

    public function navigation()
    {
        if (!has_nav_menu('primary_navigation')) {
            return [];
        }

        return (new Navi())
            ->build('primary_navigation')
            ->toArray();
    }
}
PHPEOF

print_success "Footer Composer aangemaakt"

# ============================================
# Create Header Blade Template with Alpine
# ============================================

print_info "Aanmaken header.blade.php met hamburger menu (lg breakpoint)..."

cat > resources/views/sections/header.blade.php << 'BLADEEOF'
<header class="banner bg-white shadow-sm" x-data="{ mobileOpen: false }">
  <div class="container">
    <div class="flex items-center justify-between min-h-[80px]">
      {{-- Logo --}}
      <a href="{{ home_url('/') }}" class="text-2xl font-bold" style="color: var(--primary);">
        {{ get_bloginfo('name') }}
      </a>

      {{-- Desktop Navigation --}}
      @if($navigation)
        <nav class="hidden lg:block">
          <ul class="flex items-center space-x-8 mb-0">
            @foreach($navigation as $item)
              <li @if($item->children) 
                    x-data="{ open: false }" 
                    @mouseenter="open = true" 
                    @mouseleave="open = false"
                    class="relative"
                  @endif>
                
                <a href="{{ $item->url }}" 
                   class="nav-link transition-colors duration-200 {{ $item->active ? 'font-semibold' : '' }}"
                   style="color: {{ $item->active ? 'var(--primary)' : 'inherit' }};"
                   @if($item->target) target="{{ $item->target }}" @endif>
                  {{ $item->label }}
                  
                  @if($item->children)
                    <svg class="inline-block w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                  @endif
                </a>

                {{-- Dropdown --}}
                @if($item->children)
                  <ul x-show="open"
                      x-transition:enter="transition ease-out duration-200"
                      x-transition:enter-start="opacity-0 translate-y-1"
                      x-transition:enter-end="opacity-100 translate-y-0"
                      x-transition:leave="transition ease-in duration-150"
                      x-transition:leave-start="opacity-100 translate-y-0"
                      x-transition:leave-end="opacity-0 translate-y-1"
                      class="absolute left-0 mt-2 w-48 bg-white rounded-md shadow-lg py-2 z-50 mb-0"
                      style="display: none;">
                    @foreach($item->children as $child)
                      <li>
                        <a href="{{ $child->url }}" 
                           class="block px-4 py-2 text-gray-700 hover:bg-gray-100 transition-colors duration-200"
                           @if($child->target) target="{{ $child->target }}" @endif>
                          {{ $child->label }}
                        </a>
                      </li>
                    @endforeach
                  </ul>
                @endif
              </li>
            @endforeach
          </ul>
        </nav>
      @endif

      {{-- Mobile Menu Button with 3-line Animation (lg breakpoint) --}}
      <button type="button" 
              class="lg:hidden -m-2.5 p-2.5" 
              @click="mobileOpen = !mobileOpen" 
              :aria-expanded="mobileOpen.toString()" 
              aria-label="Toggle menu">
        <span class="block relative w-6 h-4">
          <span class="absolute left-0 top-0 block h-[2px] w-6 bg-current transition-transform duration-300" 
                :class="mobileOpen ? 'translate-y-[7px] rotate-45' : ''"></span>
          <span class="absolute left-0 top-1/2 block h-[2px] w-6 -translate-y-1/2 bg-current transition-opacity duration-200" 
                :class="mobileOpen ? 'opacity-0' : 'opacity-100'"></span>
          <span class="absolute left-0 bottom-0 block h-[2px] bg-current transition-all duration-300" 
                :class="mobileOpen ? 'w-6 -translate-y-[7px] -rotate-45' : 'w-4'"></span>
        </span>
      </button>
    </div>

    {{-- Mobile Navigation --}}
    @if($navigation)
      <nav x-show="mobileOpen"
           x-transition:enter="transition ease-out duration-200"
           x-transition:enter-start="opacity-0 -translate-y-4"
           x-transition:enter-end="opacity-100 translate-y-0"
           x-transition:leave="transition ease-in duration-150"
           x-transition:leave-start="opacity-100 translate-y-0"
           x-transition:leave-end="opacity-0 -translate-y-4"
           class="lg:hidden py-4 border-t border-gray-200"
           style="display: none;">
        <ul class="space-y-2 mb-0">
          @foreach($navigation as $item)
            <li x-data="{ subOpen: false }">
              <div class="flex items-center justify-between">
                <a href="{{ $item->url }}" 
                   class="block py-2 {{ $item->active ? 'font-semibold' : '' }}"
                   style="color: {{ $item->active ? 'var(--primary)' : 'inherit' }};"
                   @if($item->target) target="{{ $item->target }}" @endif>
                  {{ $item->label }}
                </a>
                
                @if($item->children)
                  <button @click="subOpen = !subOpen" 
                          class="p-2">
                    <svg x-show="!subOpen" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                    <svg x-show="subOpen" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/>
                    </svg>
                  </button>
                @endif
              </div>

              @if($item->children)
                <ul x-show="subOpen"
                    x-transition
                    class="pl-4 space-y-2 mt-2 mb-0"
                    style="display: none;">
                  @foreach($item->children as $child)
                    <li>
                      <a href="{{ $child->url }}" 
                         class="block py-2 text-gray-600"
                         @if($child->target) target="{{ $child->target }}" @endif>
                        {{ $child->label }}
                      </a>
                    </li>
                  @endforeach
                </ul>
              @endif
            </li>
          @endforeach
        </ul>
      </nav>
    @endif
  </div>
</header>
BLADEEOF

print_success "header.blade.php aangemaakt met 3-line hamburger (lg breakpoint)"

# ============================================
# Create Footer Blade Template with Copyright
# ============================================

print_info "Aanmaken footer.blade.php met witte tekst en copyright..."

cat > resources/views/sections/footer.blade.php << 'BLADEEOF'
<footer class="footer">
  <div class="container">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
      {{-- Footer Column 1 --}}
      <div>
        <h3 class="text-lg font-semibold mb-4 text-white">{{ get_bloginfo('name') }}</h3>
        <p class="text-sm text-white">
          {{ get_bloginfo('description') }}
        </p>
      </div>

      {{-- Footer Column 2 - Primary Menu --}}
      <div>
        <h3 class="text-lg font-semibold mb-4 text-white">Menu</h3>
        @if($navigation)
          <ul class="space-y-2 text-sm mb-0">
            @foreach($navigation as $item)
              <li>
                <a href="{{ $item->url }}" 
                   class="text-white hover:text-gray-100"
                   @if($item->target) target="{{ $item->target }}" @endif>
                  {{ $item->label }}
                </a>
              </li>
            @endforeach
          </ul>
        @endif
      </div>

      {{-- Footer Column 3 --}}
      <div>
        <h3 class="text-lg font-semibold mb-4 text-white">Contact</h3>
        <p class="text-sm text-white">
          Email: info@example.com<br>
          Tel: 012-3456789
        </p>
      </div>
    </div>
  </div>
</footer>

{{-- Copyright Section (Outside primary color background) --}}
<div class="bg-white py-6">
  <div class="container">
    <div class="text-center text-xs md:text-sm text-gray-600">
      &copy; <?php echo date('Y'); ?> <?php echo get_bloginfo('name'); ?>. Alle rechten voorbehouden.
    </div>
  </div>
</div>
BLADEEOF

print_success "footer.blade.php aangemaakt met witte tekst en gescheiden copyright"

# ============================================
# Create Search Form Template
# ============================================

print_info "Aanmaken search.blade.php met neutrale kleuren..."

mkdir -p resources/views/forms

cat > resources/views/forms/search.blade.php << 'BLADEEOF'
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
BLADEEOF

print_success "search.blade.php aangemaakt met thema kleuren"

print_info "Verificatie:"
echo "  - app.css: $([ -f resources/css/app.css ] && echo '✓ EXISTS' || echo '✗ MISSING')"
echo "  - editor.css: $([ -f resources/css/editor.css ] && echo '✓ EXISTS' || echo '✗ MISSING')"
echo "  - login.css: $([ -f resources/css/login.css ] && echo '✓ EXISTS' || echo '✗ MISSING')"
echo "  - tailwind.config.js: $([ -f tailwind.config.js ] && echo '✓ EXISTS' || echo '✗ MISSING')"
echo "  - Header.php: $([ -f app/View/Composers/Header.php ] && echo '✓ EXISTS' || echo '✗ MISSING')"
echo "  - Footer.php: $([ -f app/View/Composers/Footer.php ] && echo '✓ EXISTS' || echo '✗ MISSING')"
echo "  - filters.php: $([ -f app/filters.php ] && echo '✓ EXISTS' || echo '✗ MISSING')"
echo "  - 404.blade.php: $([ -f resources/views/404.blade.php ] && echo '✓ EXISTS' || echo '✗ MISSING')"
echo "  - header.blade.php: $([ -f resources/views/sections/header.blade.php ] && echo '✓ EXISTS' || echo '✗ MISSING')"
echo "  - footer.blade.php: $([ -f resources/views/sections/footer.blade.php ] && echo '✓ EXISTS' || echo '✗ MISSING')"
echo "  - search.blade.php: $([ -f resources/views/forms/search.blade.php ] && echo '✓ EXISTS' || echo '✗ MISSING')"

cd ${PROJECT_ROOT}

# ============================================
# Install Node & Yarn with Volta
# ============================================

print_header "Installeren Volta, Node & Yarn"

if ! command -v volta &> /dev/null; then
    print_info "Installeren Volta..."
    curl https://get.volta.sh | bash
    
    export VOLTA_HOME="$HOME/.volta"
    export PATH="$VOLTA_HOME/bin:$PATH"
    
    print_success "Volta geïnstalleerd"
else
    print_info "Volta is al geïnstalleerd"
fi

print_info "Installeren Node..."
volta install node
print_success "Node geïnstalleerd"

print_info "Installeren Yarn..."
npm install --global yarn
print_success "Yarn geïnstalleerd"

# ============================================
# Install Theme Dependencies & Build
# ============================================

print_header "Installeren theme dependencies"

cd web/app/themes/${THEME_NAME}

print_info "Yarn install..."
yarn install
if [ $? -eq 0 ]; then
    print_success "Theme dependencies geïnstalleerd"
else
    print_error "Yarn install mislukt"
fi

# ============================================
# Install Alpine.js
# ============================================

print_info "Installeren Alpine.js..."
yarn add alpinejs
if [ $? -eq 0 ]; then
    print_success "Alpine.js geïnstalleerd"
else
    print_error "Alpine.js installatie mislukt"
fi

# ============================================
# Configure Alpine.js in app.js
# ============================================

print_info "Configureren Alpine.js in app.js..."

if [ -f "resources/js/app.js" ]; then
    cp resources/js/app.js resources/js/app.js.backup
fi

if ! grep -q "import Alpine from 'alpinejs'" resources/js/app.js; then
    sed -i "1i import Alpine from 'alpinejs'\n\nwindow.Alpine = Alpine\n\nAlpine.start()\n" resources/js/app.js
    print_success "Alpine.js toegevoegd aan app.js"
else
    print_info "Alpine.js al aanwezig in app.js"
fi

# ============================================
# Build Theme
# ============================================

print_info "Building theme (yarn build)..."
yarn build
if [ $? -eq 0 ]; then
    print_success "Theme build voltooid"
    
    if [ -f "public/build/manifest.json" ]; then
        if grep -q "login.css" "public/build/manifest.json"; then
            print_success "✓ login.css gevonden in build manifest"
        else
            print_error "✗ login.css NIET in build manifest"
            print_info "Check vite.config.js handmatig"
        fi
        
        if grep -q "editor.css" "public/build/manifest.json"; then
            print_success "✓ editor.css gevonden in build manifest"
        else
            print_error "✗ editor.css NIET in build manifest"
        fi
    fi
else
    print_error "Yarn build mislukt"
fi

cd ${PROJECT_ROOT}

# ============================================
# Install Plugins
# ============================================

print_header "Installeren plugins"

for plugin in "${DEFAULT_PLUGINS[@]}"; do
    print_info "Installeren ${plugin}..."
    composer require wpackagist-plugin/${plugin}
    if [ $? -eq 0 ]; then
        print_success "Plugin geïnstalleerd: ${plugin}"
    else
        print_error "Plugin installatie mislukt: ${plugin}"
    fi
done

print_success "Plugins installatie voltooid"

# ============================================
# Install WordPress & Create Admin User
# ============================================

print_header "WordPress installatie"

print_info "Installeren WordPress..."

if command -v wp &> /dev/null; then
    cd ${PROJECT_ROOT}/web/wp
    
    if ! wp core is-installed 2>/dev/null; then
        wp core install \
            --url="${WP_HOME}" \
            --title="${PROJECT_NAME}" \
            --admin_user="${ADMIN_USERNAME}" \
            --admin_password="${ADMIN_PASSWORD}" \
            --admin_email="${ADMIN_EMAIL}" \
            --skip-email
        
        if [ $? -eq 0 ]; then
            print_success "WordPress geïnstalleerd"
            print_success "Admin user aangemaakt: ${ADMIN_USERNAME}"
        else
            print_error "WordPress installatie via WP-CLI mislukt"
            print_info "Installeer WordPress handmatig via de browser"
        fi
    else
        print_info "WordPress is al geïnstalleerd"
    fi
    
    # ============================================
    # WordPress Configuration
    # ============================================
    
    print_header "WordPress configuratie"
    
    print_info "Installeren Nederlandse taal..."
    wp language core install nl_NL
    wp site switch-language nl_NL
    print_success "Nederlandse taal geïnstalleerd en geactiveerd"
    
    print_info "Instellen permalink structuur..."
    wp rewrite structure '/%postname%/' --hard
    wp rewrite flush --hard
    print_success "Permalink structuur ingesteld"
    
    print_info "Activeren ${THEME_NAME} theme..."
    wp theme activate ${THEME_NAME}
    print_success "Theme geactiveerd: ${THEME_NAME}"
    
    print_info "Activeren plugins..."
    for plugin in "${DEFAULT_PLUGINS[@]}"; do
        wp plugin activate ${plugin}
        if [ $? -eq 0 ]; then
            print_success "Plugin geactiveerd: ${plugin}"
        fi
    done
    
    print_info "Verwijderen alle widgets..."
    wp widget reset --all
    print_success "Alle widgets verwijderd"
    
    print_info "Aanmaken Primary Menu..."
    MENU_ID=$(wp menu create "Primary Menu" --porcelain)
    if [ $? -eq 0 ]; then
        print_success "Primary Menu aangemaakt (ID: ${MENU_ID})"
        
        HOME_ID=$(wp post create --post_type=page --post_title='Home' --post_status=publish --porcelain)
        ABOUT_ID=$(wp post create --post_type=page --post_title='Over ons' --post_status=publish --porcelain)
        CONTACT_ID=$(wp post create --post_type=page --post_title='Contact' --post_status=publish --porcelain)
        
        wp menu item add-post ${MENU_ID} ${HOME_ID}
        wp menu item add-post ${MENU_ID} ${ABOUT_ID}
        wp menu item add-post ${MENU_ID} ${CONTACT_ID}
        
        print_success "Standaard pagina's toegevoegd aan menu"
        
        print_info "Toewijzen menu aan primary_navigation location..."
        wp menu location assign ${MENU_ID} primary_navigation
        if [ $? -eq 0 ]; then
            print_success "Primary Menu toegewezen aan 'primary_navigation' location"
            
            if wp menu location list --format=csv | grep -q "primary_navigation,${MENU_ID}"; then
                print_success "✓ Primary Navigation location aangevinkt"
            else
                print_info "Probeer alternatieve methode..."
                wp option patch update "theme_mods_${THEME_NAME}" nav_menu_locations primary_navigation ${MENU_ID}
                if [ $? -eq 0 ]; then
                    print_success "Menu location geforceerd via theme_mods"
                fi
            fi
        else
            print_error "Kon menu niet automatisch toewijzen"
            print_info "Wijs handmatig toe in Appearance > Menus"
        fi
        
        wp option update show_on_front 'page'
        wp option update page_on_front ${HOME_ID}
        print_success "Homepage ingesteld"
    else
        print_error "Kon Primary Menu niet aanmaken"
    fi
    
    print_info "Opruimen standaard content..."
    wp post delete 1 --force
    wp post delete 2 --force
    print_success "Standaard content verwijderd"
    
    wp option update timezone_string 'Europe/Amsterdam'
    print_success "Timezone ingesteld op Europe/Amsterdam"
    
    wp option update date_format 'd-m-Y'
    wp option update time_format 'H:i'
    print_success "Datum en tijd formaat ingesteld"
    
    cd ${PROJECT_ROOT}
    
else
    print_info "WP-CLI niet gevonden. Installeer WordPress handmatig via de browser."
    print_info "Admin username: ${ADMIN_USERNAME}"
fi

# ============================================
# Save Credentials to File
# ============================================

print_header "Opslaan credentials"

cat > CREDENTIALS.txt << EOF
====================================
STUDIO-PIT PROJECT CREDENTIALS
====================================

Project: ${PROJECT_NAME}
Theme: ${THEME_NAME}
URL: ${WP_HOME}

WordPress Admin:
- Username: ${ADMIN_USERNAME}
- Password: ${ADMIN_PASSWORD}
- Email: ${ADMIN_EMAIL}
- Login URL: ${WP_HOME}/wp/wp-admin/

Database:
- Name: ${DB_NAME}
- User: ${DB_USER}
- Password: ${DB_PASSWORD}
- Host: ${DB_HOST}

Primary Color: ${PRIMARY_COLOR}

Generated: $(date)
====================================
EOF

print_success "Credentials opgeslagen in: CREDENTIALS.txt"

# ============================================
# Final Steps
# ============================================

print_header "Installatie voltooid!"

echo ""
print_success "Project: ${PROJECT_NAME}"
print_success "Theme: ${THEME_NAME}"
print_success "Admin user: ${ADMIN_USERNAME}"
print_success "Admin password: ${ADMIN_PASSWORD}"
print_success "Admin email: ${ADMIN_EMAIL}"
print_success "Primary color: ${PRIMARY_COLOR}"
print_success "Taal: Nederlands (nl_NL)"
print_success "Primary Menu: Aangemaakt en toegewezen (header + footer)"
print_success "Google Fonts: Poppins toegevoegd"
print_success "Widgets: Allemaal verwijderd"
print_success "Theme: Gebuild en klaar voor gebruik"
print_success "Tailwind 4: Geconfigureerd met @source en @import"
print_success "Alpine.js: Geïnstalleerd en geconfigureerd"
print_success "Navi: Geïnstalleerd voor navigatie"
print_success "Acorn Prettify: Geïnstalleerd voor cleaner HTML"
print_success "Login page: Custom Studio-Pit styling met projectnaam"
print_success "Header: Verticaal gecentreerd + 3-line hamburger animatie (lg breakpoint)"
print_success "Footer: Primary menu met witte tekst + gescheiden copyright"
print_success "Search: Neutrale kleuren met thema integratie"
print_success "Editor.css: Consistente typography voor Gutenberg editor"
print_success "Lists: Geen list-style in navigatie"
print_success "Main: mx-auto max-w-4xl px-6 py-10 md:py-16"
print_success "Typography: Consistent tussen app.css, editor.css en login.css"
print_success "Filters: Excerpt length (18 woorden) + archive title prefixes verwijderd"
print_success "404: Nederlandse tekst 'Sorry, de pagina die je zoekt bestaat helaas niet.'"
print_success "Wachtwoord: Zonder problematische karakters (%, /, \, quotes)"
echo ""
print_info "⚠️  BELANGRIJK - Credentials opgeslagen in: CREDENTIALS.txt"
echo ""
print_info "WordPress admin: ${WP_HOME}/wp/wp-admin/"
print_info "Login: ${ADMIN_USERNAME}"
print_info "Password: ${ADMIN_PASSWORD}"
print_info "Email: ${ADMIN_EMAIL}"
echo ""
print_info "Geïnstalleerde packages:"
echo "  - Bedrock (WordPress boilerplate)"
echo "  - Sage (theme framework)"
echo "  - Acorn (Laravel components)"
echo "  - Acorn Prettify (HTML cleanup + Schema.org)"
echo "  - Navi (moderne navigatie)"
echo "  - Alpine.js (interactieve UI)"
echo "  - Tailwind CSS 4 (styling met @source)"
echo ""
print_info "Primary color gebruik in je theme:"
echo "  - CSS: var(--primary), var(--primary-dark), var(--primary-light)"
echo "  - Tailwind: bg-primary, text-primary, border-primary"
echo "  - Components: .btn, .footer, .card"
echo ""
print_info "Alpine.js features:"
echo "  - 3-line hamburger menu animatie (lg breakpoint = 1024px)"
echo "  - Dropdown navigatie (desktop)"
echo "  - Mobile menu met submenu's"
echo "  - x-data, x-show, x-transition"
echo ""
print_info "Navi navigatie:"
echo "  - Header Composer: app/View/Composers/Header.php"
echo "  - Footer Composer: app/View/Composers/Footer.php"
echo "  - Header template: resources/views/sections/header.blade.php"
echo "  - Footer template: resources/views/sections/footer.blade.php"
echo "  - Search form: resources/views/forms/search.blade.php"
echo "  - Automatische active states"
echo "  - Dropdown support met Alpine.js"
echo ""
print_info "CSS bestanden:"
echo "  - app.css: Main theme styling"
echo "  - editor.css: Gutenberg editor styling (consistent met app.css)"
echo "  - login.css: Custom login page (consistent typography)"
echo ""
print_info "Custom filters (app/filters.php):"
echo "  - excerpt_length: 18 woorden"
echo "  - get_the_archive_title: Verwijdert 'Category:' en 'Tag:' prefixes"
echo ""
print_info "Volgende stappen:"
echo "  1. Navigeer naar je theme: cd web/app/themes/${THEME_NAME}"
echo "  2. Start development server: yarn dev"
echo "  3. Build voor productie: yarn build"
echo ""
print_info "Verificatie na installatie:"
echo "  - Check login page: ${WP_HOME}/wp/wp-login.php (primary color button)"
echo "  - Check menu in: Appearance > Menus"
echo "  - Check header: Verticaal gecentreerd met 3-line hamburger (lg)"
echo "  - Check footer: Witte tekst + gescheiden copyright (wit op wit)"
echo "  - Check mobile: 3-line hamburger animatie (onder 1024px)"
echo "  - Check editor: Gutenberg met consistente typography"
echo "  - Check search: Neutrale kleuren met thema integratie"
echo "  - Check 404: Nederlandse tekst"
echo "  - Check archive: Geen 'Category:' of 'Tag:' prefix"
echo "  - Check Tailwind utilities: bg-primary, text-primary"
echo ""
print_success "Veel succes met je project!"