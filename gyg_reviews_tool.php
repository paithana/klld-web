<?php
/**
 * GYG Reviews Tool - WordPress Admin Only
 * Requires: logged-in WordPress user with manage_options capability (Administrator)
 */
define('SHORTINIT', false);

// Load WordPress
$wp_load = dirname(__FILE__) . '/wp-load.php';
if (!file_exists($wp_load)) {
    http_response_code(503);
    die('WordPress not found.');
}
require_once $wp_load;

// Must be logged in AND have admin capability
if (!is_user_logged_in()) {
    $redirect = urlencode('https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
    wp_redirect(wp_login_url('https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']));
    exit;
}

if (!current_user_can('manage_options')) {
    wp_die(
        '<h1>Access Denied</h1><p>You need Administrator privileges to access this tool.</p>',
        'Access Denied',
        ['response' => 403, 'back_link' => true]
    );
}

// ── AJAX Handler: Save Mappings ──────────────────────────────────────────
if (isset($_POST['action']) && $_POST['action'] === 'save_ota_mappings') {
    $mappings = json_decode(stripslashes($_POST['mappings']), true);
    if (!is_array($mappings)) {
        wp_send_json_error('Invalid mapping data.');
    }

    $count = 0;
    foreach ($mappings as $map) {
        $wp_id = (int)($map['wpId'] ?? 0);
        if (!$wp_id) continue;

        update_post_meta($wp_id, '_gyg_activity_id', sanitize_text_field($map['gygId'] ?? ''));
        update_post_meta($wp_id, '_viator_activity_id', sanitize_text_field($map['viatorId'] ?? ''));
        update_post_meta($wp_id, '_tripadvisor_activity_id', sanitize_text_field($map['taId'] ?? ''));
        update_post_meta($wp_id, '_gmb_id', sanitize_text_field($map['gmbId'] ?? ''));
        update_post_meta($wp_id, '_trustpilot_id', sanitize_text_field($map['tpId'] ?? ''));
        $count++;
    }

    wp_send_json_success(['message' => "Saved $count mappings successfully."]);
}

// ── AJAX Handler: Proxy Discovery (Fixes CORS/404) ────────────────────────
if (isset($_POST['action']) && $_POST['action'] === 'proxy_ota_discover') {
    $supplier_id = 203466;
    $offset = (int)($_POST['offset'] ?? 0);
    $limit  = (int)($_POST['limit'] ?? 50);

    // Try multiple possible GYG endpoints server-side
    $urls = [
        "https://travelers-api.getyourguide.com/suppliers/khao-lak-land-discovery-co-ltd-s{$supplier_id}/activities?limit={$limit}&offset={$offset}",
        "https://travelers-api.getyourguide.com/suppliers/{$supplier_id}/activities?limit={$limit}&offset={$offset}",
        "https://travelers-api.getyourguide.com/activities?supplierId={$supplier_id}&limit={$limit}&offset={$offset}",
        "https://www.getyourguide.com/api/catalog/v1/suppliers/{$supplier_id}/activities?limit={$limit}&offset={$offset}"
    ];

    $response_data = null;
    foreach ($urls as $url) {
        $resp = wp_remote_get($url, [
            'timeout' => 15,
            'headers' => [
                'Accept' => 'application/json',
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36'
            ]
        ]);
        if (!is_wp_error($resp) && wp_remote_retrieve_response_code($resp) === 200) {
            $response_data = wp_remote_retrieve_body($resp);
            break;
        }
    }

    if ($response_data) {
        echo $response_data;
    } else {
        echo json_encode(['error' => 'Could not fetch activities from GYG endpoints (Blocked or Invalid ID).']);
    }
    exit;
}

// ── AJAX Handler: Universal OTA Proxy Fetch ──────────────────────────
if (isset($_POST['action']) && $_POST['action'] === 'proxy_ota_fetch') {
    $url = $_POST['url'] ?? '';
    if (!$url) wp_send_json_error('URL missing');

    $is_ta = (strpos($url, 'tripadvisor.com') !== false);

    $resp = wp_remote_get($url, [
        'timeout' => 20,
        'headers' => [
            'Accept' => ($is_ta ? 'text/html' : 'application/json'),
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36'
        ]
    ]);
    if (is_wp_error($resp)) wp_send_json_error($resp->get_error_message());
    
    $body = wp_remote_retrieve_body($resp);

    if ($is_ta && strpos($body, 'reviewCard') !== false) {
        // Basic TripAdvisor Scraper Logic
        $reviews = [];
        $dom = new DOMDocument();
        @$dom->loadHTML('<?xml encoding="UTF-8">' . $body);
        $xpath = new DOMXPath($dom);
        
        // This is a simplified scraper for modern TA layout
        $cards = $xpath->query("//div[contains(@data-automation, 'reviewCard')]");
        foreach ($cards as $card) {
            $id = $card->getAttribute('id') ?: bin2hex(random_bytes(8));
            $text = $xpath->query(".//span[contains(@class, 'ySdfQ')]", $card)->item(0)?->nodeValue ?? '';
            $author = $xpath->query(".//span[contains(@class, 'biGQs')]", $card)->item(0)?->nodeValue ?? 'TA Traveler';
            
            // Extract rating from SVG or bubble class
            $bubbles = $xpath->query(".//span[contains(@class, 'ui_bubble_rating')]", $card)->item(0);
            $rating = 5;
            if ($bubbles) {
                $class = $bubbles->getAttribute('class');
                if (preg_match('/bubble_(\d+)/', $class, $m)) $rating = intval($m[1]) / 10;
            }

            if ($text) {
                $reviews[] = [
                    'id' => $id,
                    'text' => $text,
                    'author' => $author,
                    'rating' => $rating
                ];
            }
        }
        echo json_encode(['data' => ['reviews' => $reviews]]);
        exit;
    }

    echo $body;
    exit;
}

