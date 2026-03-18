<?php

namespace App\View\Composers;

use Roots\Acorn\View\Composer;

class SingleDienst extends Composer
{
    /**
     * List of views served by this composer.
     *
     * @var array
     */
    protected static $views = [
        'single-dienst',
    ];

    /**
     * Data to be passed to view before rendering.
     *
     * @return array
     */
    public function with()
    {
        return [
            'title'      => $this->title(),
            'content'    => $this->content(),
            'thumbnail'  => get_the_post_thumbnail_url(get_the_ID(), 'large'),
            'faqs'       => $this->faqs(),
            'teamleden'  => $this->teamleden(),
        ];
    }

    /**
     * Returns the post title.
     */
    public function title()
    {
        return get_the_title();
    }

    /**
     * Returns the post content.
     */
    public function content()
    {
        return apply_filters('the_content', get_the_content());
    }

    /**
     * Returns team members linked to this dienst via ACF relationship field.
     */
    public function teamleden(): array
    {
        if (!function_exists('get_field')) {
            return [];
        }

        $posts = get_field('dienst_contactpersonen', get_the_ID());

        if (empty($posts)) {
            return [];
        }

        return array_map(function ($post) {
            return [
                'id'        => $post->ID,
                'title'     => get_the_title($post->ID),
                'permalink' => get_permalink($post->ID),
                'thumbnail' => get_the_post_thumbnail_url($post->ID, 'medium') ?: null,
                'functie'   => get_field('team_functie', $post->ID) ?: '',
                'mail'      => get_field('team_mail', $post->ID) ?: '',
                'telefoon'  => get_field('team_tel', $post->ID) ?: '',
                'linkedin'  => get_field('team_linkedin', $post->ID) ?: '',
                'excerpt'   => wp_trim_words(
                    get_post_field('post_excerpt', $post->ID) ?: strip_tags(get_post_field('post_content', $post->ID)),
                    100
                ),
            ];
        }, $posts);
    }

    /**
     * Returns the FAQ at singel Service.
     */
    public function faqs(): array
    {
        if (!function_exists('get_field')) {
            return [];
        }

        $faqs = get_field('dienst_faq', get_the_ID()) ?: [];

        // Filter lege items
        return array_values(array_filter($faqs, function ($item) {
            return !empty($item['q']) && !empty($item['a']);
        }));
    }
}