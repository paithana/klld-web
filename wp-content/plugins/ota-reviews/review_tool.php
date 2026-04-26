<?php
/**
 * OTAs Manager - Sync Manager
 * Consolidates mapping and fetching within the WP environment.
 */

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
}

if ( ! current_user_can( 'manage_options' ) && ! defined('KLLD_TOOL_RUN') ) {
    wp_die( '<h1>Unauthorized</h1><p>You need Administrator privileges to access this tool.</p>' );
}

/**
 * Helper: Find all translations of a tour post
 *
 * @param int $post_id The tour post ID.
 * @return array Array of translated post IDs.
 */
function klld_get_translated_post_ids($post_id) {
    global $wpdb;
    $table = $wpdb->prefix . 'icl_translations';
    
    if ($wpdb->get_var("SHOW TABLES LIKE '$table'") !== $table) {
        return [$post_id];
    }

    $trid = $wpdb->get_var($wpdb->prepare(
        "SELECT trid FROM {$table} WHERE element_id = %d AND element_type = 'post_st_tours' LIMIT 1",
        $post_id
    ));

    if (!$trid) {
        return [$post_id];
    }

    $ids = $wpdb->get_col($wpdb->prepare(
        "SELECT element_id FROM {$table} WHERE trid = %d AND element_type = 'post_st_tours'",
        $trid
    ));

    return !empty($ids) ? $ids : [$post_id];
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
        if (!$wp_id) {
            continue;
        }

        update_post_meta($wp_id, '_gyg_activity_id', sanitize_text_field($map['gygId'] ?? ''));
        update_post_meta($wp_id, '_gyg_url', esc_url_raw($map['gygUrl'] ?? ''));
        update_post_meta($wp_id, '_viator_activity_id', sanitize_text_field($map['viatorId'] ?? ''));
        update_post_meta($wp_id, '_viator_url', esc_url_raw($map['viatorUrl'] ?? ''));
        update_post_meta($wp_id, '_tripadvisor_activity_id', sanitize_text_field($map['taId'] ?? ''));
        update_post_meta($wp_id, '_ta_url', esc_url_raw($map['taUrl'] ?? ''));
        update_post_meta($wp_id, '_ota_keywords', sanitize_text_field($map['keywords'] ?? ''));
        update_post_meta($wp_id, '_gmb_id', sanitize_text_field($map['gmbId'] ?? ''));
        update_post_meta($wp_id, '_trustpilot_id', sanitize_text_field($map['tpId'] ?? ''));
        $count++;
    }

    wp_send_json_success(['message' => "Saved $count mappings successfully."]);
}

// ── AJAX Handler: Proxy Discovery ─────────────────────────────────────────
if (isset($_POST['action']) && $_POST['action'] === 'proxy_ota_discover') {
    $supplier_id = 203466;
    $offset = (int)($_POST['offset'] ?? 0);
    $limit  = (int)($_POST['limit'] ?? 50);
    $token = get_option('_gyg_explorer_token', 'Basic VGhpbmtXZWIubWU6MmE3YzIwOGExOWM0MWE3Mzg4ZDYwZDA5YjQxMzhmZDI=');

    $gyg_urls = [
        "https://travelers-api.getyourguide.com/activities?supplierId={$supplier_id}&limit={$limit}&offset={$offset}",
        "https://www.getyourguide.com/api/catalog/v1/suppliers/{$supplier_id}/activities?limit={$limit}&offset={$offset}"
    ];

    $response_data = null;
    foreach ($gyg_urls as $url) {
        $resp = wp_remote_get($url, [
            'timeout' => 10,
            'headers' => [
                'Accept' => 'application/json',
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                'Authorization' => $token
            ]
        ]);
        if (!is_wp_error($resp) && wp_remote_retrieve_response_code($resp) === 200) {
            $response_data = wp_remote_retrieve_body($resp);
            break;
        }
    }

    if ($response_data) {
        header('Content-Type: application/json');
        echo $response_data;
    } else {
        echo json_encode(['error' => 'Discovery failed. Check API Keys in Settings.']);
    }
    exit;
}

