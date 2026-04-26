<?php
/**
 * Review Editor - WordPress Admin Only
 * Allows searching, remapping, and status management of reviews.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! current_user_can( 'manage_options' ) && ! defined('KLLD_TOOL_RUN') ) {
    wp_die( '<h1>Unauthorized</h1><p>You need Administrator privileges to access this tool.</p>' );
}

global $wpdb;

// ── AJAX Handler: Fetch Reviews ─────────────────────────────────────────
if (isset($_POST['action']) && $_POST['action'] === 'klld_get_reviews') {
    $search = sanitize_text_field($_POST['search'] ?? '');
    $paged  = intval($_POST['paged'] ?? 1);
    $per_page = intval($_POST['per_page'] ?? 50);
    $lang_filter = sanitize_text_field($_POST['lang_filter'] ?? 'all');
    $offset = ($paged - 1) * $per_page;

    $join = "";
    $where = "WHERE c.comment_type = 'st_reviews'";
    
    if ($lang_filter === 'default') {
        // Get default language
        $wpml_settings = get_option('icl_sitepress_settings');
        $default_lang = $wpml_settings['default_language'] ?? 'en';
        
        $join .= " JOIN {$wpdb->prefix}icl_translations t ON c.comment_post_ID = t.element_id AND t.element_type = CONCAT('post_', (SELECT post_type FROM {$wpdb->posts} WHERE ID = c.comment_post_ID LIMIT 1))";
        $where .= $wpdb->prepare(" AND t.language_code = %s", $default_lang);
    }

    if (!empty($search)) {
        $where .= $wpdb->prepare(" AND (c.comment_author LIKE %s OR c.comment_content LIKE %s OR c.comment_post_ID IN (SELECT ID FROM {$wpdb->posts} WHERE post_title LIKE %s))", "%$search%", "%$search%", "%$search%");
    }

    $total_items = $wpdb->get_var("SELECT COUNT(DISTINCT c.comment_ID) FROM {$wpdb->comments} c $join $where");
    $reviews = $wpdb->get_results("SELECT c.* FROM {$wpdb->comments} c $join $where GROUP BY c.comment_ID ORDER BY c.comment_date DESC LIMIT $offset, $per_page");

    $rows = [];
    foreach ($reviews as $review) {
        $source = get_comment_meta($review->comment_ID, 'ota_source', true) ?: 'Website';
        $title = get_comment_meta($review->comment_ID, 'comment_title', true) ?: '';
        
        // Get actual language for badge
        $post_lang = $wpdb->get_var($wpdb->prepare("SELECT language_code FROM {$wpdb->prefix}icl_translations WHERE element_id = %d AND element_type LIKE 'post_%%' LIMIT 1", $review->comment_post_ID)) ?: '??';

        $rows[] = [
            'id' => $review->comment_ID,
            'author' => $review->comment_author,
            'date' => get_comment_date('Y-m-d H:i', $review->comment_ID),
            'content' => wp_trim_words($review->comment_content, 20),
            'full_content' => $review->comment_content,
            'comment_title' => $title,
            'post_id' => $review->comment_post_ID,
            'post_title' => get_the_title($review->comment_post_ID),
            'status' => $review->comment_approved,
            'source' => strtoupper($source),
            'lang' => strtoupper($post_lang)
        ];
    }

    wp_send_json_success([
        'reviews' => $rows,
        'total' => $total_items,
        'pages' => ceil($total_items / $per_page)
    ]);
}

// ── AJAX Handler: Update Status ──────────────────────────────────────────
if (isset($_POST['action']) && $_POST['action'] === 'klld_update_review_status') {
    $id = intval($_POST['id']);
    $status = sanitize_text_field($_POST['status']);
    $wpdb->update($wpdb->comments, ['comment_approved' => $status], ['comment_ID' => $id]);
    wp_send_json_success('Status updated.');
}

// ── AJAX Handler: Update Post ID ─────────────────────────────────────────
if (isset($_POST['action']) && $_POST['action'] === 'klld_update_review_post_id') {
    $id = intval($_POST['id']);
    $post_id = intval($_POST['post_id']);
    $post_type = get_post_type($post_id);
    
    if ($post_type) {
        $wpdb->update($wpdb->comments, ['comment_post_ID' => $post_id], ['comment_ID' => $id]);
        update_comment_meta($id, 'st_category_name', $post_type);
        wp_send_json_success('Post ID and Category updated.');
    } else {
        wp_send_json_error('Invalid Post ID.');
    }
}

// ── AJAX Handler: Delete Review ──────────────────────────────────────────
if (isset($_POST['action']) && $_POST['action'] === 'klld_delete_review') {
    $id = intval($_POST['id']);
    wp_delete_comment($id, true);
    wp_send_json_success('Review deleted permanently.');
}

// ── AJAX Handler: Find Best Matches (Bulk) ──────────────────────────────
if (isset($_POST['action']) && $_POST['action'] === 'klld_find_best_matches') {
    $ids = array_map('intval', $_POST['ids'] ?? []);
    if (empty($ids)) {
        $ids_json = $_POST['ids_json'] ?? '';
        if ($ids_json) $ids = json_decode(stripslashes($ids_json), true);
    }
    if (empty($ids)) wp_send_json_error('No IDs provided.');

    set_time_limit(120); // Increase timeout for heavy matching

    // 1. Get all English Tours
    $en_tours = $wpdb->get_results("
        SELECT p.ID, p.post_title 
        FROM {$wpdb->posts} p
        JOIN {$wpdb->prefix}icl_translations t ON p.ID = t.element_id
        WHERE p.post_type = 'st_tours' 
        AND p.post_status = 'publish'
        AND t.language_code = 'en'
        AND t.element_type = 'post_st_tours'
    ");

    $results = [];
    foreach ($ids as $comment_id) {
        $comment = get_comment($comment_id);
        if (!$comment) continue;

        // Try to find tour name in content/meta
        $review_of = get_comment_meta($comment_id, 'ota_review_of', true);
        $content = $comment->comment_content;
        $search_text = strtolower(($review_of ?: '') . " " . $content);

        $matches = [];
        foreach ($en_tours as $tour) {
            $score = 0;
            $title_lower = strtolower($tour->post_title);

            // Fast Exact Match (60 pts)
            if (!empty($review_of) && stripos($title_lower, strtolower($review_of)) !== false) $score += 60;
            
            // Text similarity (Only if title is somewhat present to save CPU)
            if ($score > 0 || stripos($search_text, $title_lower) !== false) {
                similar_text($title_lower, strtolower($review_of ?: $content), $percent);
                $score += $percent;
            }

            if ($score > 10) {
                $matches[] = [
                    'id' => $tour->ID,
                    'title' => $tour->post_title,
                    'score' => round($score)
                ];
            }
        }

        // Sort by score and take top 3
        usort($matches, function($a, $b) { return $b['score'] <=> $a['score']; });
        $results[$comment_id] = array_slice($matches, 0, 3);
    }

    wp_send_json_success($results);
}

// ── AJAX Handler: Bulk Update Post ID (Clone/Copy) ──────────────────────
if (isset($_POST['action']) && $_POST['action'] === 'klld_bulk_update_post') {
    $mappings = $_POST['mappings'] ?? []; // Array of {comment_id: post_id}
    if (is_string($mappings)) {
        $mappings = json_decode(stripslashes($mappings), true);
    }
    
    $mode = $_POST['mode'] ?? 'copy'; // Default to copy as per new requirement
    $count = 0;

    if (!is_array($mappings)) wp_send_json_error('Invalid mappings.');

    foreach ($mappings as $cid => $pid) {
        $cid = intval($cid);
        $pid = intval($pid);
        
        $original = get_comment($cid);
        if (!$original || !get_post_type($pid)) continue;

        if ($mode === 'copy') {
            // Create a new comment (Clone)
            $comment_data = [
                'comment_post_ID'      => $pid,
                'comment_author'       => $original->comment_author,
                'comment_author_email' => $original->comment_author_email,
                'comment_author_url'   => $original->comment_author_url,
                'comment_content'      => $original->comment_content,
                'comment_type'         => $original->comment_type,
                'comment_parent'       => $original->comment_parent,
                'user_id'              => $original->user_id,
                'comment_author_IP'    => $original->comment_author_IP,
                'comment_agent'        => $original->comment_agent,
                'comment_date'         => $original->comment_date,
                'comment_approved'     => $original->comment_approved,
            ];

            $new_cid = wp_insert_comment($comment_data);
            if ($new_cid) {
                // Copy all meta data
                $meta = get_comment_meta($cid);
                foreach ($meta as $key => $values) {
                    foreach ($values as $value) {
                        update_comment_meta($new_cid, $key, maybe_unserialize($value));
                    }
                }
                // Ensure the category meta matches the new post type
                update_comment_meta($new_cid, 'st_category_name', get_post_type($pid));
                $count++;
            }
        } else {
            // Original "Move" logic
            $wpdb->update($wpdb->comments, ['comment_post_ID' => $pid], ['comment_ID' => $cid]);
            update_comment_meta($cid, 'st_category_name', get_post_type($pid));
            $count++;
        }
    }
    $verb = ($mode === 'copy') ? 'Cloned' : 'Updated';
    wp_send_json_success("$verb $count reviews.");
}

// ── Main Page UI ────────────────────────────────────────────────────────
$tours = get_posts(['post_type' => 'st_tours', 'posts_per_page' => -1, 'post_status' => 'publish']);
?>
<div class="wrap" id="klld-review-editor">
    <style>
        #klld-review-editor { margin-top: 20px; font-family: 'Inter', system-ui, sans-serif; color: #1e293b; }
        .k-dash-header { background: linear-gradient(135deg, #0ea5e9, #6366f1); color: white; padding: 30px; border-radius: 12px; margin-bottom: 25px; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.1); display: flex; justify-content: space-between; align-items: center; }
        .k-dash-header h1 { color: white; margin: 0; font-size: 28px; font-weight: 700; letter-spacing: -0.5px; }
        .k-dash-header #editor-stats { background: rgba(255,255,255,0.2); padding: 5px 12px; border-radius: 20px; font-size: 13px; font-weight: 600; color: white; }
        
        .k-search-bar { background: white; padding: 20px; border-radius: 12px; border: 1px solid #e2e8f0; margin-bottom: 25px; display: flex; gap: 15px; align-items: center; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); flex-wrap: wrap; }
        .k-search-bar .k-input { flex: 1; min-width: 250px; max-width: none; }
        .k-search-bar select#per-page-select { flex: 0 0 150px; min-width: 150px; }
        .k-search-bar .k-btn { white-space: nowrap; }

        .k-input { padding: 10px 15px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 14px; width: 100%; transition: all 0.2s; background: white; }
        .k-input:focus { border-color: #0ea5e9; outline: none; box-shadow: 0 0 0 3px rgba(14, 165, 233, 0.15); }
        
        .k-table-card { background: white; border-radius: 12px; border: 1px solid #e2e8f0; overflow: hidden; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.05); }
        .k-table-scroll { overflow-x: auto; width: 100%; }
        .k-table { width: 100%; border-collapse: collapse; text-align: left; font-size: 13px; min-width: 1000px; table-layout: fixed; }
        .k-table th { background: #f8fafc; padding: 15px 20px; font-weight: 600; color: #64748b; text-transform: uppercase; font-size: 11px; border-bottom: 1px solid #e2e8f0; letter-spacing: 0.5px; }
        .k-table td { padding: 15px 20px; border-bottom: 1px solid #f1f5f9; vertical-align: top; word-break: break-word; overflow-wrap: break-word; overflow: hidden; }
        
        /* Select styling for long text */
        .k-input.tour-select { 
            max-width: 100%; 
            text-overflow: ellipsis; 
            white-space: nowrap; 
            overflow: hidden; 
        }
        .k-input.tour-select option { 
            white-space: normal; 
        /* Column Widths */
        .col-cb { width: 45px; }
        .col-reviewer { width: 250px; }
        .col-headline { width: auto; }
        .col-status-badge { width: 120px; }
        .col-manage { width: 130px; }

        .k-table tr.review-row { cursor: pointer; }
        .k-table tr.edit-row { background: #f8fafc; }
        .edit-panel-content { display: grid; grid-template-columns: 1fr 350px; gap: 30px; padding: 25px; border-top: 1px solid #e2e8f0; }
        .edit-section-label { font-weight: 700; color: #64748b; font-size: 11px; text-transform: uppercase; margin-bottom: 10px; display: block; }

        .status-pill-large { padding: 6px 12px; border-radius: 20px; font-weight: 700; font-size: 12px; }

        @media (max-width: 900px) {
            .edit-panel-content { grid-template-columns: 1fr; gap: 20px; }
        }

        @media (max-width: 768px) {
        .badge-status-1 { background: #dcfce7; color: #16a34a; border: 1px solid #bbf7d0; }
        .badge-status-0 { background: #fef9c3; color: #a16207; border: 1px solid #fef08a; }
        .badge-status-trash { background: #fee2e2; color: #dc2626; border: 1px solid #fecaca; }
        
        .k-btn { padding: 9px 16px; border-radius: 8px; border: 1px solid transparent; font-size: 13px; font-weight: 600; cursor: pointer; transition: all 0.2s; display: inline-flex; align-items: center; justify-content: center; gap: 6px; }
        .k-btn-primary { background: linear-gradient(to bottom, #0ea5e9, #0284c7); color: white; border: 1px solid #0369a1; box-shadow: 0 1px 2px rgba(0,0,0,0.1); }
        .k-btn-primary:hover { background: linear-gradient(to bottom, #0284c7, #0369a1); transform: translateY(-1px); }
        .k-btn-danger { background: linear-gradient(to bottom, #ef4444, #dc2626); color: white; border: 1px solid #b91c1c; box-shadow: 0 1px 2px rgba(0,0,0,0.1); }
        .k-btn-danger:hover { background: linear-gradient(to bottom, #dc2626, #b91c1c); transform: translateY(-1px); }
        .k-btn-outline { background: white; border-color: #cbd5e1; color: #475569; }
        .k-btn-outline:hover { background: #f8fafc; border-color: #94a3b8; color: #0f172a; }

        .k-pagination { display: flex; justify-content: center; gap: 6px; flex-wrap: wrap; }
        .k-pagination-wrap { margin-top: 30px; padding-bottom: 50px; display: flex; justify-content: center; width: 100%; }
        .k-page-link { padding: 8px 14px; background: white; border: 1px solid #cbd5e1; border-radius: 8px; cursor: pointer; font-size: 13px; font-weight: 500; color: #475569; transition: all 0.2s; min-width: 40px; text-align: center; }
        .k-page-link:hover { border-color: #0ea5e9; color: #0ea5e9; }
        .k-page-link.active { background: #0ea5e9; color: white; border-color: #0ea5e9; box-shadow: 0 2px 4px rgba(14, 165, 233, 0.2); }
        
        .k-bulk-bar { 
            background: linear-gradient(to right, #0f172a, #1e293b); color: white; padding: 18px 25px; border-radius: 12px; 
            margin-bottom: 25px; display: none; flex-direction: column; gap: 12px;
            position: sticky; top: 32px; z-index: 100; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.2); border: 1px solid #334155;
        }
        .k-bulk-controls { display: flex; justify-content: space-between; align-items: center; width: 100%; }
        .bulk-form-group { display: flex; align-items: center; gap: 12px; }
        .row-suggested { background: #f0f9ff !important; border-left: 4px solid #0ea5e9; }
        .suggestion-box { margin-top: 10px; font-size: 11px; background: #e0f2fe; padding: 10px; border-radius: 8px; border: 1px solid #bae6fd; }
        .suggestion-item { cursor: pointer; color: #0284c7; text-decoration: none; font-weight: 600; margin-right: 12px; padding: 4px 8px; background: white; border-radius: 4px; border: 1px solid #bae6fd; transition: all 0.2s; display: inline-block; margin-bottom: 4px; }
        .suggestion-item:hover { background: #bae6fd; color: #0369a1; }
        .content-full { display: none; background: #f8fafc; padding: 12px; border-radius: 8px; border: 1px solid #e2e8f0; margin-top: 8px; font-size: 13px; white-space: pre-wrap; color: #334155; line-height: 1.5; }
        .toggle-details { font-size: 11px; color: #0ea5e9; cursor: pointer; text-decoration: none; font-weight: 600; margin-left: 5px; }
        .toggle-details:hover { text-decoration: underline; }
        
        /* Modernized Checkbox */
        .k-checkbox { width: 16px; height: 16px; border-radius: 4px; border: 1px solid #cbd5e1; cursor: pointer; }
        .k-checkbox:checked { background-color: #0ea5e9; border-color: #0ea5e9; }

        /* Searchable Dropdown */
        .k-searchable-select { position: relative; width: 100%; }
        .k-search-input { width: 100%; padding: 10px 15px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 14px; background: white; }
        .k-dropdown-list { 
            position: absolute; top: 100%; left: 0; right: 0; background: white; border: 1px solid #cbd5e1; 
            border-radius: 8px; margin-top: 4px; max-height: 250px; overflow-y: auto; z-index: 1000; display: none; 
            box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); 
        }
        .k-dropdown-item { padding: 10px 15px; cursor: pointer; font-size: 13px; border-bottom: 1px solid #f1f5f9; }
        .k-dropdown-item:hover { background: #f0f9ff; color: #0ea5e9; }
        .k-dropdown-item:last-child { border-bottom: none; }
        .k-dropdown-item.selected { background: #0ea5e9; color: white; }

        @media (max-width: 768px) {
// ...
            .k-pagination-wrap { margin-top: 20px; padding-bottom: 30px; }
            .k-pagination { gap: 4px; }
            .k-page-link { padding: 6px 10px; font-size: 12px; min-width: 32px; }
        }
    </style>

    <div class="k-dash-header">
        <h1>✍️ OTAs Editor</h1>
        <div id="editor-stats">Total: Loading...</div>
    </div>

    <div class="k-search-bar">
        <input type="text" id="review-search" class="k-input" placeholder="Search by Author, Content, or Tour Title...">
        <select id="lang-filter-select" class="k-input" style="width: 180px;">
            <option value="all">All Languages</option>
            <option value="default" selected>Default (EN) Only</option>
        </select>
        <select id="per-page-select" class="k-input" style="width: 120px;">
            <option value="20">20 per page</option>
            <option value="50" selected>50 per page</option>
            <option value="100">100 per page</option>
        </select>
        <button onclick="fetchReviews(1)" class="k-btn k-btn-primary">Search</button>
        <button onclick="resetSearch()" class="k-btn k-btn-outline">Reset</button>
    </div>

    <div class="k-bulk-bar" id="bulk-action-bar">
        <div class="k-bulk-controls">
            <div id="bulk-count" style="font-weight: 700; color: #38bdf8;">0 selected</div>
            
            <div class="bulk-form-group">
                <span style="font-size: 11px; opacity: 0.8;">Assign to:</span>
                <div class="k-searchable-select" style="width: 350px;">
                    <input type="text" id="bulk-tour-search" class="k-input k-search-input" placeholder="Search for a tour..." onfocus="showDropdown('bulk-dropdown')" oninput="filterDropdown('bulk-dropdown', this.value)">
                    <input type="hidden" id="bulk-tour-select" value="">
                    <div id="bulk-dropdown" class="k-dropdown-list">
                        <?php 
                        $en_tours = $wpdb->get_results("SELECT p.ID, p.post_title FROM {$wpdb->posts} p JOIN {$wpdb->prefix}icl_translations t ON p.ID = t.element_id WHERE p.post_type = 'st_tours' AND p.post_status = 'publish' AND t.language_code = 'en' AND t.element_type = 'post_st_tours' ORDER BY p.post_title ASC");
                        foreach ($en_tours as $t) { ?>
                            <div class="k-dropdown-item" data-id="<?php echo $t->ID; ?>" onclick="selectTour('bulk', '<?php echo esc_js($t->post_title); ?>', <?php echo $t->ID; ?>)">
                                <?php echo esc_html($t->post_title); ?>
                            </div>
                        <?php } ?>
                    </div>
                </div>
                <button onclick="confirmBulkManualUpdate()" class="k-btn k-btn-primary" style="background: #10b981;">Clone to Tour</button>
            </div>

            <div class="flex gap-2">
                <button onclick="applyBestMatch()" class="k-btn k-btn-outline" style="color: #38bdf8; border-color: #38bdf8;">✨ Auto Match</button>
                <button onclick="confirmBulkUpdates()" id="btn-confirm-bulk" class="k-btn k-btn-primary" style="display:none; background:#10b981;">✅ Confirm All</button>
                <button onclick="resetBulkSelection()" class="k-btn k-btn-outline" style="color: white; border-color: #334155;">Reset</button>
            </div>
        </div>
        <div id="bulk-results" style="font-size: 11px; color: #94a3b8; border-top: 1px solid #334155; padding-top: 10px; display: none;"></div>
    </div>

<div class="k-table-card">
    <div class="k-table-scroll">
        <table class="k-table">
            <thead>
                <tr>
                    <th class="col-cb"><input type="checkbox" class="k-checkbox" onclick="toggleAllReviews(this.checked)"></th>
                    <th class="col-reviewer">Reviewer</th>
                    <th class="col-headline">Headline / Summary</th>
                    <th class="col-status-badge">Status</th>
                    <th class="col-manage">Actions</th>
                </tr>
            </thead>
            <tbody id="reviews-body">
                <!-- Loaded via JS -->
            </tbody>
        </table>
    </div>
</div>

<div class="k-pagination-wrap">
    <div id="reviews-pagination" class="k-pagination"></div>
</div>
</div>

<script>
let currentPage = 1;

async function fetchReviews(page = 1) {
    currentPage = page;
    const search = document.getElementById('review-search').value;
    const perPage = document.getElementById('per-page-select').value;
    const langFilter = document.getElementById('lang-filter-select').value;
    const body = document.getElementById('reviews-body');
    const pagination = document.getElementById('reviews-pagination');
    const stats = document.getElementById('editor-stats');

    body.innerHTML = '<tr><td colspan="8" style="text-align:center; padding:50px;">Loading reviews...</td></tr>';

    const formData = new FormData();
    formData.append('action', 'klld_get_reviews');
    formData.append('search', search);
    formData.append('paged', page);
    formData.append('per_page', perPage);
    formData.append('lang_filter', langFilter);

    try {
        const resp = await fetch(window.location.href, { method: 'POST', body: formData });
        const res = await resp.json();
        
        if (res.success) {
            stats.innerText = `Total: ${res.data.total} reviews`;
            renderTable(res.data.reviews);
            renderPagination(res.data.pages, page);
        }
    } catch (e) {
        body.innerHTML = '<tr><td colspan="8" style="color:red; text-align:center;">Failed to load reviews.</td></tr>';
    }
}

function renderTable(reviews) {
    const body = document.getElementById('reviews-body');
    if (reviews.length === 0) {
        body.innerHTML = '<tr><td colspan="5" style="text-align:center; padding:50px;">No reviews found matching your search.</td></tr>';
        return;
    }

    body.innerHTML = reviews.map(r => {
        const statusLabel = r.status == '1' ? 'Approved' : (r.status == '0' ? 'Pending' : 'Trash');
        const statusClass = `badge-status-${r.status}`;
        
        return `
            <tr id="review-row-${r.id}" class="review-row" onclick="toggleEdit(${r.id}, event)">
                <td data-label="Select" onclick="event.stopPropagation()"><input type="checkbox" class="k-checkbox review-cb" value="${r.id}" onchange="updateBulkUI()"></td>
                
                <td data-label="Reviewer">
                    <div style="font-weight:700; color:#0f172a; font-size:14px;">${r.author}</div>
                    <div style="display:flex; align-items:center; gap:8px; margin-top:4px;">
                        <span class="badge ota-badge badge-gyg" style="font-size:9px;">${r.source}</span>
                        <span style="color:#94a3b8; font-weight:600; font-size:10px;">#${r.id}</span>
                        <span class="badge" style="background:#f1f5f9; color:#475569; font-size:9px; border:1px solid #e2e8f0;">${r.lang}</span>
                    </div>
                </td>

                <td data-label="Headline">
                    <div style="font-weight:800; color:#8b5cf6; font-size:13px; line-height:1.4;">
                        <span id="title-${r.id}">${r.comment_title || '<span style="color:#94a3b8; font-weight:400; font-style:italic;">No Headline</span>'}</span>
                        ${!r.comment_title ? `<a href="?page=klld-ai-writer" title="Generate with AI" style="text-decoration:none; margin-left:5px;">🪄</a>` : ''}
                    </div>
                    <div style="font-size:12px; color:#64748b; margin-top:4px; opacity:0.8;">${r.content}</div>
                </td>

                <td data-label="Status">
                    <span class="badge ${statusClass}">${statusLabel}</span>
                </td>

                <td data-label="Actions" style="text-align:right;">
                    <button class="k-btn k-btn-outline" style="padding:6px 12px; font-size:11px;">Manage ▾</button>
                </td>
            </tr>
            <tr id="edit-row-${r.id}" class="edit-row" style="display:none;">
                <td colspan="5" style="padding:0; border-bottom: 2px solid #e2e8f0;">
                    <div class="edit-panel-content">
                        <div class="edit-section">
                            <span class="edit-section-label">Full Customer Review</span>
                            <div id="full-content-text-${r.id}" style="background:white; padding:15px; border-radius:12px; border:1px solid #e2e8f0; font-size:14px; line-height:1.6; color:#1e293b; white-space:pre-wrap;">${r.full_content || r.content}</div>
                            
                            <div style="margin-top:20px;">
                                <span class="edit-section-label">AI Assisted Moderation</span>
                                <div style="display:flex; gap:8px; flex-wrap:wrap;">
                                    <button onclick="useAhrefs(${r.id}, 'detector')" class="k-btn k-btn-outline" style="flex:1; border-color:#10b981; color:#059669; font-size:10px; min-width:80px;" title="Check for AI content">🛡 AI Check</button>
                                    <button onclick="useAhrefs(${r.id}, 'grammar')" class="k-btn k-btn-outline" style="flex:1; border-color:#818cf8; color:#4f46e5; font-size:10px; min-width:80px;" title="Fix typos and grammar">✨ Fix Text</button>
                                    <button onclick="useAhrefs(${r.id}, 'reply')" class="k-btn k-btn-outline" style="flex:1; border-color:#0ea5e9; color:#0284c7; font-size:10px; min-width:80px;" title="Generate a response">💬 Reply</button>
                                </div>
                                <div style="margin-top:15px; color:#94a3b8; font-size:11px;">Submitted on: ${r.date}</div>
                            </div>
                        </div>

                        <div class="edit-section">
                            <div style="margin-bottom:20px;">
                                <span class="edit-section-label">Tour Mapping (WPML: ${r.lang})</span>
                                <div class="k-searchable-select">
                                    <input type="text" id="tour-search-${r.id}" class="k-input k-search-input" value="${r.post_title}" onfocus="showDropdown('dropdown-${r.id}')" oninput="filterDropdown('dropdown-${r.id}', this.value)">
                                    <input type="hidden" id="tour-select-${r.id}" value="${r.post_id}">
                                    <div id="dropdown-${r.id}" class="k-dropdown-list">
                                        <?php 
                                        foreach ($en_tours as $t) { ?>
                                            <div class="k-dropdown-item" data-id="<?php echo $t->ID; ?>" onclick="selectTour(${r.id}, '<?php echo esc_js($t->post_title); ?>', <?php echo $t->ID; ?>)">
                                                <?php echo esc_html($t->post_title); ?>
                                            </div>
                                        <?php } ?>
                                    </div>
                                </div>
                                <div style="font-size:11px; color:#94a3b8; margin-top:8px;">Current Post ID Reference: <b>${r.post_id}</b></div>
                            </div>

                            <div style="margin-bottom:25px;">
                                <span class="edit-section-label">Moderation Status</span>
                                <select onchange="updateStatus(${r.id}, this.value)" class="k-input status-select" style="width:100%; height:45px; background:#fff;">
                                    <option value="1" ${r.status == '1' ? 'selected' : ''}>Approved & Published</option>
                                    <option value="0" ${r.status == '0' ? 'selected' : ''}>Hold (Pending)</option>
                                    <option value="trash" ${r.status == 'trash' ? 'selected' : ''}>Move to Trash</option>
                                </select>
                            </div>

                            <div style="display:flex; gap:10px; border-top:1px solid #e2e8f0; padding-top:20px;">
                                <button onclick="deleteReview(${r.id})" class="k-btn k-btn-danger" style="flex:1;">Delete Permanently</button>
                                <button onclick="toggleEdit(${r.id})" class="k-btn k-btn-outline" style="flex:1;">Close Panel</button>
                            </div>
                        </div>
                    </div>
                </td>
            </tr>
        `;
    }).join('');
}

function toggleEdit(id, event) {
    if (event && (event.target.tagName === 'INPUT' || event.target.tagName === 'SELECT' || event.target.tagName === 'BUTTON' || event.target.tagName === 'A')) return;
    
    const row = document.getElementById(`edit-row-${id}`);
    const mainRow = document.getElementById(`review-row-${id}`);
    const isHidden = row.style.display === 'none';
    
    // Close all other open panels first for a true accordion feel
    document.querySelectorAll('.edit-row').forEach(r => r.style.display = 'none');
    document.querySelectorAll('.review-row').forEach(r => r.style.backgroundColor = '');

    if (isHidden) {
        row.style.display = 'table-row';
        mainRow.style.backgroundColor = '#f1f5f9';
    }
}

function toggleReviewDetails(id, show) {
    // Legacy function - kept for compatibility if needed elsewhere
    toggleEdit(id);
}

function toggleAllReviews(checked) {
    document.querySelectorAll('.review-cb').forEach(cb => cb.checked = checked);
    updateBulkUI();
}

function updateBulkUI() {
    const selected = document.querySelectorAll('.review-cb:checked');
    const bar = document.getElementById('bulk-action-bar');
    const count = document.getElementById('bulk-count');
    const results = document.getElementById('bulk-results');
    
    if (selected.length > 0) {
        bar.style.display = 'flex';
        count.innerText = `${selected.length} reviews selected`;
    } else {
        bar.style.display = 'none';
        results.style.display = 'none';
        results.innerHTML = '';
    }
}

async function confirmBulkManualUpdate() {
    const selectedIds = Array.from(document.querySelectorAll('.review-cb:checked')).map(cb => cb.value);
    const tourId = document.getElementById('bulk-tour-select').value;
    const results = document.getElementById('bulk-results');

    if (selectedIds.length === 0) return;
    if (!tourId) {
        alert('Please select a tour to assign these reviews to.');
        return;
    }

    results.style.display = 'block';
    results.innerHTML = `Processing ${selectedIds.length} reviews...`;
    
    const mappings = {};
    selectedIds.forEach(id => mappings[id] = tourId);

    const formData = new FormData();
    formData.append('action', 'klld_bulk_update_post');
    formData.append('mappings', JSON.stringify(mappings));

    try {
        const resp = await fetch(window.location.href, { method: 'POST', body: formData });
        const res = await resp.json();
        if (res.success) {
            results.innerHTML = `<span style="color:#10b981;">✅ Success: ${res.data}</span>`;
            setTimeout(() => {
                resetBulkSelection();
                fetchReviews(currentPage);
            }, 1000);
        } else {
            results.innerHTML = `<span style="color:#ef4444;">❌ Error: ${res.data}</span>`;
        }
    } catch (e) {
        results.innerHTML = `<span style="color:#ef4444;">❌ Network Error</span>`;
    }
}

let pendingMappings = {};

async function applyBestMatch() {
    const selectedIds = Array.from(document.querySelectorAll('.review-cb:checked')).map(cb => cb.value);
    if (selectedIds.length === 0) return;

    const bar = document.getElementById('bulk-action-bar');
    const btnConfirm = document.getElementById('btn-confirm-bulk');
    
    bar.style.opacity = '0.5';
    
    const formData = new FormData();
    formData.append('action', 'klld_find_best_matches');
    formData.append('ids_json', JSON.stringify(selectedIds));

    try {
        const resp = await fetch(window.location.href, { method: 'POST', body: formData });
        const res = await resp.json();
        
        if (res.success) {
            Object.entries(res.data).forEach(([id, matches]) => {
                const row = document.getElementById(`review-row-${id}`);
                const sugBox = document.getElementById(`suggestions-${id}`);
                const select = document.getElementById(`tour-select-${id}`);
                const search = document.getElementById(`tour-search-${id}`);
                
                row.classList.add('row-suggested');
                
                // Set the best match in the select box
                if (matches.length > 0) {
                    select.value = matches[0].id;
                    search.value = matches[0].title;
                    pendingMappings[id] = matches[0].id;
                }

                sugBox.innerHTML = `
                    <div class="suggestion-box">
                        <b>Top Matches:</b><br>
                        ${matches.map(m => `
                            <span class="suggestion-item" onclick="pickSuggestion(${id}, ${m.id}, '${m.title.replace(/'/g, "\\'")}')">
                                ${m.title} (${m.score}%)
                            </span>
                        `).join('')}
                    </div>
                `;
            });
            btnConfirm.style.display = 'inline-block';
        }
    } catch (e) { console.error(e); }
    
    bar.style.opacity = '1';
}

function pickSuggestion(cid, pid, title) {
    const select = document.getElementById(`tour-select-${cid}`);
    const search = document.getElementById(`tour-search-${cid}`);
    select.value = pid;
    search.value = title;
    pendingMappings[cid] = pid;
}

async function confirmBulkUpdates() {
    if (Object.keys(pendingMappings).length === 0) return;
    
    const formData = new FormData();
    formData.append('action', 'klld_bulk_update_post');
    formData.append('mappings', JSON.stringify(pendingMappings));

    const resp = await fetch(window.location.href, { method: 'POST', body: formData });
    const res = await resp.json();
    if (res.success) {
        resetBulkSelection();
        fetchReviews(currentPage);
    }
}

function resetBulkSelection() {
    pendingMappings = {};
    document.querySelectorAll('.review-row').forEach(row => row.classList.remove('row-suggested'));
    document.querySelectorAll('[id^="suggestions-"]').forEach(box => box.innerHTML = '');
    document.querySelectorAll('.review-cb').forEach(cb => cb.checked = false);
    document.getElementById('btn-confirm-bulk').style.display = 'none';
    updateBulkUI();
}

function renderPagination(totalPages, active) {
    const container = document.getElementById('reviews-pagination');
    if (totalPages <= 1) { container.innerHTML = ''; return; }

    let html = '';
    const range = 5; // Number of pages to show on each side of active
    
    // Always show First Page
    if (active > range + 2) {
        html += `<button onclick="fetchReviews(1)" class="k-page-link">1</button>`;
        html += `<span style="padding: 5px 10px; color: #64748b;">...</span>`;
    }

    for (let i = 1; i <= totalPages; i++) {
        if (i >= active - range && i <= active + range) {
            html += `<button onclick="fetchReviews(${i})" class="k-page-link ${i === active ? 'active' : ''}">${i}</button>`;
        }
    }

    // Always show Last Page
    if (active < totalPages - range - 1) {
        html += `<span style="padding: 5px 10px; color: #64748b;">...</span>`;
        html += `<button onclick="fetchReviews(${totalPages})" class="k-page-link">${totalPages}</button>`;
    }

    container.innerHTML = html;
}

async function updateStatus(id, status) {
    const formData = new FormData();
    formData.append('action', 'klld_update_review_status');
    formData.append('id', id);
    formData.append('status', status);
    const resp = await fetch(window.location.href, { method: 'POST', body: formData });
}

async function updatePostId(id, postId) {
    const formData = new FormData();
    formData.append('action', 'klld_update_review_post_id');
    formData.append('id', id);
    formData.append('post_id', postId);
    const resp = await fetch(window.location.href, { method: 'POST', body: formData });
    const res = await resp.json();
    if (!res.success) alert(res.data);
    else fetchReviews(currentPage);
}

async function deleteReview(id) {
    if (!confirm('Are you sure you want to delete this review permanently?')) return;
    const formData = new FormData();
    formData.append('action', 'klld_delete_review');
    formData.append('id', id);
    await fetch(window.location.href, { method: 'POST', body: formData });
    fetchReviews(currentPage);
}

function resetSearch() {
    document.getElementById('review-search').value = '';
    fetchReviews(1);
}

function useAhrefs(id, tool) {
    const content = document.getElementById('full-content-text-' + id).innerText;
    const encoded = encodeURIComponent(content);
    
    let url = 'https://ahrefs.com/writing-tools';
    switch(tool) {
        case 'detector': url = `https://ahrefs.com/writing-tools/ai-content-detector?input=${encoded}`; break;
        case 'grammar':  url = `https://ahrefs.com/writing-tools/grammar-checker?input=${encoded}`; break;
        case 'reply':    url = `https://ahrefs.com/writing-tools/review-response-generator?input=${encoded}`; break;
    }
    
    window.open(url, '_blank');
}

// ── Searchable Dropdown Logic ───────────────────────────────────────────
function showDropdown(id) {
    // Close other dropdowns first
    document.querySelectorAll('.k-dropdown-list').forEach(el => {
        if (el.id !== id) el.style.display = 'none';
    });
    document.getElementById(id).style.display = 'block';
}

function filterDropdown(id, query) {
    const list = document.getElementById(id);
    const items = list.getElementsByClassName('k-dropdown-item');
    const q = query.toLowerCase();
    
    let visibleCount = 0;
    for (let item of items) {
        if (item.innerText.toLowerCase().includes(q)) {
            item.style.display = 'block';
            visibleCount++;
        } else {
            item.style.display = 'none';
        }
    }
    list.style.display = visibleCount > 0 ? 'block' : 'none';
}

function selectTour(context, title, id) {
    if (context === 'bulk') {
        document.getElementById('bulk-tour-search').value = title;
        document.getElementById('bulk-tour-select').value = id;
        document.getElementById('bulk-dropdown').style.display = 'none';
    } else {
        document.getElementById(`tour-search-${context}`).value = title;
        document.getElementById(`tour-select-${context}`).value = id;
        document.getElementById(`dropdown-${context}`).style.display = 'none';
        updatePostId(context, id);
    }
}

// Close dropdowns on outside click
document.addEventListener('click', function(e) {
    if (!e.target.closest('.k-searchable-select')) {
        document.querySelectorAll('.k-dropdown-list').forEach(el => el.style.display = 'none');
    }
});

// Initial Load
fetchReviews(1);
</script>
<?php
if ( ! defined( "KLLD_TOOL_RUN" ) ) { ?>
</body>
</html><?php } ?>