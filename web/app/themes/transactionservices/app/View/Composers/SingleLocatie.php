<?php

namespace App\View\Composers;

use Roots\Acorn\View\Composer;

class SingleLocatie extends Composer
{
    /**
     * List of views served by this composer.
     *
     * @var array
     */
    protected static $views = [
        'single-locatie',
    ];

    /**
     * Data to be passed to view before rendering.
     *
     * @return array
     */
    public function with()
    {
        return [
            'title'     => $this->title(),
            'thumbnail' => $this->thumbnail(),
            'adres'     => $this->adres(),
            'telefoon'  => $this->telefoon(),
            'email'     => $this->email(),
            'route'     => $this->route(),
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
     * Returns the post thumbnail.
     */
    public function thumbnail()
    {
        return get_the_post_thumbnail_url(get_the_ID(), 'large');
    }

    /**
     * Returns the location address.
     */
    public function adres()
    {
        return function_exists('get_field') ? get_field('locatie_adres') : '';
    }

    /**
     * Returns the location phone number.
     */
    public function telefoon()
    {
        return function_exists('get_field') ? get_field('locatie_telefoon') : '';
    }

    /**
     * Returns the location email.
     */
    public function email()
    {
        return function_exists('get_field') ? get_field('locatie_email') : '';
    }

    /**
     * Returns the location route URL.
     */
    public function route()
    {
        return function_exists('get_field') ? get_field('locatie_route') : '';
    }
}
