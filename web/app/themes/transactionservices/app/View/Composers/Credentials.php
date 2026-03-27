<?php

namespace App\View\Composers;

use Roots\Acorn\View\Composer;

class Credentials extends Composer
{
    protected static $views = [
        'page-credentials',
    ];

    public function with()
    {
        return [
            'credentials' => $this->credentialsQuery(),
        ];
    }

    private function credentialsQuery()
    {
        global $wpdb;

        $where  = [];
        $params = [];

        if (!empty($_GET['sector_naam'])) {
            $where[]  = 'sector = %s';
            $params[] = sanitize_text_field($_GET['sector_naam']);
        }

        if (!empty($_GET['jaar'])) {
            $where[]  = 'YEAR(datum) = %d';
            $params[] = intval($_GET['jaar']);
        }

        $sql = "SELECT * FROM {$wpdb->prefix}credentials ORDER BY volgorde ASC";

        if (!empty($where)) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
            $sql  = $wpdb->prepare($sql, $params);
        }

        $sql .= ' ORDER BY datum DESC';

        return $wpdb->get_results($sql, ARRAY_A);
    }
}

