<?php
/*
Plugin Name: Danko REST API Post Grid
Description: Display posts from any REST API in a customizable grid or list, adjustable link styles, Gutenberg block, and settings page.
Version: 1.0
Author: Kevin Danko
Author URI: https://dankocreative.com
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: danko-rest-post-grid
Domain Path: /languages
*/

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

function drpg_get_items_html($atts) {
    $atts = shortcode_atts([
        'api_url'     => '',
        'layout'      => 'grid',
        'columns'     => 4,
        'font_family' => 'inherit',
        'font_size'   => 'inherit',
        'link_color'  => '#000000',
        'link_size'   => 'inherit',
        'link_family' => 'inherit',
        'icon_color'  => '#ffffff',
        'icon_size'   => '32px',
        'overlay_title' => false,
        'line_height'  => '1.2',
        'text_shadow'  => 'none',
        'box_shadow'   => 'none',
        'border_radius'=> '0',
        'padding'      => '10px',
        'margin'       => '0',
        'title_padding'=> '0',
        'title_margin' => '0',
        'link_decoration' => 'none',
        'title_align'  => 'center',
        'order_by'     => 'date',
        'show_meta'    => false,
    ], $atts);

    if (empty($atts['api_url'])) {
        return '<p>No API URL provided.</p>';
    }

    $api_url = $atts['api_url'];
    if (false === strpos($api_url, '_embed')) {
        $api_url .= (strpos($api_url, '?') === false ? '?' : '&') . '_embed';
    }
    $order_args = [];
    if ($atts['order_by'] === 'title') {
        $order_args = ['orderby' => 'title', 'order' => 'asc'];
    } else {
        $order_args = ['orderby' => 'date', 'order' => 'desc'];
    }
    $api_url = add_query_arg($order_args, $api_url);

    $response = wp_remote_get($api_url);
    if (is_wp_error($response)) {
        return '<p>Unable to retrieve posts.</p>';
    }

    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);
    if (!$data) {
        return '<p>Invalid API response.</p>';
    }

    ob_start();
    foreach ($data as $post) {
        $image = '';
        if (!empty($post['_embedded']['wp:featuredmedia'][0]['source_url'])) {
            $image = $post['_embedded']['wp:featuredmedia'][0]['source_url'];
        } elseif (!empty($post['better_featured_image']['source_url'])) {
            $image = $post['better_featured_image']['source_url'];
        } elseif (!empty($post['jetpack_featured_media_url'])) {
            $image = $post['jetpack_featured_media_url'];
        }
        ?>
        <div class="drpg-item">
            <?php if ($image): ?>
                <div class="drpg-thumb">
                    <a href="<?php echo esc_url($post['link']); ?>" target="_blank" rel="noopener noreferrer" title="<?php echo esc_attr(strip_tags($post['title']['rendered'])); ?>">
                        <img src="<?php echo esc_url($image); ?>" alt="<?php echo esc_attr(strip_tags($post['title']['rendered'])); ?>" title="<?php echo esc_attr(strip_tags($post['title']['rendered'])); ?>">
                        <span class="dashicons dashicons-video-alt3 drpg-video-icon"></span>
                    </a>
                    <?php if ($atts['overlay_title']): ?>
                        <h3 class="drpg-title-overlay" style="text-align: <?php echo esc_attr($atts['title_align']); ?>;"><a href="<?php echo esc_url($post['link']); ?>" target="_blank" rel="noopener noreferrer" title="<?php echo esc_attr(strip_tags($post['title']['rendered'])); ?>">
                            <?php echo esc_html(strip_tags($post['title']['rendered'])); ?>
                        </a></h3>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            <?php if (!$atts['overlay_title']): ?>
            <h3 class="drpg-title-under" style="text-align: <?php echo esc_attr($atts['title_align']); ?>;"><a href="<?php echo esc_url($post['link']); ?>" target="_blank" rel="noopener noreferrer" title="<?php echo esc_attr(strip_tags($post['title']['rendered'])); ?>">
                <?php echo esc_html(strip_tags($post['title']['rendered'])); ?>
            </a></h3>
            <?php endif; ?>
            <?php if (!empty($post['excerpt']['rendered'])): ?>
                <div class="drpg-excerpt"><?php echo wp_kses_post($post['excerpt']['rendered']); ?></div>
            <?php endif; ?>
            <?php if ($atts['show_meta']): ?>
                <?php
                $author = '';
                if (!empty($post['_embedded']['author'][0]['name'])) {
                    $author = $post['_embedded']['author'][0]['name'];
                }
                $date = date_i18n(get_option('date_format'), strtotime($post['date']));
                ?>
                <div class="drpg-meta">
                    <?php echo esc_html($date); ?>
                    <?php if ($author): ?>
                        <span class="drpg-meta-author"><?php echo esc_html__('by', 'danko-rest-post-grid'); ?> <?php echo esc_html($author); ?></span>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
        <?php
    }
    return ob_get_clean();
}

