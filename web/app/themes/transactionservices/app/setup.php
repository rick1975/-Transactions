<?php

/**
 * Theme setup.
 */

namespace App;

use Illuminate\Support\Facades\Vite;

/**
 * Inject styles into the block editor.
 *
 * @return array
 */
add_filter('block_editor_settings_all', function ($settings) {
    $style = Vite::asset('resources/css/editor.css');

    $settings['styles'][] = [
        'css' => "@import url('{$style}')",
    ];

    return $settings;
});

/**
 * Inject scripts into the block editor.
 *
 * @return void
 */
add_filter('admin_head', function () {
    if (! get_current_screen()?->is_block_editor()) {
        return;
    }

    $dependencies = json_decode(Vite::content('editor.deps.json'));

    foreach ($dependencies as $dependency) {
        if (! wp_script_is($dependency)) {
            wp_enqueue_script($dependency);
        }
    }

    echo Vite::withEntryPoints([
        'resources/js/editor.js',
    ])->toHtml();
});

/**
 * Use the generated theme.json file.
 *
 * @return string
 */
add_filter('theme_file_path', function ($path, $file) {
    return $file === 'theme.json'
        ? public_path('build/assets/theme.json')
        : $path;
}, 10, 2);

/**
 * Register the initial theme setup.
 *
 * @return void
 */
add_action('after_setup_theme', function () {
    /**
     * Disable full-site editing support.
     *
     * @link https://wptavern.com/gutenberg-10-5-embeds-pdfs-adds-verse-block-color-options-and-introduces-new-patterns
     */
    remove_theme_support('block-templates');

    /**
     * Register the navigation menus.
     *
     * @link https://developer.wordpress.org/reference/functions/register_nav_menus/
     */
    register_nav_menus([
        'primary_navigation' => __('Primary Navigation', 'sage'),
    ]);

    /**
     * Disable the default block patterns.
     *
     * @link https://developer.wordpress.org/block-editor/developers/themes/theme-support/#disabling-the-default-block-patterns
     */
    remove_theme_support('core-block-patterns');

    /**
     * Enable plugins to manage the document title.
     *
     * @link https://developer.wordpress.org/reference/functions/add_theme_support/#title-tag
     */
    add_theme_support('title-tag');

    /**
     * Enable post thumbnail support.
     *
     * @link https://developer.wordpress.org/themes/functionality/featured-images-post-thumbnails/
     */
    add_theme_support('post-thumbnails');

    /**
     * Enable responsive embed support.
     *
     * @link https://developer.wordpress.org/block-editor/how-to-guides/themes/theme-support/#responsive-embedded-content
     */
    add_theme_support('responsive-embeds');

    /**
     * Enable HTML5 markup support.
     *
     * @link https://developer.wordpress.org/reference/functions/add_theme_support/#html5
     */
    add_theme_support('html5', [
        'caption',
        'comment-form',
        'comment-list',
        'gallery',
        'search-form',
        'script',
        'style',
    ]);

    /**
     * Enable selective refresh for widgets in customizer.
     *
     * @link https://developer.wordpress.org/reference/functions/add_theme_support/#customize-selective-refresh-widgets
     */
    add_theme_support('customize-selective-refresh-widgets');
}, 20);

/**
 * Register the theme sidebars.
 *
 * @return void
 */
add_action('widgets_init', function () {
    $config = [
        'before_widget' => '<section class="widget %1$s %2$s">',
        'after_widget' => '</section>',
        'before_title' => '<h3>',
        'after_title' => '</h3>',
    ];

    register_sidebar([
        'name' => __('Primary', 'sage'),
        'id' => 'sidebar-primary',
    ] + $config);

    register_sidebar([
        'name' => __('Footer', 'sage'),
        'id' => 'sidebar-footer',
    ] + $config);
});

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

// Always LOGIN 
add_action('template_redirect', function () {
  // al ingelogd? prima
  if (is_user_logged_in()) {
    return;
  }

  // laat login/registratie/POSTs door
  if (is_admin()) {
    return;
  }

  $uri = $_SERVER['REQUEST_URI'] ?? '';

  // sta wp-login.php, logout en admin-ajax toe
  if (str_contains($uri, 'wp-login.php') || str_contains($uri, 'wp-admin') || str_contains($uri, 'admin-ajax.php')) {
    return;
  }

  // (optioneel) laat REST door voor bv. headless / plugins
  // if (str_contains($uri, '/wp-json/')) return;

  // redirect naar login met terugkeer-url
  wp_safe_redirect(wp_login_url(home_url(add_query_arg([], $wp->request ?? ''))));
  exit;
}, 1);

/**
 * Register Custom Post Type: Diensten
 */
