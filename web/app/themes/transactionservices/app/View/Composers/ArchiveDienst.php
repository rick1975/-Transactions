<?php

namespace App\View\Composers;

use Roots\Acorn\View\Composer;
use WP_Query;

class ArchiveDienst extends Composer
{
    /**
     * Views waarop deze composer actief is
     *
     * @var array
     */
    protected static $views = [
        'archive-dienst',
        'front-page',
        'sections.footer',
    ];

    /**
     * Data die naar de view gaat
     *
     * @return array
     */
    public function with()
    {
        return [
            'diensten' => $this->diensten(),
            'archive_title' => $this->archiveTitle(),
        ];
    }

    /**
     * Haal alle diensten op
     *
     * @return \WP_Query
     */
    public function diensten()
    {
        return new WP_Query([
            'post_type'      => 'dienst',
            'posts_per_page' => -1, // alles tonen
            'orderby'        => 'menu_order',
            'order'          => 'ASC',
        ]);
    }

    /**
     * Archive titel (alleen op archive)
     *
     * @return string
     */
    public function archiveTitle()
    {
        return is_post_type_archive('dienst') 
            ? post_type_archive_title('', false) 
            : '';
    }
}