// ── AJAX Handler: Database & Maintenance ──────────────────────────────────
if (isset($_POST['action']) && $_POST['action'] === 'ota_db_maintenance') {
    $job = $_POST['job'] ?? '';
    global $wpdb;

    if ($job === 'deduplicate') {
        $duplicates = $wpdb->get_results("SELECT MIN(comment_ID) as keep_id, comment_post_ID, comment_author, comment_content, comment_date, COUNT(*) as cnt FROM {$wpdb->comments} WHERE comment_type = 'st_reviews' GROUP BY comment_post_ID, comment_author, comment_content, comment_date HAVING cnt > 1");
        $deleted = 0;
        foreach ($duplicates as $dup) {
            $ids_to_del = $wpdb->get_col($wpdb->prepare("SELECT comment_ID FROM {$wpdb->comments} WHERE comment_post_ID = %d AND comment_author = %s AND comment_content = %s AND comment_date = %s AND comment_ID != %d", $dup->comment_post_ID, $dup->comment_author, $dup->comment_content, $dup->comment_date, $dup->keep_id));
            if (!empty($ids_to_del)) {
                $ids_str = implode(',', array_map('intval', $ids_to_del));
                $wpdb->query("DELETE FROM {$wpdb->comments} WHERE comment_ID IN ($ids_str)");
                $wpdb->query("DELETE FROM {$wpdb->commentmeta} WHERE comment_id IN ($ids_str)");
                $deleted += count($ids_to_del);
            }
        }
        wp_send_json_success(['message' => "Cleaned up $deleted duplicate reviews."]);
    } elseif ($job === 'remap_orphans' || $job === 'gmb_filter' || $job === 'remap_all') {
        $all_mapped_tours = get_posts(['post_type' => 'st_tours', 'posts_per_page' => -1, 'post_status' => 'publish']);
        
        if ($job === 'remap_orphans') {
            $reviews = $wpdb->get_results("SELECT * FROM {$wpdb->comments} WHERE comment_post_ID = 0 AND comment_type = 'st_reviews'");
        } elseif ($job === 'gmb_filter') {
            $reviews = $wpdb->get_results("SELECT c.* FROM {$wpdb->comments} c JOIN {$wpdb->commentmeta} cm ON c.comment_ID = cm.comment_id WHERE cm.meta_key = 'ota_source' AND cm.meta_value = 'gmb'");
        } else {
            $reviews = $wpdb->get_results("SELECT * FROM {$wpdb->comments} WHERE comment_type = 'st_reviews'");
        }

        $mapped = 0;
        $cloned = 0;
        $skipped = 0;
        
        foreach ($reviews as $rev) {
            $matched_pids = [];
            $content_lower = strtolower($rev->comment_content);
            
            // Detect Multi-Tour Intent
            $is_multi = false;
            $multi_phrases = ['2 tours', '2 trips', 'two tours', 'two trips', '3 tours', '3 trips', 'both tours', 'combined', 'multiple tours'];
            foreach ($multi_phrases as $phrase) {
                if (strpos($content_lower, $phrase) !== false) {
                    $is_multi = true;
                    break;
                }
            }

            foreach ($all_mapped_tours as $t) {
                $score = klld_calculate_review_match_score($rev->comment_content, $t->ID);
                if ($score >= 150 || ($is_multi && $score >= 70)) {
                    $matched_pids[] = ['id' => $t->ID, 'score' => $score];
                }
            }

            usort($matched_pids, function($a, $b) { return $b['score'] <=> $a['score']; });
            $matched_pids = array_slice($matched_pids, 0, 3);
            $final_pids = array_column($matched_pids, 'id');

            if (!empty($final_pids)) {
                if (in_array((int)$rev->comment_post_ID, $final_pids)) {
                    $primary_pid = (int)$rev->comment_post_ID;
                    $final_pids = array_diff($final_pids, [$primary_pid]);
                } else {
                    $primary_pid = array_shift($final_pids);
                    $wpdb->update($wpdb->comments, ['comment_post_ID' => $primary_pid], ['comment_ID' => $rev->comment_ID]);
                    if (function_exists('st_helper_update_total_review')) st_helper_update_total_review($primary_pid);
                    $mapped++;
                }

                foreach ($final_pids as $extra_pid) {
                    $exists = $wpdb->get_var($wpdb->prepare("SELECT comment_ID FROM $wpdb->comments WHERE comment_post_ID = %d AND comment_author = %s AND comment_date = %s", $extra_pid, $rev->comment_author, $rev->comment_date));
                    if (!$exists) {
                        $new_comment = (array)$rev;
                        unset($new_comment['comment_ID']);
                        $new_comment['comment_post_ID'] = $extra_pid;
                        $new_id = wp_insert_comment($new_comment);
                        if ($new_id) {
                            $meta = $wpdb->get_results($wpdb->prepare("SELECT meta_key, meta_value FROM $wpdb->commentmeta WHERE comment_id = %d", $rev->comment_ID));
                            foreach ($meta as $m) {
                                update_comment_meta($new_id, $m->meta_key, maybe_unserialize($m->meta_value));
                            }
                            if (function_exists('st_helper_update_total_review')) st_helper_update_total_review($extra_pid);
                            $cloned++;
                        }
                    }
                }
            } else {
                $skipped++;
            }
        }
        $type_label = ($job === 'remap_all') ? "Global" : (($job === 'remap_orphans') ? "orphan" : "GMB");
        wp_send_json_success(['message' => "Scanned " . count($reviews) . " $type_label reviews. Re-assigned $mapped, Cloned $cloned. (Skipped $skipped weak matches)."]);
    } elseif ($job === 'refresh_ratings') {
        $mapped_ids = $wpdb->get_col("SELECT DISTINCT post_id FROM {$wpdb->postmeta} WHERE meta_key IN ('_gyg_activity_id', '_viator_activity_id', '_tripadvisor_activity_id')");
        if (empty($mapped_ids)) {
            wp_send_json_success(['message' => "No tours with OTA mappings found."]);
        }
        $all_mapped_tours = get_posts(['post_type' => 'st_tours', 'posts_per_page' => -1, 'post__in' => $mapped_ids, 'post_status' => 'any', 'no_found_rows' => true]);
        $total_updated = 0;
        foreach ($all_mapped_tours as $tour) {
            $localized_ids = klld_get_translated_post_ids($tour->ID);
            foreach ($localized_ids as $tid) {
                if (function_exists('st_helper_update_total_review')) { st_helper_update_total_review($tid); $total_updated++; }
            }
        }
        wp_send_json_success(['message' => "Successfully refreshed aggregate ratings for $total_updated localized tour pages."]);
    } elseif ($job === 'sftp_push') {
        $push_file = dirname(__FILE__) . '/gttd_sftp_push.php';
        if (file_exists($push_file)) {
            define('KLLD_TOOL_RUN', true);
            ob_start(); include($push_file); $results = ob_get_clean();
            wp_send_json_success(['message' => "SFTP Push completed.", 'results' => $results]);
        }
    }
    wp_send_json_error('Unknown maintenance job.');
}

