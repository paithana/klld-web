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
    $search_path = __DIR__;
    $found = false;
    for ($i = 0; $i < 10; $i++) {
        if (file_exists($search_path . '/wp-load.php')) {
            require_once $search_path . '/wp-load.php';
            $found = true;
            break;
        }
        $parent = dirname($search_path);
        if ($parent === $search_path) break;
        $search_path = $parent;
    }
    
    if (!$found) {
        $abs_path = dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))) . '/wp-load.php';
        if (file_exists($abs_path)) {
            require_once $abs_path;
        } else {
            die("Fatal: Could not find wp-load.php");
        }
    }
}

// Security: Optional secret key if needed
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
            'product_type' => $category,
            'availability' => 'in_stock',
            'google_product_category' => 'Travel & Events > Travel Services > Sightseeing Tours',
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

        // Map through string values and decode entities
        foreach ($product as $key => $value) {
            if (is_string($value)) {
                $product[$key] = html_entity_decode($value, ENT_QUOTES, 'UTF-8');
            }
        }

        $feed[] = apply_filters('klld_gttd_product', $product, $id);
    }
    wp_reset_postdata();
}

// ── Output Management ──────────────────────────────────────────────────────

if ($preview && current_user_can('manage_options')) {
    // Premium Debug UI
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>GTTD Feed Preview | KLD</title>
        <style>
            body { font-family: 'Inter', system-ui, sans-serif; background: #f8fafc; color: #1e293b; padding: 40px; }
            .container { max-width: 1200px; margin: 0 auto; background: #fff; padding: 30px; border-radius: 12px; box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1); }
            h1 { font-size: 24px; color: #0ea5e9; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; }
            table { width: 100%; border-collapse: collapse; margin-top: 20px; }
            th { text-align: left; padding: 12px; background: #f1f5f9; border-bottom: 2px solid #e2e8f0; font-size: 13px; text-transform: uppercase; color: #64748b; }
            td { padding: 12px; border-bottom: 1px solid #e2e8f0; font-size: 14px; vertical-align: top; }
            tr:hover { background: #f8fafc; }
            .badge { padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: 600; }
            .badge-price { background: #dcfce7; color: #15803d; }
            .badge-rating { background: #fef9c3; color: #854d0e; }
            .img-preview { width: 80px; height: 60px; object-fit: cover; border-radius: 4px; }
        </style>
    </head>
    <body>
        <div class="container">
            <h1>🎯 Google Things to Do Feed Preview</h1>
            <p>Displaying <?php echo count($feed); ?> products. Formats: 
                <a href="?format=json">JSON</a> | 
                <a href="?format=xml">XML</a> |
                Merchant ID: <code><?php echo $merchant_id; ?></code>
            </p>
            <table>
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
    </body>
    </html>
    <?php
    exit;
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
        
        // Category should also be safe
        $cat_node = $item->addChild('g:google_product_category', null, 'http://base.google.com/ns/1.0');
        $cat_node[0] = $p['google_product_category'];
        $item->addChild('g:availability', $p['availability'], 'http://base.google.com/ns/1.0');
        
        // Custom attributes for GTTD
        $item->addChild('g:location_address', htmlspecialchars($p['location']['address']), 'http://base.google.com/ns/1.0');
        $item->addChild('g:rating_average', (string)$p['rating']['average'], 'http://base.google.com/ns/1.0');
        $item->addChild('g:rating_count', (string)$p['rating']['count'], 'http://base.google.com/ns/1.0');

        if ($p['ota_ids']['getyourguide']) $item->addChild('g:getyourguide_id', $p['ota_ids']['getyourguide'], 'http://base.google.com/ns/1.0');
        if ($p['ota_ids']['viator'])       $item->addChild('g:viator_id', $p['ota_ids']['viator'], 'http://base.google.com/ns/1.0');
        if ($p['ota_ids']['tripadvisor'])  $item->addChild('g:tripadvisor_id', $p['ota_ids']['tripadvisor'], 'http://base.google.com/ns/1.0');
        
        $item->addChild('g:merchant_id', $merchant_id, 'http://base.google.com/ns/1.0');
        $item->addChild('g:inventory_type', 'INVENTORY_TYPE_OPERATOR_DIRECT', 'http://base.google.com/ns/1.0');
        $item->addChild('g:admission_ticket_type', $p['admission_ticket_type'], 'http://base.google.com/ns/1.0');
        
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
