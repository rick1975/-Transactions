<?php

namespace App\View\Composers;

use Roots\Acorn\View\Composer;
use Log1x\Navi\Navi;

class Footer extends Composer
{
    /**
     * List of views served by this composer.
     *
     * @var array
     */
    protected static $views = [
        'sections.footer',
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
        ];
    }

    /**
     * Returns the primary navigation for footer.
     *
     * @return array
     */
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