/**
 * AJAX Handler: Manual JSON Import
 * Processes batches of reviews from the clipboard.
 * 
 * @internal Includes strict sanitization to block [object Object] corruption.
 */
if (isset($_POST['action']) && $_POST['action'] === 'ota_direct_import') {
    $batch = json_decode(stripslashes($_POST['batch']), true);
    if (!is_array($batch)) {
        wp_send_json_error('Invalid batch data.');
    }

    global $wpdb;
    $count = 0;
    foreach ($batch as $entry) {
        $post_id   = intval($entry['postId'] ?? 0);
        $review_id = sanitize_text_field($entry['reviewId'] ?? '');
        $meta_key  = sanitize_text_field($entry['metaKey'] ?? '');
        
        $author    = (isset($entry['author']) && is_string($entry['author'])) ? trim($entry['author']) : '';
        $content   = (isset($entry['content']) && is_string($entry['content'])) ? trim($entry['content']) : '';

        // Block malformed JS objects and empty data
        if (empty($author) || strpos($author, '[object Object]') !== false) {
            continue;
        }
        if (empty($content) || strpos($content, '[object Object]') !== false) {
            continue;
        }
        
        if (!$post_id || !$review_id || !$meta_key) {
            continue;
        }

        $localized_ids = klld_get_translated_post_ids($post_id);

        foreach ($localized_ids as $target_post_id) {
            // 1. DEDUPLICATION
            $old_id = $wpdb->get_var($wpdb->prepare("SELECT comment_id FROM {$wpdb->commentmeta} WHERE meta_key = %s AND meta_value = %s LIMIT 1", $meta_key, $review_id));
            if ($old_id) {
                $old_comment = get_comment($old_id);
                if ($old_comment && (int)$old_comment->comment_post_ID === (int)$target_post_id) {
                    wp_delete_comment($old_id, true);
                }
            }

            // 2. INSERT
            $comment_data = [
                'comment_post_ID'      => $target_post_id,
                'comment_author'       => $author,
                'comment_author_email' => sanitize_email($entry['email'] ?? 'traveler@getyourguide.com'),
                'comment_content'      => wp_kses_post($content),
                'comment_type'         => 'st_reviews',
                'comment_parent'       => 0,
                'user_id'              => 0,
                'comment_author_IP'    => '127.0.0.1',
                'comment_agent'        => 'KLLD OTA Importer',
                'comment_date'         => sanitize_text_field($entry['dateStr'] ?? current_time('mysql')),
                'comment_approved'     => 1,
            ];
            $new_id = wp_insert_comment($comment_data);

            if ($new_id) {
                update_comment_meta($new_id, $meta_key, $review_id);
                update_comment_meta($new_id, 'st_category_name', 'st_tours');
                update_comment_meta($new_id, 'comment_rate', intval($entry['rating'] ?? 5));
                update_comment_meta($new_id, 'ota_source', ($meta_key === 'tripadvisor_review_id' ? 'TA' : ($meta_key === 'gyg_review_id' ? 'gyg' : 'gmb')));

                if (function_exists('st_helper_update_total_review')) {
                    st_helper_update_total_review($target_post_id);
                }
                $count++;
            }
        }
    }
    wp_send_json_success(['message' => "Successfully imported $count reviews directly to database."]);
}

