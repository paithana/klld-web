<?php
/**
 * Google Things to Do (GTTD) Feed
 * Generates a high-quality product feed for Google Things to Do and Merchant Center.
 * Supports JSON and XML formats.
 * 
 * Usage:
 * /google-tours-feed.php                  -> JSON (Default)
 * /google-tours-feed.php?format=xml       -> XML
 * /google-tours-feed.php?preview=1        -> HTML Table (for debugging)
 */

// Load WordPress
if ( ! defined( 'ABSPATH' ) ) {
    $search_paths = [
        __DIR__ . '/wp-load.php',
        dirname(__DIR__, 2) . '/wp-load.php',
        dirname(__DIR__, 3) . '/wp-load.php',
        dirname(__DIR__, 4) . '/wp-load.php',
    ];
    foreach ($search_paths as $path) {
        if (file_exists($path)) {
            require_once $path;
            break;
        }
    }
    if ( ! defined( 'ABSPATH' ) ) {
        die("Fatal: Could not find wp-load.php.");
    }
}

// Security: HTTP Basic Auth for Google Merchant Center
$auth_user = 'mc-sftp-5520609361'; 
$auth_pass = ':(2Q>%zv4e';

if (PHP_SAPI !== 'cli') {
    $user = $_SERVER['PHP_AUTH_USER'] ?? '';
    $pass = $_SERVER['PHP_AUTH_PW'] ?? '';

    // LiteSpeed / CGI Workaround
    if (empty($user) && isset($_SERVER['HTTP_AUTHORIZATION'])) {
        if (preg_match('/Basic\s+(.*)$/i', $_SERVER['HTTP_AUTHORIZATION'], $matches)) {
            list($user, $pass) = explode(':', base64_decode($matches[1]));
        }
    }

    if ($user !== $auth_user || $pass !== $auth_pass) {
        header('WWW-Authenticate: Basic realm="GTTD Feed"');
        header('HTTP/1.0 401 Unauthorized');
        die('Authentication Required');
    }
}

// Security: Optional secret key (Legacy support)
$secret_key = 'kld_feed_2024';
if (!is_admin() && PHP_SAPI !== 'cli' && isset($_GET['key']) && $_GET['key'] !== $secret_key) {
    http_response_code(403);
    die('Forbidden');
}

// Detect CLI vs Browser and parse arguments
if (PHP_SAPI === 'cli') {
    $args = $_GET; // Support manual overrides if included internally
    if (isset($argv)) {
        foreach ($argv as $arg) {
            if (strpos($arg, '=') !== false) {
                list($key, $val) = explode('=', $arg);
                $args[$key] = $val;
            }
        }
    }
} else {
    $args = $_GET;
}

$format  = $args['format'] ?? 'json';
$preview = isset($args['preview']);

// GTTD Configuration
$merchant_id = '5520609361';
$official_address = '21/5 Moo 7, Khuk Khak, Takua Pa, Phang Nga 82220, Thailand';

// Query Tours
$args = array(
    'post_type' => 'st_tours',
    'posts_per_page' => -1,
    'post_status' => 'publish',
);

$query = new WP_Query($args);
$feed = array();