// All good — serve the tool
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GYG Reviews → SQL Generator | KLD</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Segoe UI', sans-serif;
            background: #0f172a;
            color: #e2e8f0;
            min-height: 100vh;
            padding: 2rem;
        }

        h1 {
            color: #38bdf8;
            margin-bottom: 0.25rem;
            font-size: 1.6rem;
        }

        .subtitle {
            color: #64748b;
            margin-bottom: 2rem;
            font-size: 0.9rem;
        }

        .card {
            background: #1e293b;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            border: 1px solid #334155;
        }

        h2 {
            color: #94a3b8;
            font-size: 1rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 1rem;
        }

        .tour-row {
            display: grid;
            grid-template-columns: 80px 80px 80px 80px 80px 80px 1fr;
            gap: 0.5rem;
            align-items: center;
            margin-bottom: 0.5rem;
        }

        .tour-header {
            display: grid;
            grid-template-columns: 80px 80px 80px 80px 80px 80px 1fr;
            gap: 0.5rem;
            font-size: 0.65rem;
            color: #64748b;
            text-transform: uppercase;
            font-weight: 700;
            margin-bottom: 0.25rem;
            padding: 0 0.5rem;
        }

        label {
            color: #94a3b8;
            font-size: 0.85rem;
            display: block;
            margin-bottom: 0.25rem;
        }

        input {
            width: 100%;
            padding: 0.5rem 0.75rem;
            background: #0f172a;
            border: 1px solid #334155;
            border-radius: 6px;
            color: #e2e8f0;
            font-size: 0.9rem;
        }

        input:focus {
            outline: none;
            border-color: #38bdf8;
        }

        .btn {
            padding: 0.65rem 1.5rem;
            border-radius: 8px;
            font-size: 0.95rem;
            font-weight: 600;
            cursor: pointer;
            border: none;
            transition: all 0.2s;
        }

        .btn-primary {
            background: #0ea5e9;
            color: #fff;
        }

        .btn-primary:hover {
            background: #38bdf8;
        }

        .btn-primary:disabled {
            background: #334155;
            color: #64748b;
            cursor: not-allowed;
        }

        .btn-success {
            background: #10b981;
            color: #fff;
        }

        .btn-success:hover {
            background: #34d399;
        }

        .btn-sm {
            padding: 0.4rem 1rem;
            font-size: 0.8rem;
        }

        .btn-danger {
            background: #ef4444;
        }

        .btn-danger:hover {
            background: #f87171;
        }

        .status-bar {
            display: flex;
            align-items: center;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .badge {
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .badge-blue {
            background: #0ea5e920;
            color: #38bdf8;
            border: 1px solid #0ea5e940;
        }

        .badge-green {
            background: #10b98120;
            color: #10b981;
            border: 1px solid #10b98140;
        }

        .badge-red {
            background: #ef444420;
            color: #ef4444;
            border: 1px solid #ef444440;
        }

        #progress {
            margin-top: 1rem;
        }

        .progress-bar {
            height: 6px;
            background: #334155;
            border-radius: 3px;
            overflow: hidden;
            margin-top: 0.5rem;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #0ea5e9, #38bdf8);
            transition: width 0.3s;
            width: 0%;
        }

        #log {
            background: #0f172a;
            border-radius: 8px;
            padding: 1rem;
            max-height: 200px;
            overflow-y: auto;
            font-family: monospace;
            font-size: 0.8rem;
            color: #94a3b8;
            margin-top: 1rem;
            border: 1px solid #1e293b;
        }

        .log-ok {
            color: #10b981;
        }

        .log-err {
            color: #ef4444;
        }

        .log-info {
            color: #38bdf8;
        }

        #sql-output {
            width: 100%;
            height: 350px;
            background: #0f172a;
            color: #a3e635;
            font-family: monospace;
            font-size: 0.75rem;
            border: 1px solid #334155;
            border-radius: 8px;
            padding: 1rem;
            resize: vertical;
            margin-top: 1rem;
        }

        .actions {
            display: flex;
            gap: 0.75rem;
            flex-wrap: wrap;
            margin-top: 1rem;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
            gap: 0.75rem;
            margin-top: 1rem;
        }

        .stat-box {
            background: #0f172a;
            border-radius: 8px;
            padding: 1rem;
            text-align: center;
            border: 1px solid #1e293b;
        }

        .stat-val {
            font-size: 1.8rem;
            font-weight: 700;
            color: #38bdf8;
        }

        .stat-lbl {
            font-size: 0.75rem;
            color: #64748b;
            margin-top: 0.25rem;
        }

        .tour-header {
            display: grid;
            grid-template-columns: 180px 1fr 1fr;
            gap: 0.75rem;
            margin-bottom: 0.5rem;
        }

        .tour-header span {
            font-size: 0.75rem;
            color: #64748b;
        }

        .add-row {
            color: #38bdf8;
            background: none;
            border: 1px dashed #334155;
            border-radius: 6px;
            padding: 0.4rem 1rem;
            cursor: pointer;
            font-size: 0.85rem;
            margin-top: 0.5rem;
            width: 100%;
        }

        .add-row:hover {
            background: #1e293b;
        }
    </style>
