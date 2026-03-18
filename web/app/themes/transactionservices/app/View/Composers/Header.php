<?php

namespace App\View\Composers;

use Roots\Acorn\View\Composer;
use Log1x\Navi\Facades\Navi;

class Header extends Composer
{
    /**
     * List of views served by this composer.
     *
     * @var array
     */
    protected static $views = [
        'sections.header',
    ];

    /**
     * Data to be passed to view before rendering.
     *
     * @return array
     */
    public function with()
    {
        return [
            'navigation' => $this->navigation(),
            'diensten_menu' => $this->getDiensten(),
        ];
    }

    /**
     * Returns the primary navigation.
     *
     * @return array
     */
    public function navigation()
    {
        if (! has_nav_menu('primary_navigation')) {
            return [];
        }

        return Navi::build('primary_navigation')->toArray();
    }

    /**
     * Returns all diensten posts ordered by menu order.
     *
     * @return array
     */
    public function getDiensten()
    {
        $query = new \WP_Query([
            'post_type' => 'dienst',
            'posts_per_page' => -1,
            'orderby' => 'menu_order',
            'order' => 'ASC',
            'post_status' => 'publish',
        ]);

        $diensten = [];

        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                
                $diensten[] = [
                    'id' => get_the_ID(),
                    'title' => get_the_title(),
                    'permalink' => get_permalink(),
                    'excerpt' => get_the_excerpt(),
                    'thumbnail' => get_the_post_thumbnail_url(get_the_ID(), 'thumbnail'),
                ];
            }
            wp_reset_postdata();
        }

        return $diensten;
    }
}