function drpg_render_post_grid($atts) {
    $atts = shortcode_atts([
        'api_url'          => '',
        'layout'           => 'grid',
        'columns'          => 4,
        'font_family'      => 'inherit',
        'font_size'        => 'inherit',
        'link_color'       => '#000000',
        'link_size'        => 'inherit',
        'link_family'      => 'inherit',
        'icon_color'       => '#ffffff',
        'icon_size'        => '32px',
        'overlay_title'    => false,
        'line_height'      => '1.2',
        'text_shadow'      => 'none',
        'box_shadow'       => 'none',
        'border_radius'    => '0',
        'padding'          => '10px',
        'margin'           => '0',
        'title_padding'    => '0',
        'title_margin'     => '0',
        'link_decoration'  => 'none',
        'title_align'      => 'center',
        'order_by'         => 'date',
        'auto_fetch'       => true,
        'refresh_interval' => 60,
        'show_meta'        => false,
    ], $atts, 'rest_post_grid');

    if (empty($atts['api_url'])) {
        $atts['api_url'] = get_option('drpg_api_url', '');
    }

    $columns = max(1, intval($atts['columns']));
    $layout = $atts['layout'] === 'list' ? 'list' : 'grid';

    $container_id = 'drpg-' . uniqid();

    ob_start();
    ?>
    <style>
    .drpg-grid {
        display: grid;
        grid-template-columns: repeat(<?php echo $columns; ?>, 1fr);
        gap: 20px;
        font-family: <?php echo esc_attr($atts['font_family']); ?>;
        font-size: <?php echo esc_attr($atts['font_size']); ?>;
    }
    @media (max-width: 768px) {
        .drpg-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }
    @media (max-width: 480px) {
        .drpg-grid {
            grid-template-columns: 1fr;
        }
    }
    .drpg-list {
        display: block;
        font-family: <?php echo esc_attr($atts['font_family']); ?>;
        font-size: <?php echo esc_attr($atts['font_size']); ?>;
    }
    .drpg-item {
        border: 1px solid #ccc;
        padding: <?php echo esc_attr($atts['padding']); ?>;
        margin: <?php echo esc_attr($atts['margin']); ?>;
        text-align: center;
        box-shadow: <?php echo esc_attr($atts['box_shadow']); ?>;
        border-radius: <?php echo esc_attr($atts['border_radius']); ?>;
    }
    .drpg-thumb {
        position: relative;
        padding-top: 56%;
        overflow: hidden;
    }
    .drpg-item h3 {
        margin-top: 0;
        padding: <?php echo esc_attr($atts['title_padding']); ?>;
        margin: <?php echo esc_attr($atts['title_margin']); ?>;
    }
    .drpg-item img {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }
    .drpg-video-icon {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        pointer-events: none;
        opacity: 0;
        transition: opacity 0.3s;
        color: <?php echo esc_attr($atts['icon_color']); ?>;
        font-size: <?php echo esc_attr($atts['icon_size']); ?>;
    }
    .drpg-thumb:hover .drpg-video-icon {
        opacity: 1;
    }
    .drpg-title-overlay {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        margin: <?php echo esc_attr($atts['title_margin']); ?>;
        padding: <?php echo esc_attr($atts['title_padding']); ?>;
        background: rgba(0,0,0,0.6);
        text-align: <?php echo esc_attr($atts['title_align']); ?>;
    }
    .drpg-title-under {
        text-align: <?php echo esc_attr($atts['title_align']); ?>;
        margin: <?php echo esc_attr($atts['title_margin']); ?>;
        padding: <?php echo esc_attr($atts['title_padding']); ?>;
    }
    .drpg-item a {
        color: <?php echo esc_attr($atts['link_color']); ?>;
        font-family: <?php echo esc_attr($atts['link_family']); ?>;
        font-size: <?php echo esc_attr($atts['link_size']); ?>;
        line-height: <?php echo esc_attr($atts['line_height']); ?>;
        text-shadow: <?php echo esc_attr($atts['text_shadow']); ?>;
        text-decoration: <?php echo esc_attr($atts['link_decoration']); ?>;
    }
    .drpg-excerpt {
        text-align: left;
    }
    </style>
    <div id="<?php echo esc_attr($container_id); ?>" class="drpg-container drpg-<?php echo $layout; ?>">
        <?php echo drpg_get_items_html($atts); ?>
    </div>
    <?php if ($atts['auto_fetch']) : ?>
    <script type="text/javascript">
    window.drpgQueues = window.drpgQueues || [];
    window.drpgQueues.push({
        container: '<?php echo esc_js($container_id); ?>',
        atts: <?php echo wp_json_encode($atts); ?>,
        interval: <?php echo intval($atts['refresh_interval']); ?>
    });
    </script>
    <?php endif; ?>
    <?php
    return ob_get_clean();
}
add_shortcode('rest_post_grid', 'drpg_render_post_grid');

function drpg_ajax_fetch_posts() {
    check_ajax_referer('drpg_fetch', 'nonce');
    $atts = [];
    if (!empty($_POST['atts'])) {
        $atts = json_decode(stripslashes($_POST['atts']), true);
    }
    echo drpg_get_items_html((array) $atts);
    wp_die();
}
add_action('wp_ajax_drpg_fetch_posts', 'drpg_ajax_fetch_posts');
add_action('wp_ajax_nopriv_drpg_fetch_posts', 'drpg_ajax_fetch_posts');

