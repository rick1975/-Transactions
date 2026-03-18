<?php

namespace App\View\Composers;

use Roots\Acorn\View\Composer;
use WP_Query;

class ArchiveLocatie extends Composer
{
    /**
     * Views waarop deze composer actief is
     *
     * @var array
     */
    protected static $views = [
        'archive-locatie',
    ];

    /**
     * Data die naar de view gaat
     *
     * @return array
     */
    public function with()
    {
        return [
            'locaties' => $this->locaties(),
            'archive_title' => $this->archiveTitle(),
        ];
    }

    /**
     * Haal alle locaties op
     *
     * @return \WP_Query
     */
    public function locaties()
    {
        return new WP_Query([
            'post_type'      => 'locatie',
            'posts_per_page' => -1,
            'orderby'        => 'menu_order',
            'order'          => 'ASC',
        ]);
    }

    /**
     * Archive titel
     *
     * @return string
     */
    public function archiveTitle()
    {
        return is_post_type_archive('locatie')
            ? post_type_archive_title('', false)
            : 'Onze locaties';
    }
}