if (isset($_POST['action']) && $_POST['action'] === 'run_auto_mapper') {
    $mapper_file = dirname(__FILE__) . '/ota_auto_mapper.php';
    if (file_exists($mapper_file)) {
        ob_start(); include($mapper_file); $results = ob_get_clean();
        wp_send_json_success(['results' => $results]);
    } else {
        wp_send_json_error('Auto-Mapper script not found.');
    }
}

// Fetch All Tours for UI
$all_st_tours = get_posts(['post_type' => 'st_tours', 'posts_per_page' => -1, 'post_status' => ['publish', 'private', 'draft'], 'orderby' => 'title', 'order' => 'ASC']);

if ( ! defined( 'KLLD_TOOL_RUN' ) ) { ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OTAs Sync Manager | KLD</title>
    <script>
        const State = {
            data: { mappings: [], isSyncing: false, currentTab: 'sync', lastLog: 'Ready...', stopSync: false, searchQuery: '' },
            listeners: [],
            subscribe(fn) { this.listeners.push(fn); },
            setState(update) { this.data = { ...this.data, ...update }; this.listeners.forEach(fn => fn(this.data)); this.render(); },
            render() {
                document.querySelectorAll('.tab-content').forEach(c => c.classList.toggle('active', c.id === this.data.currentTab));
                document.querySelectorAll('.tab-btn').forEach(b => {
                    const tid = b.getAttribute('onclick').match(/'([^']+)'/)[1];
                    b.classList.toggle('active', tid === this.data.currentTab);
                });
                const syncBtn = document.getElementById('sync-runner-btn');
                if (syncBtn) { syncBtn.disabled = this.data.isSyncing; syncBtn.innerHTML = this.data.isSyncing ? '⏳ Syncing...' : '🚀 Start Full Sync'; }
                const badge = document.getElementById('status-badge');
                if (badge) { badge.textContent = this.data.isSyncing ? 'Syncing...' : 'Ready'; badge.className = `badge ${this.data.isSyncing ? 'badge-blue pulse' : 'badge-green'}`; }
            }
        };

        function showTab(tabId) { State.setState({ currentTab: tabId }); }

        async function startHistoricalSync() {
            if (!confirm('Start full historical sync for all mapped tours?')) return;
            const logBox = document.getElementById('sync-runner-log');
            const rows = [...document.querySelectorAll('#tour-rows .tour-row')];
            State.setState({ isSyncing: true, stopSync: false });
            logBox.innerHTML = '<b>Initializing Global Sync Sequence...</b><br>';
            for (let i = 0; i < rows.length; i++) {
                if (State.data.stopSync) { logBox.innerHTML += '<br><b style="color:var(--danger);">[ABORTED]</b>'; break; }
                const wpId = rows[i].querySelector('.wp-id').value.trim();
                const tourName = rows[i].querySelector('.tour-name').value;
                if (!wpId) continue;
                logBox.innerHTML += `<div class="text-xs mt-1">[${i+1}/${rows.length}] ${tourName}...</div>`;
                logBox.scrollTop = logBox.scrollHeight;
                try {
                    const url = `<?php echo KLLD_OTA_PLUGIN_URL; ?>ota_sync.php?post_id=${wpId}&limit=5000&secret=kld_sync_2024&format=json&force=1`;
                    const resp = await fetch(url);
                    const data = await resp.json();
                    if (data.success) {
                        const count = (data.log.match(/Sync'd (\d+) reviews/) || [0, 0])[1];
                        logBox.innerHTML += `<div style="color:var(--success); font-size:11px;">&nbsp;&nbsp;✓ ${count} reviews imported.</div>`;
                    }
                } catch (e) { logBox.innerHTML += `<div style="color:var(--danger); font-size:11px;">&nbsp;&nbsp;⚠ Network Failure</div>`; }
            }
            State.setState({ isSyncing: false });
            logBox.innerHTML += '<br><b style="color:var(--success);">[COMPLETED]</b>';
        }

        async function saveMappings() {
            const rows = [...document.querySelectorAll('#tour-rows .tour-row')];
            const mappings = rows.map(r => ({
                wpId: r.querySelector('.wp-id').value,
                gygId: r.querySelector('.gyg-id').value,
                gygUrl: r.querySelector('.gyg-url')?.value || '',
                viatorId: r.querySelector('.viator-id').value,
                viatorUrl: r.querySelector('.viator-url')?.value || '',
                taId: r.querySelector('.ta-id').value,
                taUrl: r.querySelector('.ta-url')?.value || '',
                gmbId: r.querySelector('.gmb-id').value,
                tpId: r.querySelector('.tp-id').value,
                keywords: r.querySelector('.keywords').value
            }));
            const formData = new FormData();
            formData.append('action', 'save_ota_mappings');
            formData.append('mappings', JSON.stringify(mappings));
            const resp = await fetch(window.location.href, { method: 'POST', body: formData });
            const data = await resp.json();
            if (data.success) { alert('✅ Mappings saved.'); location.reload(); }
        }

        async function runMaintenance(job) {
            const formData = new FormData();
            formData.append('action', 'ota_db_maintenance');
            formData.append('job', job);
            const resp = await fetch(window.location.href, { method: 'POST', body: formData });
            const data = await resp.json();
            alert(data.success ? '✅ ' + data.data.message : '❌ ' + data.data);
        }

        function filterTours() {
            const query = document.getElementById('tour-search').value.toLowerCase();
            document.querySelectorAll('#tour-rows .tour-row').forEach(row => {
                row.classList.toggle('hidden', !row.getAttribute('data-search').includes(query));
            });
        }
    </script>
    <style>
        :root { --primary: #0ea5e9; --primary-dark: #0284c7; --secondary: #6366f1; --bg: #f8fafc; --card-bg: #ffffff; --border: #e2e8f0; --text: #1e293b; --text-muted: #64748b; --success: #10b981; --warning: #f59e0b; --danger: #ef4444; }
        .klld-dashboard { font-family: 'Inter', system-ui, sans-serif; max-width: 1400px; margin: 0 auto; padding: 20px; }
        .header-section { background: linear-gradient(135deg, #0ea5e9, #6366f1); color: white; padding: 30px; border-radius: 12px; margin-bottom: 25px; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.1); display: flex; justify-content: space-between; align-items: center; }
        .header-section h1 { color: white; margin: 0; font-size: 28px; font-weight: 700; }
        .tabs { display: flex; gap: 8px; margin-bottom: 25px; background: #fff; padding: 6px; border-radius: 12px; border: 1px solid var(--border); width: fit-content; }
        .tab-btn { padding: 10px 20px; background: transparent; border: none; color: var(--text-muted); cursor: pointer; font-weight: 600; border-radius: 8px; transition: all 0.2s; }
        .tab-btn.active { background: var(--primary); color: white; box-shadow: 0 4px 6px rgba(14, 165, 233, 0.2); }
        .tab-content { display: none; }
        .tab-content.active { display: block; }
        .k-card { background: var(--card-bg); border: 1px solid var(--border); border-radius: 12px; padding: 25px; margin-bottom: 25px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        .mapping-wrapper { overflow-x: auto; border-radius: 12px; border: 1px solid var(--border); background: #fff; }
        .mapping-table { width: 100%; border-collapse: collapse; font-size: 13px; min-width: 1200px; table-layout: fixed; }
        .mapping-table th { text-align: left; padding: 15px; background: #f8fafc; color: var(--text-muted); font-size: 11px; text-transform: uppercase; border-bottom: 1px solid var(--border); }
        .mapping-table td { padding: 12px 15px; border-bottom: 1px solid #f1f5f9; vertical-align: top; }
        .mapping-table tr:hover { background: #f8fafc; }
        .k-input { width: 100%; border: 1px solid #cbd5e1; border-radius: 8px; padding: 8px 12px; font-size: 13px; }
        .k-btn { display: inline-flex; align-items: center; justify-content: center; padding: 10px 18px; border-radius: 8px; font-weight: 600; cursor: pointer; transition: all 0.2s; border: 1px solid transparent; gap: 8px; font-size: 13px; }
        .k-btn-primary { background: linear-gradient(to bottom, #0ea5e9, #0284c7); color: white; border-color: #0369a1; }
        .k-btn-outline { background: white; border-color: #cbd5e1; color: #475569; }
        .log-panel { background: #0f172a; border-radius: 12px; padding: 20px; font-family: monospace; font-size: 12px; color: #38bdf8; height: 300px; overflow-y: auto; margin-top: 1.5rem; }
        .tour-row.hidden { display: none !important; }
        .badge { padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 700; }
        .badge-green { background: #dcfce7; color: #16a34a; }
        .badge-blue { background: #e0f2fe; color: #0ea5e9; }
        .status-indicator { width: 10px; height: 10px; border-radius: 50%; display: inline-block; }
        .status-online { background: var(--success); box-shadow: 0 0 8px var(--success); }
        .status-offline { background: #cbd5e1; }
    </style>
</head>
<body>
    <div class="klld-dashboard">
        <div class="header-section">
            <div style="display:flex; align-items:center; gap: 1rem;">
                <img src="<?php echo KLLD_OTA_PLUGIN_URL; ?>img/ota-reviews-logo.svg" style="width:50px; height:50px; filter: brightness(0) invert(1);" alt="Logo">
                <div>
                    <h1>🎯 Sync Manager Dashboard</h1>
                    <p style="margin:5px 0 0 0; opacity:0.9; font-size:14px;">Multi-source synchronization & mapping control center</p>
                </div>
            </div>
            <div class="badge badge-blue" id="status-badge">Ready</div>
        </div>

        <div class="tabs">
            <button class="tab-btn active" onclick="showTab('sync')">🔄 Sync & Import</button>
            <button class="tab-btn" onclick="showTab('mapping')">🗺 Global Mapping</button>
            <button class="tab-btn" onclick="showTab('feed')">📡 Feed Settings</button>
            <button class="tab-btn" onclick="showTab('maintenance')">🛠 Maintenance</button>
        </div>

        <div id="sync" class="tab-content active">
            <div class="k-card" style="border-left: 4px solid var(--primary);">
                <h2>🚀 Historical Sync Runner</h2>
                <div style="background: #f0f9ff; padding: 20px; border-radius: 12px; border: 1px solid #bae6fd; margin-bottom: 25px; display: flex; align-items: center; justify-content: space-between;">
                    <div style="font-size: 14px; color: #0369a1;"><b>Connection Settings:</b> Manage your API keys and security tokens in the global settings.</div>
                    <a href="?page=klld-ota-settings" class="k-btn k-btn-outline" style="background:white; border-color:#0ea5e9; color:#0ea5e9;">⚙️ Open Settings</a>
                </div>
                <p class="text-sm text-muted mb-4">Trigger a full historical sync for all mapped tours. Sequential processing avoids server timeouts.</p>
                <div class="flex gap-2">
                    <button id="sync-runner-btn" onclick="startHistoricalSync()" class="k-btn k-btn-primary">Start Full Sync</button>
                    <button onclick="State.setState({stopSync: true})" class="k-btn k-btn-outline">Stop</button>
                    <button onclick="document.getElementById('sync-runner-log').innerHTML='Ready...'" class="k-btn k-btn-outline" style="margin-left:auto;">Clear Log</button>
                </div>
                <div id="sync-runner-log" class="log-panel">Ready...</div>
            </div>
        </div>

        <div id="mapping" class="tab-content">
            <div class="k-card">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem;">
                    <div style="display:flex; align-items:center; gap: 15px;">
                        <h2>🗺 Global Tour Mapping</h2>
                        <input type="text" id="tour-search" class="k-input" style="width:300px;" placeholder="Search tours..." oninput="filterTours()">
                    </div>
                    <div class="flex gap-2">
                        <button onclick="runAutoMapper()" class="k-btn k-btn-outline">Auto-Map IDs</button>
                        <button onclick="saveMappings()" class="k-btn k-btn-primary">Save Mappings</button>
                    </div>
                </div>
                <div class="mapping-wrapper">
                    <table class="mapping-table">
                        <thead>
                            <tr>
                                <th style="width:80px;">WP ID</th>
                                <th style="width:250px;">Tour Name</th>
                                <th style="width:150px;">GYG ID</th>
                                <th style="width:150px;">Viator ID</th>
                                <th style="width:150px;">TA ID</th>
                                <th style="width:150px;">GMB ID</th>
                                <th style="width:200px;">Keywords</th>
                                <th style="width:60px;">Status</th>
                            </tr>
                        </thead>
                        <tbody id="tour-rows">
                            <?php foreach ($all_st_tours as $tour) { 
                                $id = $tour->ID;
                                $has_ota = get_post_meta($id, '_gyg_activity_id', true) || get_post_meta($id, '_viator_activity_id', true) || get_post_meta($id, '_tripadvisor_activity_id', true);
                            ?>
                                <tr class="tour-row" data-search="<?php echo esc_attr(strtolower($tour->post_title . ' ' . $id)); ?>">
                                    <td><input type="number" class="k-input wp-id" value="<?php echo $id; ?>" disabled></td>
                                    <td><div style="font-weight:600;"><?php echo esc_html($tour->post_title); ?></div><input type="hidden" class="tour-name" value="<?php echo esc_attr($tour->post_title); ?>"></td>
                                    <td><input type="text" class="k-input gyg-id" value="<?php echo esc_attr(get_post_meta($id, '_gyg_activity_id', true)); ?>"></td>
                                    <td><input type="text" class="k-input viator-id" value="<?php echo esc_attr(get_post_meta($id, '_viator_activity_id', true)); ?>"></td>
                                    <td><input type="text" class="k-input ta-id" value="<?php echo esc_attr(get_post_meta($id, '_tripadvisor_activity_id', true)); ?>"></td>
                                    <td><input type="text" class="k-input gmb-id" value="<?php echo esc_attr(get_post_meta($id, '_gmb_id', true)); ?>"></td>
                                    <td><input type="text" class="k-input keywords" value="<?php echo esc_attr(get_post_meta($id, '_ota_keywords', true)); ?>"></td>
                                    <td style="text-align:center;"><span class="status-indicator <?php echo $has_ota ? 'status-online' : 'status-offline'; ?>"></span></td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div id="feed" class="tab-content">
            <div class="k-card" style="border-left: 4px solid var(--secondary);">
                <h2>📡 Google Things to Do Feed</h2>
                <div style="background: #f5f3ff; padding: 20px; border-radius: 12px; border: 1px solid #ddd6fe; margin-bottom: 25px; display: flex; align-items: center; justify-content: space-between;">
                    <div style="font-size: 14px; color: #5b21b6;"><b>SFTP Settings:</b> Delivery endpoints and private keys have been moved to the centralized settings page.</div>
                    <a href="?page=klld-ota-settings" class="k-btn k-btn-outline" style="background:white; border-color:#8b5cf6; color:#8b5cf6;">⚙️ Configure SFTP</a>
                </div>
                <button onclick="runMaintenance('sftp_push')" class="k-btn k-btn-primary" style="background:var(--success);">🚀 Push Feed Now</button>
            </div>
        </div>

        <div id="maintenance" class="tab-content">
            <div class="k-card" style="border-left: 4px solid var(--danger);">
                <h2>🛠 System Maintenance</h2>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem;">
                    <button onclick="runMaintenance('remap_all')" class="k-btn k-btn-primary" style="background:var(--secondary);">🎯 Global Remap All</button>
                    <button onclick="runMaintenance('deduplicate')" class="k-btn k-btn-outline">🧹 Deduplicate Reviews</button>
                    <button onclick="runMaintenance('remap_orphans')" class="k-btn k-btn-outline">📍 Remap Orphans</button>
                    <button onclick="runMaintenance('refresh_ratings')" class="k-btn k-btn-outline">⭐ Refresh Star Ratings</button>
                    <button onclick="runMaintenance('approve_all')" class="k-btn k-btn-outline" style="color:var(--success);">✅ Approve All</button>
                </div>
                <p class="description" style="margin-top:20px;"><b>Global Remap:</b> Re-evaluates every review against prioritized keywords and enables multi-tour cloning for reviews like "We booked 2 tours".</p>
            </div>
        </div>
    </div>
</body>
</html>
<?php } ?>