function drpg_enqueue_assets() {
    wp_enqueue_style('dashicons');
    wp_enqueue_style('drpg-style', plugins_url('block/style.css', __FILE__), ['dashicons'], filemtime(plugin_dir_path(__FILE__).'block/style.css'));
    wp_register_script(
        'drpg-fetch',
        plugins_url('js/fetch.js', __FILE__),
        [],
        filemtime(plugin_dir_path(__FILE__) . 'js/fetch.js'),
        true
    );
    wp_localize_script('drpg-fetch', 'drpg_ajax', [
        'url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('drpg_fetch')
    ]);
    wp_enqueue_script('drpg-fetch');
}
add_action('wp_enqueue_scripts', 'drpg_enqueue_assets');

function drpg_register_block() {
    wp_register_script(
        'drpg-block',
        plugins_url('block/block.js', __FILE__),
        ['wp-blocks', 'wp-element', 'wp-components', 'wp-block-editor', 'wp-i18n', 'wp-server-side-render'],
        filemtime(plugin_dir_path(__FILE__) . 'block/block.js')
    );
    wp_localize_script('drpg-block', 'drpg_defaults', [
        'api_url' => get_option('drpg_api_url', '')
    ]);

    wp_register_style(
        'drpg-editor-style',
        plugins_url('block/editor.css', __FILE__),
        ['dashicons'],
        filemtime(plugin_dir_path(__FILE__) . 'block/editor.css')
    );
    wp_register_style(
        'drpg-style',
        plugins_url('block/style.css', __FILE__),
        ['dashicons'],
        filemtime(plugin_dir_path(__FILE__) . 'block/style.css')
    );

    register_block_type('danko-rest-post-grid/block', [
        'editor_script' => 'drpg-block',
        'editor_style' => 'drpg-editor-style',
        'style' => 'drpg-style',
        'render_callback' => 'drpg_render_post_grid',
        'attributes' => [
            'api_url'    => [ 'type' => 'string', 'default' => '' ],
            'layout'     => [ 'type' => 'string', 'default' => 'grid' ],
            'columns'    => [ 'type' => 'number', 'default' => 4 ],
            'font_family'=> [ 'type' => 'string', 'default' => 'inherit' ],
            'font_size'  => [ 'type' => 'string', 'default' => 'inherit' ],
            'link_color' => [ 'type' => 'string', 'default' => '#000000' ],
            'link_size'  => [ 'type' => 'string', 'default' => 'inherit' ],
            'link_family'=> [ 'type' => 'string', 'default' => 'inherit' ],
            'icon_color' => [ 'type' => 'string', 'default' => '#ffffff' ],
            'icon_size'  => [ 'type' => 'string', 'default' => '32px' ],
            'overlay_title'=> [ 'type' => 'boolean', 'default' => false ],
            'line_height'  => [ 'type' => 'string', 'default' => '1.2' ],
            'text_shadow'  => [ 'type' => 'string', 'default' => 'none' ],
            'box_shadow'   => [ 'type' => 'string', 'default' => 'none' ],
            'border_radius'=> [ 'type' => 'string', 'default' => '0' ],
            'padding'      => [ 'type' => 'string', 'default' => '10px' ],
            'margin'       => [ 'type' => 'string', 'default' => '0' ],
            'title_padding'=> [ 'type' => 'string', 'default' => '0' ],
            'title_margin' => [ 'type' => 'string', 'default' => '0' ],
            'link_decoration' => [ 'type' => 'string', 'default' => 'none' ],
            'title_align'  => [ 'type' => 'string', 'default' => 'center' ],
            'order_by'     => [ 'type' => 'string', 'default' => 'date' ],
            'auto_fetch' => [ 'type' => 'boolean', 'default' => true ],
            'refresh_interval' => [ 'type' => 'number', 'default' => 60 ],
            'show_meta'    => [ 'type' => 'boolean', 'default' => false ],
        ],
    ]);
}
add_action('init', 'drpg_register_block');

// Settings page
function drpg_register_settings() {
    register_setting('drpg_settings', 'drpg_api_url');
}
add_action('admin_init', 'drpg_register_settings');

function drpg_settings_page() {
    ?>
    <div class="wrap">
        <h1>Danko REST Post Grid Settings</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('drpg_settings');
            do_settings_sections('drpg_settings');
            ?>
            <table class="form-table" role="presentation">
                <tr valign="top">
                    <th scope="row"><label for="drpg_api_url">Default API URL</label></th>
                    <td><input type="text" id="drpg_api_url" name="drpg_api_url" value="<?php echo esc_attr(get_option('drpg_api_url', '')); ?>" class="regular-text" /></td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}

function drpg_add_settings_page() {
    add_options_page(
        'Danko REST Post Grid',
        'Danko Post Grid',
        'manage_options',
        'drpg',
        'drpg_settings_page'
    );
}
add_action('admin_menu', 'drpg_add_settings_page');