if ($query->have_posts()) {
    while ($query->have_posts()) {
        $query->the_post();
        $id = get_the_ID();
        
        // Basic Info
        $title = html_entity_decode(get_the_title(), ENT_QUOTES, 'UTF-8');
        $permalink = get_the_permalink();
        $excerpt = get_the_excerpt();
        $content = get_the_content();
        $description = !empty($excerpt) ? $excerpt : wp_trim_words($content, 60, '...');
        $description = html_entity_decode(strip_tags(strip_shortcodes($description)), ENT_QUOTES, 'UTF-8');

        // Price Logic
        // Traveler theme stores price in 'st_tour_price' or 'price'
        $price = get_post_meta($id, 'st_tour_price', true);
        if (!$price) $price = get_post_meta($id, 'price', true);
        
        // Handle "Starting from" logic if available via Traveler classes
        if (class_exists('STTour')) {
            $info_price = STTour::get_info_price($id);
            if (!empty($info_price['price_new'])) {
                $price = $info_price['price_new'];
            }
        }

        // WooCommerce Price Overwrite (Priority)
        // Check if there is a linked WooCommerce product
        $wc_product_ids = get_posts([
            'post_type' => 'product',
            'meta_key' => '_st_booking_id',
            'meta_value' => $id,
            'posts_per_page' => 1,
            'fields' => 'ids'
        ]);

        if (!empty($wc_product_ids)) {
            $wc_prod_id = $wc_product_ids[0];
            $wc_product = wc_get_product($wc_prod_id);
            if ($wc_product) {
                $wc_price = $wc_product->get_price();
                if ($wc_price > 0) {
                    $price = $wc_price;
                }
            }
        }
        
        $currency = function_exists('st_get_default_currency') ? st_get_default_currency() : 'THB';
        
        // Images (Featured + Gallery)
        $image_id = get_post_thumbnail_id($id);
        $image_url = $image_id ? wp_get_attachment_image_url($image_id, 'full') : '';
        
        // Location
        $address = get_post_meta($id, 'address', true);
        
        // Review Statistics
        $rating = get_post_meta($id, 'rate_review', true) ?: 5;
        $review_count = get_post_meta($id, 'total_review', true) ?: 0;

        // OTA Identifiers for GTTD mapping
        $gyg_id = get_post_meta($id, '_gyg_activity_id', true);
        $viator_id = get_post_meta($id, '_viator_activity_id', true);
        $ta_id = get_post_meta($id, '_tripadvisor_activity_id', true);

        // Categories / Attributes
        $terms = get_the_terms($id, 'st_tour_type');
        $category = (!is_wp_error($terms) && !empty($terms)) ? $terms[0]->name : 'Sightseeing Tour';

        // Duration Mapping
        $raw_duration = get_post_meta($id, 'duration_day', true);
        $iso_duration = 'PT8H'; // Default Full Day
        if (strpos(strtolower($raw_duration), 'day') !== false) {
            $num = (int)preg_replace('/[^0-9]/', '', $raw_duration) ?: 1;
            $iso_duration = $num > 1 ? "P{$num}D" : "PT8H";
        } elseif (strpos(strtolower($raw_duration), 'hour') !== false) {
            $num = (int)preg_replace('/[^0-9]/', '', $raw_duration) ?: 4;
            $iso_duration = "PT{$num}H";
        }

        // GTTD specific mapping
        $product = array(
            'id' => (string)$id,
            'title' => $title,
            'description' => $description,
            'link' => $permalink,
            'image_link' => $image_url,
            'price' => array(
                'amount' => (float)$price,
                'currency' => $currency
            ),
            'brand' => 'Khao Lak Land Discovery',
            'google_product_category' => 'Travel & Events > Travel Services > Sightseeing Tours',
            'product_type' => 'Tour',
            'rating' => array(
                'average' => (float)$rating,
                'count' => (int)$review_count
            ),
            'location' => array(
                'address' => $address ?: $official_address,
                'country' => 'Thailand',
                'postal_code' => '82220'
            ),
            'merchant_id' => $merchant_id,
            'inventory_types' => array('INVENTORY_TYPE_OPERATOR_DIRECT'),
            'admission_ticket_type' => 'tours',
            'confirmation_type' => 'INSTANT',
            'duration' => $iso_duration,
            'booking_options' => array(
                'adult' => array(
                    'price' => (float)get_post_meta($id, 'adult_price', true),
                    'currency' => $currency
                ),
                'child' => array(
                    'price' => (float)get_post_meta($id, 'child_price', true),
                    'currency' => $currency
                )
            ),
            'ota_ids' => array(
                'getyourguide' => $gyg_id,
                'viator' => $viator_id,
                'tripadvisor' => $ta_id
            ),
            'last_updated' => get_the_modified_date('c')
        );

        // Map through values and decode entities recursively
        $product = array_map(function($val) {
            if (is_string($val)) return html_entity_decode($val, ENT_QUOTES, 'UTF-8');
            if (is_array($val)) {
                return array_map(function($v) {
                    return is_string($v) ? html_entity_decode($v, ENT_QUOTES, 'UTF-8') : $v;
                }, $val);
            }
            return $val;
        }, $product);

        $feed[] = apply_filters('klld_gttd_product', $product, $id);
    }
    wp_reset_postdata();
}