</head>

<body>

    <h1>🎯 GYG Reviews → SQL Generator</h1>
    <p class="subtitle">Fetches reviews from GetYourGuide API and generates WordPress import SQL · <strong style="color:#38bdf8">Admin only</strong></p>

    <div class="card" style="border-color:#0ea5e940;">
        <h2>⚡ Auto-Discover GYG Activity IDs</h2>
        <p style="font-size:0.85rem;color:#94a3b8;margin-bottom:1rem;">Fetches all activities from your GYG supplier page and auto-fills the Activity ID column by matching tour names. Run this first, then check matches below.</p>
        <div style="display:flex;gap:0.75rem;align-items:center;flex-wrap:wrap;">
            <button class="btn btn-primary" id="discover-btn" onclick="autoDiscover()">🔍 Auto-Discover from GYG</button>
            <span id="discover-status" style="font-size:0.85rem;color:#64748b;">Supplier: Khao Lak Land Discovery Co., Ltd. (s203466)</span>
        </div>
        <div id="discover-log" style="margin-top:0.75rem;font-family:monospace;font-size:0.8rem;color:#94a3b8;max-height:120px;overflow-y:auto;"></div>
        <div id="unmatched-box" style="display:none;margin-top:1rem;">
            <p style="font-size:0.8rem;color:#f59e0b;margin-bottom:0.5rem;">⚠ Unmatched GYG activities (assign manually if needed):</p>
            <div id="unmatched-list" style="font-family:monospace;font-size:0.75rem;color:#94a3b8;"></div>
        </div>
    </div>

    <div class="card" style="border-left:4px solid #10b981;">
        <h2>🤖 Automated Daily Sync</h2>
        <p style="font-size:0.85rem;color:#94a3b8;margin-bottom:0.5rem;">To automatically sync reviews every day, set up a <strong>Cron Job</strong> (in your hosting panel) to call this URL once a day:</p>
        <code style="display:block; background:#0f172a; padding:0.5rem; border-radius:4px; font-size:0.8rem; color:#10b981; margin-bottom:0.5rem;">
            https://khaolaklanddiscovery.com/ota_sync.php?secret=kld_sync_2024
        </code>
        <p style="font-size:0.75rem;color:#64748b;">This script will loop through all mapped tours and fetch new reviews without you having to open this tool.</p>
    </div>

    <div class="card">
        <h2>Tour Mapping (OTA Activity IDs → WordPress Post ID)</h2>
        <p style="font-size:0.8rem;color:#64748b;margin-bottom:1rem;">Enter the Activity IDs from GetYourGuide, Viator, and TripAdvisor. Map each to its <strong>English</strong> WordPress post. <strong style="color:#fbbf24;">Click "Save Mappings" to persist these IDs to your tour pages.</strong></p>
        
        <div class="tour-header">
            <span>GYG Activity ID</span>
            <span>Viator ID</span>
            <span>TA ID</span>
            <span>GMB ID</span>
            <span>TP ID</span>
            <span>WP ID</span>
            <span>Tour Name</span>
        </div>
        <div id="tour-rows">
            <?php
            $tour_ids = [37796, 14528, 14583, 14625, 14755, 14761, 14789, 14790, 14791, 15162, 15166, 15168, 16171, 49149, 16255, 16271, 16299, 16321, 16352, 16371, 27793, 28002, 28139, 14353, 52439];
            foreach ($tour_ids as $id) {
                $gyg = get_post_meta($id, '_gyg_activity_id', true);
                $via = get_post_meta($id, '_viator_activity_id', true);
                $ta  = get_post_meta($id, '_tripadvisor_activity_id', true);
                $gmb = get_post_meta($id, '_gmb_id', true);
                $tp  = get_post_meta($id, '_trustpilot_id', true);
                $title = get_the_title($id);
                if (!$title) $title = "Unknown Tour (#$id)";
                ?>
                <div class="tour-row">
                    <input type="number" class="gyg-id" placeholder="e.g. 409896" value="<?php echo esc_attr($gyg); ?>">
                    <input type="text" class="viator-id" placeholder="e.g. 123456P1" value="<?php echo esc_attr($via); ?>">
                    <input type="text" class="ta-id" placeholder="e.g. d12345" value="<?php echo esc_attr($ta); ?>">
                    <input type="text" class="gmb-id" placeholder="e.g. ChIJ..." value="<?php echo esc_attr($gmb); ?>">
                    <input type="text" class="tp-id" placeholder="TP" value="<?php echo esc_attr($tp); ?>">
                    <input type="number" class="wp-id" value="<?php echo $id; ?>">
                    <input type="text" class="tour-name" value="<?php echo esc_attr($title); ?>">
                </div>
                <?php
            }
            ?>
        </div>
        <div style="display:flex; justify-content:space-between; align-items:center; margin-top:1rem;">
            <button class="add-row" style="background:transparent; border:1px dashed #475569; color:#94a3b8; padding:0.4rem 1rem; border-radius:6px; cursor:pointer;" onclick="addRow()">+ Add another tour</button>
            <button class="btn btn-success" onclick="saveMappings()">💾 Save Mappings to WordPress</button>
            <a href="ota_auto_mapper.php" target="_blank" class="btn btn-secondary" style="background:#8b5cf6; text-decoration:none; display:inline-flex; align-items:center;">🚀 Auto-Detect & Save All (Background)</a>
        </div>
        </div>
    </div>

    <div class="card">
        <h2>Options</h2>
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:1rem;">
            <div>
                <label>Max reviews per activity (Auto-expanded if needed)</label>
                <input type="number" id="max-reviews" value="5000" min="10" max="10000">
            </div>
            <div>
                <label>Skip existing GYG review IDs (comma separated)</label>
                <input type="text" id="skip-ids" placeholder="Optional: 121069521, 120913329, ...">
            </div>
            <div>
                <label>Default star rating if missing</label>
                <input type="number" id="default-rating" value="5" min="1" max="5">
            </div>
        </div>
    </div>

    <div class="card">
        <div class="status-bar">
            <button class="btn btn-primary" id="fetch-btn" onclick="startFetch()">🚀 Fetch Reviews & Generate
                SQL</button>
            <span id="status-badge" class="badge badge-blue">Ready</span>
        </div>
        <div id="progress" style="display:none;">
            <div style="font-size:0.85rem;color:#94a3b8;" id="progress-text">Fetching...</div>
            <div class="progress-bar">
                <div class="progress-fill" id="progress-fill"></div>
            </div>
        </div>
        <div id="log"></div>
    </div>

    <div class="card" id="results-card" style="display:none;">
        <h2>Results</h2>
        <div class="stats-grid">
            <div class="stat-box">
                <div class="stat-val" id="stat-total">0</div>
                <div class="stat-lbl">Total Reviews</div>
            </div>
            <div class="stat-box">
                <div class="stat-val" id="stat-activities">0</div>
                <div class="stat-lbl">Activities</div>
            </div>
            <div class="stat-box">
                <div class="stat-val" id="stat-langs">0</div>
                <div class="stat-lbl">Languages</div>
            </div>
            <div class="stat-box">
                <div class="stat-val" id="stat-upserted">0</div>
                <div class="stat-lbl">Upserted (replaced)</div>
            </div>
            <div class="stat-box">
                <div class="stat-val" id="stat-avg">0</div>
                <div class="stat-lbl">Avg Rating</div>
            </div>
        </div>
        <div class="actions">
            <button class="btn btn-success" onclick="downloadSQL()">⬇ Download SQL File</button>
            <button class="btn btn-sm" onclick="copySQL()">📋 Copy to Clipboard</button>
            <button class="btn btn-sm btn-danger" onclick="clearAll()">🗑 Clear</button>
        </div>
        <textarea id="sql-output" readonly placeholder="SQL will appear here..."></textarea>
    </div>

    <script>
        let generatedSQL = '';
        let totalFetched = 0;

        // ── Auto-discover GYG Activity IDs from supplier page ─────────────────
        const SUPPLIER_ID = 203466;

        // WP post name → normalize for fuzzy match
        function normTitle(s) {
            return s.toLowerCase()
                .replace(/[^a-z0-9 ]/g, ' ')
                .replace(/\s+/g, ' ').trim();
        }

        // Jaccard word-set similarity
        function similarity(a, b) {
            const wa = new Set(normTitle(a).split(' ').filter(w => w.length > 2));
            const wb = new Set(normTitle(b).split(' ').filter(w => w.length > 2));
            const inter = [...wa].filter(w => wb.has(w)).length;
            const union = new Set([...wa, ...wb]).size;
            return union ? inter / union : 0;
        }

        async function autoDiscover() {
            const btn = document.getElementById('discover-btn');
            btn.disabled = true;
            const dlog = document.getElementById('discover-log');
            dlog.innerHTML = '';
            document.getElementById('unmatched-box').style.display = 'none';
            document.getElementById('discover-status').textContent = 'Fetching activities...';

            const dline = msg => { const d = document.createElement('div'); d.textContent = msg; dlog.appendChild(d); dlog.scrollTop = dlog.scrollHeight; };

            let activities = [];
            let offset = 0;
            const limit = 50;

            try {
                while (true) {
                    dline(`Proxying offset ${offset}...`);
                    const formData = new FormData();
                    formData.append('action', 'proxy_ota_discover');
                    formData.append('offset', offset);
                    formData.append('limit', limit);

                    const resp = await fetch(window.location.href, {
                        method: 'POST',
                        body: formData
                    });

                    if (!resp.ok) {
                        dline(`❌ Server proxy failed (HTTP ${resp.status}).`);
                        break;
                    }

                    const data = await resp.json();
                    const acts = data?.data?.activities ?? data?.activities ?? data?.data ?? [];
                    if (!Array.isArray(acts) || acts.length === 0) {
                        dline(`No more activities found.`);
                        break;
                    }

                    activities = activities.concat(acts);
                    dline(`✓ Got ${acts.length} activities (total: ${activities.length})`);
                    if (acts.length < limit) break;
                    offset += limit;
                }
            } catch(e) {
                dline(`Error: ${e.message}`);
            }

            if (!activities.length) {
                dline('❌ Could not fetch activities. Check console for API response structure.');
                document.getElementById('discover-status').textContent = 'Failed — check console (F12)';
                btn.disabled = false;
                return;
            }

            dline(`\n✅ Total activities found: ${activities.length}. Matching to WP posts...`);

            // Collect WP rows
            const rows = [...document.querySelectorAll('#tour-rows .tour-row')];
            let matched = 0;
            const unmatched = [];

            for (const act of activities) {
                const actId   = String(act.activityId ?? act.id ?? act.activity_id ?? '');
                const actTitle = act.title ?? act.name ?? act.activityTitle ?? '';
                if (!actId || !actTitle) continue;

                // Find best matching WP row
                let bestRow = null, bestScore = 0;
                for (const row of rows) {
                    const wpName = row.querySelector('.tour-name').value;
                    const score = similarity(actTitle, wpName);
                    if (score > bestScore) { bestScore = score; bestRow = row; }
                }

                if (bestScore >= 0.35 && bestRow) {
                    const gygInput = bestRow.querySelector('.gyg-id');
                    if (!gygInput.value) {
                        gygInput.value = actId;
                        if (bestScore > 0.8) {
                            gygInput.style.backgroundColor = '#065f46'; // High confidence
                            dline(`✅ Link (Match ${Math.round(bestScore*100)}%): "${actTitle}" → "${bestRow.querySelector('.tour-name').value}"`);
                        } else {
                            gygInput.style.backgroundColor = '#451a03'; // Partial match
                            dline(`⚠ Partial (Match ${Math.round(bestScore*100)}%): "${actTitle}" → "${bestRow.querySelector('.tour-name').value}"`);
                        }
                        matched++;
                    } else {
                        dline(`⚠ Already set: "${actTitle}" ID ${actId} (existing: ${gygInput.value})`);
                    }
                } else {
                    unmatched.push({ actId, actTitle, bestScore: bestScore.toFixed(2) });
                }
            }

            // After all activities processed, fill 0 for remainders
            rows.forEach(row => {
                const gygInput = row.querySelector('.gyg-id');
                if (!gygInput.value) {
                    gygInput.value = '0';
                    gygInput.style.opacity = '0.5';
                }
            });

            // Show unmatched
            if (unmatched.length) {
                document.getElementById('unmatched-box').style.display = 'block';
                const ul = document.getElementById('unmatched-list');
                ul.innerHTML = unmatched.map(u =>
                    `<div style="margin-bottom:0.25rem;">ID <strong style="color:#f59e0b">${u.actId}</strong> — ${u.actTitle} <span style="color:#475569">(best score: ${u.bestScore})</span></div>`
                ).join('');
            }

            document.getElementById('discover-status').textContent = `✓ ${matched} matched, ${unmatched.length} unmatched`;
            dline(`\nDone: ${matched} auto-filled, ${unmatched.length} unmatched (listed above).`);
            btn.disabled = false;
        }

        // ── Save Mappings to WordPress ──────────────────────────────────────────
        async function saveMappings() {
            const rows = [...document.querySelectorAll('#tour-rows .tour-row')];
            const mappings = rows.map(r => ({
                gygId: r.querySelector('.gyg-id').value,
                viatorId: r.querySelector('.viator-id').value,
                taId: r.querySelector('.ta-id').value,
                gmbId: r.querySelector('.gmb-id').value,
                tpId: r.querySelector('.tp-id').value,
                wpId: r.querySelector('.wp-id').value
            }));

            const btn = document.querySelector('button[onclick="saveMappings()"]');
            const oldText = btn.textContent;
            btn.disabled = true;
            btn.textContent = 'Saving...';

            try {
                const formData = new FormData();
                formData.append('action', 'save_ota_mappings');
                formData.append('mappings', JSON.stringify(mappings));

                const resp = await fetch(window.location.href, {
                    method: 'POST',
                    body: formData
                });
                const data = await resp.json();
                if (data.success) {
                    alert('✅ ' + data.data.message);
                } else {
                    alert('❌ Error: ' + (data.data ?? 'Unknown error'));
                }
            } catch (e) {
                alert('❌ Error: ' + e.message);
            } finally {
                btn.disabled = false;
                btn.textContent = oldText;
            }
        }

        // ── Add row ─────────────────────────────────────────────────────────────
        function addRow() {
            const row = document.createElement('div');
            row.className = 'tour-row';
            row.innerHTML = `
                <input type="number" class="gyg-id" placeholder="GYG">
                <input type="text" class="viator-id" placeholder="Viator">
                <input type="text" class="ta-id" placeholder="TA">
                <input type="text" class="gmb-id" placeholder="GMB">
                <input type="text" class="tp-id" placeholder="TP">
                <input type="number" class="wp-id" placeholder="WP ID">
                <input type="text" class="tour-name" placeholder="Tour name">
            `;
            document.getElementById('tour-rows').appendChild(row);
        }

        function log(msg, type = '') {
            const logEl = document.getElementById('log');
            const line = document.createElement('div');
            line.className = type ? `log-${type}` : '';
            line.textContent = `[${new Date().toLocaleTimeString()}] ${msg}`;
            logEl.appendChild(line);
            logEl.scrollTop = logEl.scrollHeight;
        }

        function setProgress(pct, text) {
            document.getElementById('progress-fill').style.width = pct + '%';
            document.getElementById('progress-text').textContent = text;
        }

        function escapeSQL(str) {
            if (str === null || str === undefined) return '';
            const s = String(str);
            return s.replace(/'/g, "''").replace(/\\/g, '\\\\');
        }

        function makeEmail(name, source = 'gyg') {
            const domains = { gyg: 'getyourguide.com', viator: 'viator.com', ta: 'tripadvisor.com', gmb: 'google.com', trustpilot: 'trustpilot.com' };
            const n = String(name || 'anonymous').toLowerCase();
            return n.replace(/[^a-z0-9]/gi, '.').replace(/\.+/g, '.') + '@' + (domains[source] || 'ota.com');
        }

        function normalizeDate(d) {
            if (!d) return new Date().toISOString().replace('T', ' ').substring(0, 19);
            if (typeof d === 'number') d = new Date(d).toISOString();
            if (/^\d{4}-\d{2}$/.test(d)) return d + '-01 00:00:00';
            if (/^\d{4}-\d{2}-\d{2}$/.test(d)) return d + ' 00:00:00';
            try { return new Date(d).toISOString().replace('T', ' ').substring(0, 19); } catch (e) { return d; }
        }

        // ── Viator / TA Fetchers ──────────────────────────────────────────────
        async function fetchViatorReviews(productCode, totalLimit = 100) {
            let allReviews = [];
            let offset = 0;
            const batchSize = 50;
            const baseUrl = `https://www.viator.com/api/product/reviews-v2`;

            try {
                while (allReviews.length < totalLimit) {
                    const url = `${baseUrl}?productCode=${productCode}&offset=${offset}&limit=${batchSize}&sort=NEWEST`;
                    log(`Fetching Viator ${productCode} offset ${offset} via proxy...`, 'info');
                    
                    const formData = new FormData();
                    formData.append('action', 'proxy_ota_fetch');
                    formData.append('url', url);

                    const resp = await fetch(window.location.href, { method: 'POST', body: formData });
                    if (!resp.ok) break;
                    
                    const data = await resp.json();
                    const reviews = data?.reviews ?? [];
                    if (!Array.isArray(reviews) || reviews.length === 0) break;
                    
                    allReviews = allReviews.concat(reviews);
                    offset += reviews.length;
                    if (reviews.length < batchSize) break;
                }
            } catch (e) { log(`Viator Error (${productCode}): ${e.message}`, 'err'); }
            return allReviews.slice(0, totalLimit);
        }

        async function fetchTAReviews(taId, limit = 100) {
            // If taId is just numeric, construct full URL. If it's already a URL, use it.
            let url = taId;
            if (!url.startsWith('http')) {
                url = `https://www.tripadvisor.com/Attraction_Review-g297914-d${taId}-Reviews-Khao_Lak_Land_Discovery.html`;
            }

            log(`Fetching TripAdvisor reviews via proxy scraper...`, 'info');
            try {
                const formData = new FormData();
                formData.append('action', 'proxy_ota_fetch');
                formData.append('url', url);

                const resp = await fetch(window.location.href, { method: 'POST', body: formData });
                if (!resp.ok) return [];
                
                const data = await resp.json();
                const reviews = data?.data?.reviews ?? [];
                log(`✓ [TA] Scraped ${reviews.length} reviews from TripAdvisor`, 'ok');
                return reviews;
            } catch (e) {
                log(`TA Scrape Error: ${e.message}`, 'err');
                return [];
            }
        }

        async function fetchReviews(activityId, totalLimit = 500) {
            const batch = Math.min(totalLimit, 450); 
            const url = `https://travelers-api.getyourguide.com/activities/${activityId}/reviews?limit=${batch}`;
            
            log(`Fetching activity ${activityId} (Limit ${batch}) via single pass...`, 'info');
            try {
                const formData = new FormData();
                formData.append('action', 'proxy_ota_fetch');
                formData.append('url', url);

                const resp = await fetch(window.location.href, { method: 'POST', body: formData });
                if (!resp.ok) return [];
                const data = await resp.json();

                const reviews = data?.reviews ?? data?.data?.reviews ?? data?.data ?? [];
                log(`✓ [GYG] API returned ${reviews.length} reviews for ID ${activityId}`, 'ok');
                return reviews;
            } catch (e) {
                log(`GYG Error: ${e.message}`, 'err');
                return [];
            }
        }

        function buildSQL(allEntries, skipIds, defaultRating) {
            const skipSet = new Set(skipIds.map(s => String(s).trim()).filter(Boolean));
            let sql = 'SET NAMES utf8mb4;\nSET AUTOCOMMIT=0;\nSTART TRANSACTION;\n\n';

            let count = 0, upserted = 0, ratingSum = 0;
            const langSet = new Set();

            for (const item of allEntries) {
                const { postId, source, review: r } = item;
                
                let reviewId = '', metaKey = '';
                let author = '', content = '', rating = defaultRating, dateStr = '', lang = 'en';

                if (source === 'gyg') {
                    reviewId = String(r.reviewId ?? r.id ?? '');
                    metaKey  = 'gyg_review_id';
                    
                    // Author logic (handles object or firstName)
                    const traveler = r.traveler || r.author || {};
                    author = traveler.firstName || traveler.fullName || traveler.name || (typeof traveler === 'string' ? traveler : 'Anonymous');
                    
                    // Content logic (handles nested message object)
                    content = r.message || r.text || r.comment || '';
                    if (typeof content === 'object') content = content.message || content.text || '';
                    
                    rating   = Math.round(parseFloat(r.overallRating ?? r.rating ?? defaultRating));
                    lang     = (r.language ?? r.locale ?? 'en').substring(0, 5);
                    dateStr  = normalizeDate(r.travelDate ?? r.date ?? r.created ?? '');
                } else if (source === 'viator') {
                    reviewId = String(r.reviewReferenceId ?? r.id ?? '');
                    metaKey  = 'viator_review_id';
                    author   = r.userName ?? r.authorName ?? 'Viator Traveler';
                    
                    content = r.reviewText ?? r.text ?? '';
                    if (typeof content === 'object') content = content.text || content.message || '';
                    
                    rating   = r.rating ?? defaultRating;
                    lang     = r.language ?? 'en';
                    dateStr  = normalizeDate(r.publishedDate ?? r.date ?? '');
                }
 else if (source === 'ta') {
                    reviewId = String(r.id ?? '');
                    metaKey  = 'tripadvisor_review_id';
                    author   = r.author ?? 'TA Traveler';
                    content  = r.text ?? '';
                    rating   = r.rating ?? defaultRating;
                } else if (source === 'gmb') {
                    reviewId = String(r.id ?? '');
                    metaKey  = 'gmb_review_id';
                    author   = r.author ?? 'Google User';
                    content  = r.text ?? '';
                    rating   = r.rating ?? defaultRating;
                } else if (source === 'trustpilot') {
                    reviewId = String(r.id ?? '');
                    metaKey  = 'trustpilot_review_id';
                    author   = r.author ?? 'TP Customer';
                    content  = r.text ?? '';
                    rating   = r.rating ?? defaultRating;
                }

                if (skipSet.has(reviewId)) continue;
                langSet.add(lang);
                count++;
                ratingSum += rating;

                sql += `-- ${source}:${reviewId} | ${lang} | ${rating}\u2605 | ${escapeSQL(author)}\n`;
                if (reviewId) {
                    sql += `SET @old_id = (SELECT comment_id FROM \`wp_commentmeta\` WHERE meta_key='${metaKey}' AND meta_value='${reviewId}' LIMIT 1);\n`;
                    sql += `DELETE FROM \`wp_commentmeta\` WHERE comment_id = @old_id AND @old_id IS NOT NULL;\n`;
                    sql += `DELETE FROM \`wp_comments\`   WHERE comment_ID  = @old_id AND @old_id IS NOT NULL;\n`;
                    upserted++;
                }

                const email = makeEmail(author, source);
                
                // Helper for title (truncate content)
                let title = escapeSQL(content || 'Expert Review').split(' ').slice(0, 6).join(' ');
                if (title.length > 50) title = title.substring(0, 50);
                
                // Serialized stats for Traveler
                const rVal = parseInt(rating);
                // a:4:{s:9:"Itinerary";i:5;s:10:"Tour guide";i:5;s:7:"Service";i:5;s:6:"Driver";i:5;}
                const serializedStats = `a:4:{s:9:"Itinerary";i:${rVal};s:10:"Tour guide";i:${rVal};s:7:"Service";i:${rVal};s:6:"Driver";i:${rVal};}`;

                sql += `INSERT INTO \`wp_comments\` (\`comment_post_ID\`,\`comment_author\`,\`comment_author_email\`,\`comment_date\`,\`comment_date_gmt\`,\`comment_content\`,\`comment_approved\`,\`comment_type\`) VALUES (${postId},'${escapeSQL(author)}','${escapeSQL(email)}','${dateStr}','${dateStr}','${escapeSQL(content)}','1','st_reviews');\n`;
                sql += `SET @last_comment_id = LAST_INSERT_ID();\n`;
                sql += `INSERT INTO \`wp_commentmeta\` (\`comment_id\`,\`meta_key\`,\`meta_value\`) VALUES (@last_comment_id,'st_reviews','${rating}');\n`;
                sql += `INSERT INTO \`wp_commentmeta\` (\`comment_id\`,\`meta_key\`,\`meta_value\`) VALUES (@last_comment_id,'${metaKey}','${reviewId}');\n`;
                sql += `INSERT INTO \`wp_commentmeta\` (\`comment_id\`,\`meta_key\`,\`meta_value\`) VALUES (@last_comment_id,'st_star','${rating}');\n`;
                sql += `INSERT INTO \`wp_commentmeta\` (\`comment_id\`,\`meta_key\`,\`meta_value\`) VALUES (@last_comment_id,'ota_source','${source}');\n`;
                sql += `INSERT INTO \`wp_commentmeta\` (\`comment_id\`,\`meta_key\`,\`meta_value\`) VALUES (@last_comment_id,'comment_title','${title}');\n`;
                sql += `INSERT INTO \`wp_commentmeta\` (\`comment_id\`,\`meta_key\`,\`meta_value\`) VALUES (@last_comment_id,'st_review_stats','${serializedStats}');\n`;
                sql += `INSERT INTO \`wp_commentmeta\` (\`comment_id\`,\`meta_key\`,\`meta_value\`) VALUES (@last_comment_id,'st_stat_itinerary','${rating}');\n`;
                sql += `INSERT INTO \`wp_commentmeta\` (\`comment_id\`,\`meta_key\`,\`meta_value\`) VALUES (@last_comment_id,'st_stat_tour-guide','${rating}');\n`;
                sql += `INSERT INTO \`wp_commentmeta\` (\`comment_id\`,\`meta_key\`,\`meta_value\`) VALUES (@last_comment_id,'st_stat_service','${rating}');\n`;
                sql += `INSERT INTO \`wp_commentmeta\` (\`comment_id\`,\`meta_key\`,\`meta_value\`) VALUES (@last_comment_id,'st_stat_driver','${rating}');\n`;
                sql += `INSERT INTO \`wp_commentmeta\` (\`comment_id\`,\`meta_key\`,\`meta_value\`) VALUES (@last_comment_id,'_comment_like_count','0');\n\n`;
            }

            // Recalculate summary meta for Rich Snippets (AggregateRating)
            const uniquePostIds = [...new Set(allEntries.map(e => e.postId))];
            sql += '-- Update summary ratings for Rich Snippets (AggregateRating)\n';
            uniquePostIds.forEach(pid => {
                sql += `UPDATE wp_postmeta SET meta_value = (SELECT COUNT(*) FROM wp_comments WHERE comment_post_ID = ${pid} AND comment_approved='1' AND comment_type='st_reviews') WHERE post_id = ${pid} AND meta_key='total_review';\n`;
                sql += `UPDATE wp_postmeta SET meta_value = (SELECT COALESCE(AVG(CAST(m.meta_value AS DECIMAL(10,1))), 5.0) FROM wp_comments c JOIN wp_commentmeta m ON c.comment_ID = m.comment_id WHERE c.comment_post_ID = ${pid} AND m.meta_key='st_reviews' AND c.comment_approved='1') WHERE post_id = ${pid} AND meta_key='rate_review';\n`;
            });

            sql += 'COMMIT;\nSET AUTOCOMMIT=1;\n';
            return { sql, count, upserted, langs: langSet.size, avgRating: count ? (ratingSum/count).toFixed(1) : 0 };
        }

        async function startFetch() {
            const btn = document.getElementById('fetch-btn');
            const skipIdsRaw = document.getElementById('skip-ids').value;
            const skipIds = skipIdsRaw ? skipIdsRaw.split(',').map(s => s.trim()) : [];
            const maxReviews = parseInt(document.getElementById('max-reviews').value) || 300;
            const defaultRating = parseInt(document.getElementById('default-rating').value) || 5;

            btn.disabled = true;
            document.getElementById('progress').style.display = 'block';
            document.getElementById('results-card').style.display = 'none';
            document.getElementById('log').innerHTML = '';
            document.getElementById('status-badge').textContent = 'Fetching...';
            document.getElementById('status-badge').className = 'badge badge-blue';

            log('Starting multi-source fetch...', 'info');

            const rows = [...document.querySelectorAll('#tour-rows .tour-row')];
            const allEntries = [];

            for (let i = 0; i < rows.length; i++) {
                const row = rows[i];
                const gygId = row.querySelector('.gyg-id').value.trim();
                const viatorId = row.querySelector('.viator-id').value.trim();
                const taId = row.querySelector('.ta-id').value.trim();
                const gmbId = row.querySelector('.gmb-id').value.trim();
                const tpId = row.querySelector('.tp-id').value.trim();
                const wpId = row.querySelector('.wp-id').value.trim();
                const tourName = row.querySelector('.tour-name').value;

                if (!wpId) continue;
                setProgress(((i / rows.length) * 100), `Processing ${tourName || wpId}...`);

                // Treat "0" or empty as null
                const isMapped = (id) => id && id !== '0' && id !== 0;

                // 1. Fetch GYG
                if (isMapped(gygId)) {
                    try {
                        const reviews = await fetchReviews(gygId, maxReviews);
                        reviews.forEach(r => allEntries.push({ postId: wpId, source: 'gyg', review: r }));
                        log(`✓ [GYG] Got ${reviews.length} reviews for ${tourName}`, 'ok');
                    } catch(e) { log(`GYG Error: ${e.message}`, 'err'); }
                }

                // 2. Fetch Viator
                if (isMapped(viatorId)) {
                    try {
                        const reviews = await fetchViatorReviews(viatorId, maxReviews);
                        reviews.forEach(r => allEntries.push({ postId: wpId, source: 'viator', review: r }));
                        log(`✓ [Viator] Got ${reviews.length} reviews for ${tourName}`, 'ok');
                    } catch(e) { log(`Viator Error: ${e.message}`, 'err'); }
                }

                // 3. Fetch Others (Placeholders)
                if (isMapped(taId)) {
                    const reviews = await fetchTAReviews(taId, maxReviews);
                    reviews.forEach(r => allEntries.push({ postId: wpId, source: 'ta', review: r }));
                }
                if (isMapped(gmbId)) {
                    log(`Google My Business (Place ID ${gmbId}): Automatic fetch requires Google Maps API or manual export.`, 'info');
                }
                if (isMapped(tpId)) {
                    log(`Trustpilot (${tpId}): Direct fetch restricted. Manual import recommended.`, 'info');
                }
            }

            setProgress(100, 'Generating SQL...');
            const results = buildSQL(allEntries, skipIds, defaultRating);

            document.getElementById('stat-total').textContent = results.count;
            document.getElementById('stat-activities').textContent = rows.length;
            document.getElementById('stat-langs').textContent = results.langs;
            document.getElementById('stat-upserted').textContent = results.upserted;
            document.getElementById('stat-avg').textContent = results.avgRating + '\u2605';

            generatedSQL = results.sql;
            document.getElementById('sql-output').value = results.sql;
            document.getElementById('results-card').style.display = 'block';
            log(`Done! Generated SQL with ${results.count} entries.`, 'ok');
            document.getElementById('status-badge').textContent = 'Success';
            document.getElementById('status-badge').className = 'badge badge-green';
            btn.disabled = false;
        }

        function downloadSQL() {
            const blob = new Blob([generatedSQL], { type: 'text/sql' });
            const url  = URL.createObjectURL(blob);
            const a    = document.createElement('a');
            a.href     = url;
            a.download = `ota_reviews_import_${new Date().toISOString().slice(0, 10)}.sql`;
            a.click();
        }

        async function copySQL() {
            await navigator.clipboard.writeText(generatedSQL);
            alert('SQL copied to clipboard!');
        }

        function clearAll() {
            generatedSQL = '';
            document.getElementById('sql-output').value = '';
            document.getElementById('results-card').style.display = 'none';
            document.getElementById('log').innerHTML = '';
            document.getElementById('status-badge').textContent = 'Ready';
            document.getElementById('status-badge').className = 'badge badge-blue';
        }
    </script>
</body>
</html>