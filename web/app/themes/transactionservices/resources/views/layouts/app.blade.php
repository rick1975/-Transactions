<!doctype html>
<html @php(language_attributes())>
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/png" href="/favicon-96x96.png" sizes="96x96" />
    <link rel="icon" type="image/svg+xml" href="/favicon.svg" />
    <link rel="shortcut icon" href="/favicon.ico" />
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png" />
    <meta name="apple-mobile-web-app-title" content="andTransactionservices" />
    <link rel="manifest" href="/site.webmanifest" />
    @php(do_action('get_header'))
    @php(wp_head())

    @vite(['resources/css/app.css', 'resources/js/app.js'])
  </head>

  <body @php(body_class())>
    @php(wp_body_open())

    <div id="app">
      <a class="sr-only focus:not-sr-only" href="#main">
        {{ __('Skip to content', 'sage') }}
      </a>

      @include('sections.header')

      <main id="main" 
        class="main mx-auto px-6 py-10 lg:py-12 xl:py-16 
        {{ 
          is_post_type_archive('dienst') 
            ? 'lg:max-w-[100rem]' 
            : (
                (is_post_type_archive('team')
                || is_singular('team')
                || is_singular('dienst')
                || is_post_type_archive('locatie')
                || is_singular('locatie')
                || is_home()
                || is_category('kennisbank'))
                  ? 'lg:max-w-6xl xxl:px-0' 
                  : 'md:max-w-[768px] lg:max-w-4xl'
              ) 
        }}">
        @yield('content')
      </main>
      
      @hasSection('sidebar')
        <aside class="sidebar">
          @yield('sidebar')
        </aside>
      @endif

      @include('sections.footer')
    </div>

    @php(do_action('get_footer'))
    @php(wp_footer())
  </body>
</html>
