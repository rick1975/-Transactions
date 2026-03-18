<?php

namespace App\View\Composers;

use Roots\Acorn\View\Composer;
use WP_Query;

class Team extends Composer
{
    protected static $views = [
        'archive-team',
        'page-team',
    ];

    public function with()
    {
        return [
            'teamleden' => $this->teamQuery(),
        ];
    }

    private function teamQuery()
    {
        $args = [
            'post_type'      => 'team',
            'posts_per_page' => -1,
            's'              => isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '',
        ];

        $tax_query  = [];
        $meta_query = [];

        // Afdeling filter (taxonomy)
        if (!empty($_GET['afdeling'])) {
            $tax_query[] = [
                'taxonomy' => 'afdeling',
                'field'    => 'slug',
                'terms'    => sanitize_text_field($_GET['afdeling']),
            ];
        }

        // Dienst filter (ACF relationship)
        if (!empty($_GET['dienst'])) {

            $dienst_slug = sanitize_text_field($_GET['dienst']);
            $dienst      = get_page_by_path($dienst_slug, OBJECT, 'dienst');

            if ($dienst) {
                $meta_query[] = [
                    'key'     => 'team_diensten',
                    'value'   => '"' . $dienst->ID . '"',
                    'compare' => 'LIKE',
                ];
            }
        }

        if (!empty($tax_query)) {
            $args['tax_query'] = $tax_query;
        }

        if (!empty($meta_query)) {
            $args['meta_query'] = $meta_query;
        }

        return new \WP_Query($args);
    }
}