// ── Output Management ──────────────────────────────────────────────────────

if ($preview && current_user_can('manage_options')) {
    // Premium Debug UI
    if (!defined('KLLD_DASHBOARD_PREVIEW')) {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>GTTD Feed Preview | KLD</title>
<?php } ?>
        <style>
            <?php if (!defined('KLLD_DASHBOARD_PREVIEW')): ?>
            body { font-family: 'Inter', system-ui, sans-serif; background: #f8fafc; color: #1e293b; padding: 40px; }
            .container { max-width: 1200px; margin: 0 auto; background: #fff; padding: 30px; border-radius: 12px; box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1); }
            <?php else: ?>
            .container { background: #fff; padding: 10px; border-radius: 8px; }
            <?php endif; ?>
            h1.feed-title { font-size: 24px; color: #0ea5e9; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; }
            table.feed-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
            table.feed-table th { text-align: left; padding: 12px; background: #f1f5f9; border-bottom: 2px solid #e2e8f0; font-size: 13px; text-transform: uppercase; color: #64748b; }
            table.feed-table td { padding: 12px; border-bottom: 1px solid #e2e8f0; font-size: 14px; vertical-align: top; }
            table.feed-table tr:hover { background: #f8fafc; }
            .badge { padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: 600; }
            .badge-price { background: #dcfce7; color: #15803d; }
            .badge-rating { background: #fef9c3; color: #854d0e; }
            .img-preview { width: 80px; height: 60px; object-fit: cover; border-radius: 4px; }
        </style>
<?php if (!defined('KLLD_DASHBOARD_PREVIEW')) { ?>
    </head>
    <body>
<?php } ?>
        <div class="container">
            <h1 class="feed-title">🎯 Google Things to Do Feed Preview</h1>
            <p>Displaying <?php echo count($feed); ?> products. Formats: 
                <a href="<?php echo get_stylesheet_directory_uri(); ?>/inc/ota-tools/google-tours-feed.php?format=json" target="_blank">JSON</a> | 
                <a href="<?php echo get_stylesheet_directory_uri(); ?>/inc/ota-tools/google-tours-feed.php?format=xml" target="_blank">XML</a> |
                Merchant ID: <code><?php echo $merchant_id; ?></code>
            </p>
            <table class="feed-table">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Price</th>
                        <th>Rating</th>
                        <th>OTA IDs</th>
                        <th>Address</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($feed as $p): ?>
                    <tr>
                        <td><img src="<?php echo $p['image_link']; ?>" class="img-preview"></td>
                        <td><code>#<?php echo $p['id']; ?></code></td>
                        <td><strong><?php echo $p['title']; ?></strong></td>
                        <td><span class="badge badge-price"><?php echo $p['price']['amount']; ?> <?php echo $p['price']['currency']; ?></span></td>
                        <td><span class="badge badge-rating">⭐ <?php echo $p['rating']['average']; ?> (<?php echo $p['rating']['count']; ?>)</span></td>
                        <td>
                            <?php if ($p['ota_ids']['getyourguide']): ?><div><small>GYG: <code><?php echo $p['ota_ids']['getyourguide']; ?></code></small></div><?php endif; ?>
                            <?php if ($p['ota_ids']['viator']): ?><div><small>Via: <code><?php echo $p['ota_ids']['viator']; ?></code></small></div><?php endif; ?>
                            <?php if ($p['ota_ids']['tripadvisor']): ?><div><small>TA: <code><?php echo $p['ota_ids']['tripadvisor']; ?></code></small></div><?php endif; ?>
                        </td>
                        <td><small><?php echo $p['location']['address']; ?></small></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
<?php if (!defined('KLLD_DASHBOARD_PREVIEW')) { ?>
    </body>
    </html>
<?php 
    exit;
}
}

// Ensure we don't fall through to JSON/XML if we just finished a dashboard preview
if (defined('KLLD_DASHBOARD_PREVIEW')) {
    return;
}

if ($format === 'xml') {
    header('Content-Type: application/xml; charset=utf-8');
    $xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><rss xmlns:g="http://base.google.com/ns/1.0" version="2.0"/>');
    $channel = $xml->addChild('channel');
    $channel->addChild('title', 'Khao Lak Land Discovery Tours');
    $channel->addChild('link', get_bloginfo('url'));
    $channel->addChild('description', 'High-quality tours in Khao Lak, Thailand');

    foreach ($feed as $p) {
        $item = $channel->addChild('item');
        $item->addChild('g:id', $p['id'], 'http://base.google.com/ns/1.0');
        
        // Use a safe way to add text nodes to avoid entity issues
        $title_node = $item->addChild('title');
        $title_node[0] = $p['title'];
        
        $desc_node = $item->addChild('description');
        $desc_node[0] = $p['description'];

        $item->addChild('link', $p['link']);
        $item->addChild('g:image_link', $p['image_link'], 'http://base.google.com/ns/1.0');
        $item->addChild('g:price', $p['price']['amount'] . ' ' . $p['price']['currency'], 'http://base.google.com/ns/1.0');
        $item->addChild('g:brand', $p['brand'], 'http://base.google.com/ns/1.0');
        
        $item->addChild('g:google_product_category', null, 'http://base.google.com/ns/1.0')[0] = $p['google_product_category'];
        $item->addChild('g:availability', 'in_stock', 'http://base.google.com/ns/1.0');
        
        // Custom attributes for GTTD
        $loc_node = $item->addChild('g:location_address', null, 'http://base.google.com/ns/1.0');
        $loc_node[0] = $p['location']['address'];
        
        $item->addChild('g:rating_average', (string)$p['rating']['average'], 'http://base.google.com/ns/1.0');
        $item->addChild('g:rating_count', (string)$p['rating']['count'], 'http://base.google.com/ns/1.0');

        if ($p['ota_ids']['getyourguide']) {
            $gyg_node = $item->addChild('g:getyourguide_id', null, 'http://base.google.com/ns/1.0');
            $gyg_node[0] = $p['ota_ids']['getyourguide'];
        }
        if ($p['ota_ids']['viator']) {
            $via_node = $item->addChild('g:viator_id', null, 'http://base.google.com/ns/1.0');
            $via_node[0] = $p['ota_ids']['viator'];
        }
        if ($p['ota_ids']['tripadvisor']) {
            $ta_node = $item->addChild('g:tripadvisor_id', null, 'http://base.google.com/ns/1.0');
            $ta_node[0] = $p['ota_ids']['tripadvisor'];
        }
        
        $item->addChild('g:merchant_id', $merchant_id, 'http://base.google.com/ns/1.0');
        $item->addChild('g:inventory_type', 'INVENTORY_TYPE_OPERATOR_DIRECT', 'http://base.google.com/ns/1.0');
        $item->addChild('g:admission_ticket_type', $p['admission_ticket_type'], 'http://base.google.com/ns/1.0');
        $item->addChild('g:confirmation_type', $p['confirmation_type'], 'http://base.google.com/ns/1.0');
        $item->addChild('g:duration', $p['duration'], 'http://base.google.com/ns/1.0');
        
        // Structured Booking Options (Adult/Child)
        foreach ($p['booking_options'] as $type => $opt) {
            if ($opt['price'] > 0) {
                // We use the price extension for individual ticket types
                $item->addChild('g:'.$type.'_price', $opt['price'] . ' ' . $opt['currency'], 'http://base.google.com/ns/1.0');
            }
        }
    }
    echo $xml->asXML();
} else {
    // Default JSON
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(array(
        'status' => 'success',
        'merchant_id' => $merchant_id,
        'inventory_type_default' => 'INVENTORY_TYPE_OPERATOR_DIRECT',
        'last_updated' => date('c'),
        'count' => count($feed),
        'items' => $feed
    ), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
}