add_action('init', function () {
    register_post_type('dienst', [
        'labels' => [
            'name' => 'Diensten',
            'singular_name' => 'Dienst',
            'add_new' => 'Nieuwe toevoegen',
            'add_new_item' => 'Nieuwe toevoegen',
            'edit_item' => 'Dienst bewerken',
            'view_item' => 'Dienst bekijken',
            'search_items' => 'Diensten zoeken',
            'not_found' => 'Geen diensten gevonden',
        ],
        'public' => true,
        'publicly_queryable' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'show_in_rest' => true,
        'has_archive' => true,
        'menu_icon' => 'dashicons-portfolio',
        'menu_position' => 20,
        'supports' => ['title', 'editor', 'thumbnail', 'excerpt', 'page-attributes'],
        'rewrite' => [
            'slug' => 'diensten',
            'with_front' => false,
        ],
        'capability_type' => 'post',
    ]);
});

/**
 * Register Custom Post Type: Teams
 */
add_action('init', function () {
  register_post_type('team', [
    'labels' => [
      'name' => 'Team',
      'singular_name' => 'Teamlid',
      'add_new_item' => 'Nieuw teamlid',
      'edit_item' => 'Teamlid bewerken',
    ],
    'public' => true,
    'has_archive' => true,
    'rewrite' => ['slug' => 'team'],
    'menu_icon' => 'dashicons-groups',
    'menu_position' => 21,
    'supports' => ['title', 'editor', 'thumbnail', 'excerpt'],
    'show_in_rest' => true,
  ]);

  // Optioneel (aanrader): Afdeling als taxonomy (filteren wordt super simpel)
  register_taxonomy('afdeling', ['team'], [
    'labels' => [
      'name' => 'Afdelingen',
      'singular_name' => 'Afdeling',
    ],
    'public' => true,
    'hierarchical' => true,
    'rewrite' => ['slug' => 'afdeling'],
    'show_in_rest' => true,
  ]);
});

/**
 * Register Custom Post Type: Locaties
 */
add_action('init', function () {
  register_post_type('locatie', [
    'labels' => [
      'name' => 'Locaties',
      'singular_name' => 'Locatie',
      'add_new' => 'Nieuwe toevoegen',
      'add_new_item' => 'Nieuwe locatie toevoegen',
      'edit_item' => 'Locatie bewerken',
      'view_item' => 'Locatie bekijken',
      'search_items' => 'Locaties zoeken',
      'not_found' => 'Geen locaties gevonden',
    ],
    'public' => true,
    'publicly_queryable' => true,
    'show_ui' => true,
    'show_in_menu' => true,
    'show_in_rest' => true,
    'has_archive' => true,
    'menu_icon' => 'dashicons-location',
    'menu_position' => 22,
    'supports' => ['title', 'thumbnail'],
    'rewrite' => [
      'slug' => 'locaties',
      'with_front' => false,
    ],
    'capability_type' => 'post',
  ]);
});

/**
 * Register ACF Fields for Diensten
 */
add_action('acf/include_fields', function () {
  if (!function_exists('acf_add_local_field_group')) {
    return;
  }

  acf_add_local_field_group([
    'key'    => 'group_dienst_contactpersonen',
    'title'  => 'Contactpersonen',
    'fields' => [
      [
        'key'           => 'field_dienst_contactpersonen',
        'label'         => 'Contactpersonen',
        'name'          => 'dienst_contactpersonen',
        'type'          => 'relationship',
        'instructions'  => 'Selecteer de teamleden die als contactpersoon voor deze dienst zichtbaar zijn.',
        'post_type'     => ['team'],
        'filters'       => ['search'],
        'elements'      => ['featured_image'],
        'min'           => 0,
        'max'           => 0,
        'return_format' => 'object',
      ],
    ],
    'location' => [
      [
        [
          'param'    => 'post_type',
          'operator' => '==',
          'value'    => 'dienst',
        ],
      ],
    ],
    'menu_order'            => 10,
    'position'              => 'normal',
    'style'                 => 'default',
    'label_placement'       => 'top',
    'instruction_placement' => 'label',
  ]);
});

/**
 * Register ACF Fields for Locaties
 */
add_action('acf/include_fields', function () {
  if (!function_exists('acf_add_local_field_group')) {
    return;
  }

  acf_add_local_field_group([
    'key' => 'group_locatie',
    'title' => 'Locatie gegevens',
    'fields' => [
      [
        'key' => 'field_locatie_adres',
        'label' => 'Adres',
        'name' => 'locatie_adres',
        'type' => 'textarea',
        'required' => 1,
        'rows' => 3,
      ],
      [
        'key' => 'field_locatie_telefoon',
        'label' => 'Telefoonnummer',
        'name' => 'locatie_telefoon',
        'type' => 'text',
        'required' => 0,
      ],
      [
        'key' => 'field_locatie_email',
        'label' => 'E-mailadres',
        'name' => 'locatie_email',
        'type' => 'email',
        'required' => 0,
      ],
      [
        'key' => 'field_locatie_route',
        'label' => 'Route (Google Maps URL)',
        'name' => 'locatie_route',
        'type' => 'url',
        'required' => 0,
        'instructions' => 'Plak hier de volledige Google Maps URL voor routebeschrijving',
      ],
    ],
    'location' => [
      [
        [
          'param' => 'post_type',
          'operator' => '==',
          'value' => 'locatie',
        ],
      ],
    ],
  ]);
});