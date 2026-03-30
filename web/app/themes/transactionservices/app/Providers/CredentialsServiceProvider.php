<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class CredentialsServiceProvider extends ServiceProvider
{
    public function register() {}

    public function boot()
    {
        add_action('admin_menu', [$this, 'registerAdminPage']);
        add_action('admin_init', [$this, 'handleForm']);
        add_action('wp_ajax_save_credentials_order', [$this, 'saveOrder']);
        add_action('admin_enqueue_scripts', [$this, 'enqueueScripts']);
    }

    public function enqueueScripts($hook)
    {
        if ($hook !== 'toplevel_page_credentials-overzicht' && $hook !== 'credentials_page_credentials-edit') {
            return;
        }

        wp_enqueue_script('jquery-ui-sortable');
        wp_enqueue_media();
    }

    public function registerAdminPage()
    {
        add_menu_page(
            'Credentials',
            'Credentials',
            'read',
            'credentials-overzicht',
            [$this, 'renderAdminPage'],
            'dashicons-portfolio',
            22
        );

        add_submenu_page(
            'credentials-overzicht',
            'Bewerken',
            null,
            'read',
            'credentials-edit',
            [$this, 'renderEditPage']
        );
    }

    public function saveOrder()
    {
        check_ajax_referer('credentials_order_nonce', 'nonce');

        global $wpdb;

        foreach ($_POST['order'] as $item) {
            $wpdb->update(
                "{$wpdb->prefix}credentials",
                ['volgorde' => intval($item['volgorde'])],
                ['id'       => intval($item['id'])]
            );
        }

        wp_send_json_success();
    }

    public function handleForm()
    {
        if (!isset($_POST['credentials_nonce']) || !wp_verify_nonce($_POST['credentials_nonce'], 'credentials_edit')) {
            return;
        }

        global $wpdb;
        $id = intval($_POST['credential_id']);

        $wpdb->update(
            "{$wpdb->prefix}credentials",
            [
                'partij1'       => sanitize_text_field($_POST['partij1']),
                'omschrijving1' => sanitize_text_field($_POST['omschrijving1']),
                'partij2'       => sanitize_text_field($_POST['partij2']),
                'omschrijving2' => sanitize_text_field($_POST['omschrijving2']),
                'datum'         => sanitize_text_field($_POST['datum']),
                'sector'        => sanitize_text_field($_POST['sector']),
                'type'          => sanitize_text_field($_POST['type']),
                'logo1'         => sanitize_text_field($_POST['logo1'] ?? ''),
                'logo2'         => sanitize_text_field($_POST['logo2'] ?? ''),
            ],
            ['id' => $id]
        );

        wp_redirect(admin_url('admin.php?page=credentials-overzicht&updated=1'));
        exit;
    }

    public function renderAdminPage()
    {
        global $wpdb;

        if (isset($_GET['delete']) && isset($_GET['_wpnonce'])) {
            $id = intval($_GET['delete']);
            if (wp_verify_nonce($_GET['_wpnonce'], 'credentials_delete_' . $id)) {
                $wpdb->delete("{$wpdb->prefix}credentials", ['id' => $id]);
                wp_redirect(admin_url('admin.php?page=credentials-overzicht&deleted=1'));
                exit;
            }
        }

        $rows = $wpdb->get_results(
            "SELECT * FROM {$wpdb->prefix}credentials ORDER BY datum DESC, volgorde ASC",
            ARRAY_A
        );
        ?>
        <div class="wrap">
            <style>
                #cred-table {
                    background: #fff;
                    border-radius: 12px;
                    border: 1px solid #e2e8f0;
                    width: 100%;
                    margin-top: 16px;
                    overflow: hidden;
                    border-collapse: separate;
                    border-spacing: 0;
                    box-shadow: 0 1px 3px rgba(0,0,0,0.06);
                }
                #cred-table thead th {
                    font-size: 11px;
                    font-weight: 600;
                    text-transform: uppercase;
                    letter-spacing: 0.05em;
                    color: #8a92a6;
                    padding: 12px 16px;
                    border-bottom: 1px solid #ebebeb !important;
                    text-align: left;
                    border: none;
                }
                #cred-table tbody tr {
                    transition: background 0.15s;
                }
                #cred-table tbody tr:last-child td {
                    border-bottom: none !important;
                }
                #cred-table tbody tr:hover {
                    background: #fafafa;
                }
                #cred-table tbody td {
                    padding: 14px 16px;
                    font-size: 13px;
                    color: #333;
                    border: none !important;
                    border-bottom: 1px solid #ebebeb !important;
                    vertical-align: middle;
                }
                .cred-handle { color: #ccc; font-size: 16px; cursor: grab; text-align: center; }
                .cred-name { font-weight: 600; color: #111; }
                .cred-badge { display: inline-block; padding: 2px 10px; border-radius: 20px; font-size: 11px; font-weight: 500; background: #f0f0f0; color: #555; }
                .cred-badge.aankoop { background: #e8f5e9; color: #2e7d32; }
                .cred-badge.verkoop { background: #fff3e0; color: #e65100; }
                .cred-badge.financiering { background: #e3f2fd; color: #1565c0; }
                .cred-actions { display: flex; gap: 6px; }
                .cred-actions .button { border-radius: 6px; font-size: 12px; padding: 2px 10px; height: auto; line-height: 22px; }
                .sortable-placeholder { background: #f0f6fc; border: 2px dashed #2271b1; height: 52px; }
                #cred-status { margin-top: 10px; color: #46b450; font-size: 13px; display: none; }
                .cred-description { color: #8a92a6; font-size: 13px; margin: 12px 0 0; }
            </style>

            <h1 class="wp-heading-inline">Credentials</h1>
            <span class="title-count theme-count"><?= count($rows) ?></span>

            <?php if (isset($_GET['updated'])): ?>
                <div class="notice notice-success is-dismissible"><p>Credential bijgewerkt.</p></div>
            <?php endif; ?>

            <?php if (isset($_GET['deleted'])): ?>
                <div class="notice notice-success is-dismissible"><p>Credential verwijderd.</p></div>
            <?php endif; ?>

            <p class="cred-description">Sleep de rijen om de volgorde te wijzigen. De volgorde wordt automatisch opgeslagen.</p>

            <table id="cred-table">
                <thead>
                    <tr>
                        <th style="width: 32px;"></th>
                        <th>Partij 1</th>
                        <th>Partij 2</th>
                        <th>Datum</th>
                        <th>Sector</th>
                        <th>Type</th>
                        <th>Actie</th>
                    </tr>
                </thead>
                <tbody id="credentials-sortable">
                    <?php foreach ($rows as $row): ?>
                        <tr data-id="<?= $row['id'] ?>">
                            <td class="cred-handle">☰</td>
                            <td class="cred-name"><?= esc_html($row['partij1']) ?></td>
                            <td><?= esc_html($row['partij2']) ?></td>
                            <td><?= date('Y', strtotime($row['datum'])) ?></td>
                            <td><span class="cred-badge"><?= esc_html($row['sector']) ?></span></td>
                            <td><span class="cred-badge <?= esc_attr($row['type']) ?>"><?= esc_html(ucfirst($row['type'])) ?></span></td>
                            <td>
                                <div class="cred-actions">
                                    <a href="<?= admin_url('admin.php?page=credentials-edit&id=' . $row['id']) ?>" class="button button-small">Bewerken</a>
                                    <a href="<?= wp_nonce_url(admin_url('admin.php?page=credentials-overzicht&delete=' . $row['id']), 'credentials_delete_' . $row['id']) ?>"
                                       class="button button-small button-link-delete"
                                       onclick="return confirm('Weet je zeker dat je deze credential wilt verwijderen?')">Verwijderen</a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div id="cred-status">✓ Volgorde opgeslagen</div>
        </div>

        <script>
        jQuery(document).ready(function($) {
            $('#credentials-sortable').sortable({
                handle: 'td:first-child',
                axis: 'y',
                opacity: 0.8,
                placeholder: 'sortable-placeholder',
                update: function() {
                    var order = [];
                    $('#credentials-sortable tr').each(function(i) {
                        order.push({ id: $(this).data('id'), volgorde: i });
                    });
                    $.post(ajaxurl, {
                        action: 'save_credentials_order',
                        nonce: '<?= wp_create_nonce('credentials_order_nonce') ?>',
                        order: order
                    }, function() {
                        $('#cred-status').fadeIn().delay(2000).fadeOut();
                    });
                }
            });
        });
        </script>
        <?php
    }

    public function renderEditPage()
    {
        global $wpdb;
        $id  = intval($_GET['id'] ?? 0);
        $row = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM {$wpdb->prefix}credentials WHERE id = %d", $id),
            ARRAY_A
        );

        if (!$row) {
            echo '<div class="wrap"><p>Credential niet gevonden.</p></div>';
            return;
        }
        ?>
        <div class="wrap">
            <style>
                .cred-edit-card {
                    background: #fff;
                    border-radius: 12px;
                    border: 1px solid #e2e8f0;
                    box-shadow: 0 1px 3px rgba(0,0,0,0.06);
                    padding: 32px;
                    margin-top: 16px;
                    max-width: 860px;
                }
                .cred-edit-group {
                    margin-bottom: 24px;
                }
                .cred-edit-group label {
                    display: block;
                    font-size: 11px;
                    font-weight: 600;
                    text-transform: uppercase;
                    letter-spacing: 0.05em;
                    color: #8a92a6;
                    margin-bottom: 6px;
                }
                .cred-edit-group input[type="text"],
                .cred-edit-group input[type="date"],
                .cred-edit-group select {
                    width: 100%;
                    border: 1px solid #e2e8f0;
                    border-radius: 8px;
                    padding: 10px 14px;
                    font-size: 13px;
                    color: #333;
                    background: #fff;
                    box-shadow: none;
                    outline: none;
                    transition: border-color 0.15s;
                    box-sizing: border-box;
                }
                .cred-edit-group input:focus,
                .cred-edit-group select:focus {
                    border-color: #2271b1;
                    box-shadow: 0 0 0 1px #2271b1;
                }
                .cred-edit-logo-preview {
                    background: #f8f9fb;
                    border: 1px solid #e2e8f0;
                    border-radius: 8px;
                    padding: 12px 16px;
                    display: inline-flex;
                    align-items: center;
                    justify-content: center;
                    margin-bottom: 10px;
                    min-width: 100px;
                    min-height: 60px;
                }
                .cred-edit-logo-preview img {
                    max-height: 50px;
                    max-width: 180px;
                    object-fit: contain;
                }
                .cred-edit-logo-row {
                    display: flex;
                    gap: 8px;
                    align-items: center;
                }
                .cred-edit-logo-row input {
                    flex: 1;
                }
                .cred-edit-divider {
                    border: none;
                    border-top: 1px solid #f0f0f0;
                    margin: 28px 0;
                }
                .cred-edit-grid {
                    display: grid;
                    grid-template-columns: 1fr 1fr;
                    gap: 16px;
                }
                .cred-edit-actions {
                    display: flex;
                    gap: 12px;
                    margin-top: 32px;
                    padding-top: 24px;
                    border-top: 1px solid #f0f0f0;
                }
                .cred-edit-hint {
                    font-size: 11px;
                    color: #aaa;
                    margin-top: 5px;
                }
                .cred-edit-section-title {
                    font-size: 11px;
                    font-weight: 700;
                    text-transform: uppercase;
                    letter-spacing: 0.08em;
                    color: #444;
                    margin-bottom: 20px;
                }
            </style>

            <h1 class="wp-heading-inline">Credential bewerken</h1>
            <a href="<?= admin_url('admin.php?page=credentials-overzicht') ?>" class="page-title-action">
                ← Terug naar overzicht
            </a>

            <form method="POST">
                <?php wp_nonce_field('credentials_edit', 'credentials_nonce'); ?>
                <input type="hidden" name="credential_id" value="<?= $row['id'] ?>">

                <div class="cred-edit-card">

                    <p class="cred-edit-section-title">Partij 1</p>

                    <div class="cred-edit-group">
                        <label for="partij1">Naam</label>
                        <input type="text" id="partij1" name="partij1" value="<?= esc_attr($row['partij1']) ?>">
                    </div>

                    <div class="cred-edit-group">
                        <label for="omschrijving1">Omschrijving</label>
                        <input type="text" id="omschrijving1" name="omschrijving1" value="<?= esc_attr($row['omschrijving1']) ?>">
                    </div>

                    <div class="cred-edit-group">
                        <label>Logo</label>
                        <div class="cred-edit-logo-preview">
                            <img id="logo1-preview"
                                 src="<?= !empty($row['logo1']) ? esc_url(home_url('/app/uploads/' . $row['logo1'])) : '' ?>"
                                 style="<?= empty($row['logo1']) ? 'display:none' : '' ?>">
                        </div>
                        <div class="cred-edit-logo-row">
                            <input type="text" id="logo1" name="logo1" value="<?= esc_attr($row['logo1'] ?? '') ?>" placeholder="2024/10/logo.png">
                            <button type="button" class="button" onclick="openMediaUploader('logo1')">Kies afbeelding</button>
                        </div>
                    </div>

                    <hr class="cred-edit-divider">

                    <p class="cred-edit-section-title">Partij 2</p>

                    <div class="cred-edit-group">
                        <label for="partij2">Naam</label>
                        <input type="text" id="partij2" name="partij2" value="<?= esc_attr($row['partij2']) ?>">
                    </div>

                    <div class="cred-edit-group">
                        <label for="omschrijving2">Omschrijving</label>
                        <input type="text" id="omschrijving2" name="omschrijving2" value="<?= esc_attr($row['omschrijving2']) ?>">
                    </div>

                    <div class="cred-edit-group">
                        <label>Logo</label>
                        <div class="cred-edit-logo-preview">
                            <img id="logo2-preview"
                                 src="<?= !empty($row['logo2']) ? esc_url(home_url('/app/uploads/' . $row['logo2'])) : '' ?>"
                                 style="<?= empty($row['logo2']) ? 'display:none' : '' ?>">
                        </div>
                        <div class="cred-edit-logo-row">
                            <input type="text" id="logo2" name="logo2" value="<?= esc_attr($row['logo2'] ?? '') ?>" placeholder="2024/10/logo.png">
                            <button type="button" class="button" onclick="openMediaUploader('logo2')">Kies afbeelding</button>
                        </div>
                    </div>

                    <hr class="cred-edit-divider">

                    <p class="cred-edit-section-title">Details</p>

                    <div class="cred-edit-grid">
                        <div class="cred-edit-group">
                            <label for="datum">Datum</label>
                            <input type="date" id="datum" name="datum" value="<?= esc_attr($row['datum']) ?>">
                        </div>
                        <div class="cred-edit-group">
                            <label for="type">Type</label>
                            <select id="type" name="type">
                                <?php foreach (['aankoop' => 'Aankoop', 'verkoop' => 'Verkoop', 'financiering' => 'Financiering'] as $value => $label): ?>
                                    <option value="<?= $value ?>" <?= selected($row['type'], $value, false) ?>><?= $label ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="cred-edit-group">
                        <label for="sector">Sector</label>
                        <select id="sector" name="sector">
                            <?php foreach ([
                                'agriculture'                                => 'Agriculture',
                                'business-services'                          => 'Business services',
                                'education'                                  => 'Education',
                                'financial-services'                         => 'Financial services',
                                'food-and-Consumer-goods'                    => 'Food and Consumer goods',
                                'healthcare'                                 => 'Healthcare',
                                'industrial-services-Construction-Utilities' => 'Industrial services & Construction & Utilities',
                                'information-Technology'                     => 'Information Technology',
                                'leisure'                                    => 'Leisure',
                                'logistics'                                  => 'Logistics',
                                'media'                                      => 'Media',
                                'production'                                 => 'Production',
                                'software'                                   => 'Software',
                            ] as $value => $label): ?>
                                <option value="<?= $value ?>" <?= selected($row['sector'], $value, false) ?>><?= $label ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="cred-edit-actions">
                        <button type="submit" class="button button-primary">Opslaan</button>
                        <a href="<?= admin_url('admin.php?page=credentials-overzicht') ?>" class="button">Annuleren</a>
                    </div>

                </div>
            </form>
        </div>

        <script>
        function openMediaUploader(field) {
            var frame = wp.media({
                title: 'Kies een afbeelding',
                button: { text: 'Gebruik afbeelding' },
                multiple: false
            });

            frame.on('select', function() {
                var attachment = frame.state().get('selection').first().toJSON();
                var url = attachment.url;
                var path = url.replace(/^.*\/app\/uploads\//, '');

                document.getElementById(field).value = path;

                var preview = document.getElementById(field + '-preview');
                preview.src = url;
                preview.style.display = 'block';
            });

            frame.open();
        }
        </script>
        <?php
    }
}