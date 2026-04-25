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
    $per_page = 20;
    $offset = ($paged - 1) * $per_page;

    $where = "WHERE comment_type = 'st_reviews'";
    if (!empty($search)) {
        $where .= $wpdb->prepare(" AND (comment_author LIKE %s OR comment_content LIKE %s OR comment_post_ID IN (SELECT ID FROM {$wpdb->posts} WHERE post_title LIKE %s))", "%$search%", "%$search%", "%$search%");
    }

    $total_items = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->comments} $where");
    $reviews = $wpdb->get_results("SELECT * FROM {$wpdb->comments} $where ORDER BY comment_date DESC LIMIT $offset, $per_page");

    $rows = [];
    foreach ($reviews as $review) {
        $source = get_comment_meta($review->comment_ID, 'ota_source', true) ?: 'Website';
        $rows[] = [
            'id' => $review->comment_ID,
            'author' => $review->comment_author,
            'date' => get_comment_date('Y-m-d H:i', $review->comment_ID),
            'content' => wp_trim_words($review->comment_content, 20),
            'full_content' => $review->comment_content,
            'post_id' => $review->comment_post_ID,
            'post_title' => get_the_title($review->comment_post_ID),
            'status' => $review->comment_approved,
            'source' => strtoupper($source)
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
    if (empty($ids)) wp_send_json_error('No IDs provided.');

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
        $search_text = $review_of . " " . $content;

        $matches = [];
        foreach ($en_tours as $tour) {
            $score = 0;
            // Use ota_sync matching logic (simplified here)
            if (!empty($review_of) && stripos($tour->post_title, $review_of) !== false) $score += 50;
            
            // Text similarity
            similar_text(strtolower($tour->post_title), strtolower($review_of ?: $content), $percent);
            $score += $percent;

            $matches[] = [
                'id' => $tour->ID,
                'title' => $tour->post_title,
                'score' => $score
            ];
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
    $mode = $_POST['mode'] ?? 'copy'; // Default to copy as per new requirement
    $count = 0;

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
        .k-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; }
        .k-header h1 { font-size: 24px; font-weight: 800; margin: 0; color: #0f172a; }
        
        .k-search-bar { background: white; padding: 20px; border-radius: 12px; border: 1px solid #e2e8f0; margin-bottom: 25px; display: flex; gap: 15px; align-items: center; }
        .k-input { padding: 10px 15px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 14px; width: 100%; max-width: 400px; }
        .k-input:focus { border-color: #0ea5e9; outline: none; box-shadow: 0 0 0 3px rgba(14, 165, 233, 0.1); }
        
        .k-table-card { background: white; border-radius: 12px; border: 1px solid #e2e8f0; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); }
        .k-table { width: 100%; border-collapse: collapse; text-align: left; font-size: 13px; }
        .k-table th { background: #f8fafc; padding: 12px 20px; font-weight: 600; color: #64748b; text-transform: uppercase; font-size: 11px; border-bottom: 1px solid #e2e8f0; }
        .k-table td { padding: 15px 20px; border-bottom: 1px solid #f1f5f9; }
        .k-table tr:hover { background: #f8fafc; }
        
        .badge { padding: 4px 8px; border-radius: 6px; font-weight: 700; font-size: 10px; text-transform: uppercase; }
        .badge-status-1 { background: #dcfce7; color: #16a34a; }
        .badge-status-0 { background: #fef9c3; color: #a16207; }
        .badge-status-trash { background: #fee2e2; color: #dc2626; }
        
        .k-btn { padding: 8px 12px; border-radius: 6px; border: 1px solid transparent; font-size: 12px; font-weight: 600; cursor: pointer; transition: all 0.2s; }
        .k-btn-primary { background: #0ea5e9; color: white; }
        .k-btn-primary:hover { background: #0284c7; }
        .k-btn-danger { background: #ef4444; color: white; }
        .k-btn-danger:hover { background: #dc2626; }
        .k-btn-outline { background: white; border-color: #e2e8f0; color: #64748b; }
        .k-btn-outline:hover { background: #f8fafc; border-color: #cbd5e1; }

        .k-pagination { display: flex; justify-content: center; gap: 5px; margin-top: 25px; padding-bottom: 50px; }
        .k-page-link { padding: 8px 12px; background: white; border: 1px solid #e2e8f0; border-radius: 6px; cursor: pointer; font-size: 13px; }
        .k-page-link.active { background: #0ea5e9; color: white; border-color: #0ea5e9; }
        
        .k-bulk-bar { 
            background: #0f172a; color: white; padding: 15px 25px; border-radius: 12px; 
            margin-bottom: 20px; display: none; flex-direction: column; gap: 10px;
            position: sticky; top: 32px; z-index: 100; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1);
        }
        .k-bulk-controls { display: flex; justify-content: space-between; align-items: center; width: 100%; }
        .bulk-form-group { display: flex; align-items: center; gap: 10px; }
        .row-suggested { background: #f0f9ff !important; border-left: 4px solid #0ea5e9; }
        .suggestion-box { margin-top: 8px; font-size: 11px; background: #e0f2fe; padding: 8px; border-radius: 6px; }
        .suggestion-item { cursor: pointer; color: #0284c7; text-decoration: underline; margin-right: 10px; }
        .suggestion-item:hover { color: #0369a1; }
        .content-full { display: none; background: #f1f5f9; padding: 10px; border-radius: 6px; margin-top: 5px; font-size: 12px; white-space: pre-wrap; }
        .toggle-details { font-size: 10px; color: #0ea5e9; cursor: pointer; text-decoration: underline; }
    </style>

    <div class="k-header">
        <h1>Review Editor</h1>
        <div id="editor-stats" style="font-size: 13px; color: #64748b;"></div>
    </div>
<div class="k-search-bar">
    <input type="text" id="review-search" class="k-input" placeholder="Search by Author, Content, or Tour Title...">
    <button onclick="fetchReviews(1)" class="k-btn k-btn-primary">Search</button>
    <button onclick="resetSearch()" class="k-btn k-btn-outline">Reset</button>
</div>

<div class="k-bulk-bar" id="bulk-action-bar">
    <div class="k-bulk-controls">
        <div id="bulk-count" style="font-weight: 700; color: #38bdf8;">0 selected</div>
        
        <div class="bulk-form-group">
            <span style="font-size: 11px; opacity: 0.8;">Assign to:</span>
            <select id="bulk-tour-select" class="k-input tour-select" style="width: 250px; background: #1e293b; color: white; border-color: #334155;">
                <option value="">-- Choose Tour (WPML: EN) --</option>
                <?php 
                $en_tours = $wpdb->get_results("SELECT p.ID, p.post_title FROM {$wpdb->posts} p JOIN {$wpdb->prefix}icl_translations t ON p.ID = t.element_id WHERE p.post_type = 'st_tours' AND p.post_status = 'publish' AND t.language_code = 'en' AND t.element_type = 'post_st_tours' ORDER BY p.post_title ASC");
                foreach ($en_tours as $t) { ?>
                    <option value="<?php echo $t->ID; ?>"><?php echo esc_html($t->post_title); ?></option>
                <?php } ?>
            </select>
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
    <table class="k-table">
        <thead>
            <tr>
                <th style="width:40px;"><input type="checkbox" onclick="toggleAllReviews(this.checked)"></th>
                <th>ID</th>
                <th>Source</th>
...
                    <th>Reviewer & Date</th>
                    <th>Comment Content</th>
                    <th>Assigned Tour</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="reviews-body">
                <!-- Loaded via JS -->
            </tbody>
        </table>
    </div>

    <div id="reviews-pagination" class="k-pagination"></div>
</div>

<script>
let currentPage = 1;

async function fetchReviews(page = 1) {
    currentPage = page;
    const search = document.getElementById('review-search').value;
    const body = document.getElementById('reviews-body');
    const pagination = document.getElementById('reviews-pagination');
    const stats = document.getElementById('editor-stats');

    body.innerHTML = '<tr><td colspan="8" style="text-align:center; padding:50px;">Loading reviews...</td></tr>';

    const formData = new FormData();
    formData.append('action', 'klld_get_reviews');
    formData.append('search', search);
    formData.append('paged', page);

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
        body.innerHTML = '<tr><td colspan="8" style="text-align:center; padding:50px;">No reviews found matching your search.</td></tr>';
        return;
    }

    body.innerHTML = reviews.map(r => `
        <tr id="review-row-${r.id}" class="review-row">
            <td><input type="checkbox" class="review-cb" value="${r.id}" onchange="updateBulkUI()"></td>
            <td>${r.id}</td>
            <td><span class="badge ota-badge badge-gyg">${r.source}</span></td>
            <td>
                <div style="font-weight:700;">${r.author}</div>
                <div style="font-size:11px; color:#64748b;">${r.date}</div>
            </td>
            <td style="max-width:400px;">
                <div id="content-trim-${r.id}">${r.content} <span class="toggle-details" onclick="toggleReviewDetails(${r.id}, true)">[full-details]</span></div>
                <div id="content-full-${r.id}" class="content-full">
                    <div style="margin-bottom:10px;">${r.full_content || r.content}</div>
                    <span class="toggle-details" onclick="toggleReviewDetails(${r.id}, false)">[show-less]</span>
                </div>
                <div id="suggestions-${r.id}"></div>
            </td>
            <td>
                <select id="tour-select-${r.id}" onchange="updatePostId(${r.id}, this.value)" class="k-input tour-select">
                    <?php foreach ($tours as $t) { ?>
                        <option value="<?php echo $t->ID; ?>" ${r.post_id == <?php echo $t->ID; ?> ? 'selected' : ''}>
                            <?php echo esc_js($t->post_title); ?>
                        </option>
                    <?php } ?>
                </select>
                <div style="font-size:10px; color:#94a3b8; margin-top:4px;">ID: ${r.post_id}</div>
            </td>
            <td>
                <select onchange="updateStatus(${r.id}, this.value)" class="k-input status-select">
                    <option value="1" ${r.status == '1' ? 'selected' : ''}>Approved</option>
                    <option value="0" ${r.status == '0' ? 'selected' : ''}>Pending</option>
                    <option value="trash" ${r.status == 'trash' ? 'selected' : ''}>Trash</option>
                </select>
            </td>
            <td>
                <button onclick="deleteReview(${r.id})" class="k-btn k-btn-danger">Delete</button>
            </td>
        </tr>
    `).join('');
}

function toggleReviewDetails(id, show) {
    document.getElementById(`content-trim-${id}`).style.display = show ? 'none' : 'block';
    document.getElementById(`content-full-${id}`).style.display = show ? 'block' : 'none';
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
    
    const formData = new FormData();
    formData.append('action', 'klld_bulk_update_post');
    selectedIds.forEach(id => {
        formData.append(`mappings[${id}]`, tourId);
    });

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
    selectedIds.forEach(id => formData.append('ids[]', id));

    try {
        const resp = await fetch(window.location.href, { method: 'POST', body: formData });
        const res = await resp.json();
        
        if (res.success) {
            Object.entries(res.data).forEach(([id, matches]) => {
                const row = document.getElementById(`review-row-${id}`);
                const sugBox = document.getElementById(`suggestions-${id}`);
                const select = document.getElementById(`tour-select-${id}`);
                
                row.classList.add('row-suggested');
                
                // Set the best match in the select box
                if (matches.length > 0) {
                    select.value = matches[0].id;
                    pendingMappings[id] = matches[0].id;
                }

                sugBox.innerHTML = `
                    <div class="suggestion-box">
                        <b>Top Matches:</b><br>
                        ${matches.map(m => `
                            <span class="suggestion-item" onclick="pickSuggestion(${id}, ${m.id})">
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

function pickSuggestion(cid, pid) {
    const select = document.getElementById(`tour-select-${cid}`);
    select.value = pid;
    pendingMappings[cid] = pid;
}

async function confirmBulkUpdates() {
    if (Object.keys(pendingMappings).length === 0) return;
    
    const formData = new FormData();
    formData.append('action', 'klld_bulk_update_post');
    Object.entries(pendingMappings).forEach(([cid, pid]) => {
        formData.append(`mappings[${cid}]`, pid);
    });

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
    for (let i = 1; i <= totalPages; i++) {
        if (i > 10 && i < totalPages - 2) { 
            if (html.indexOf('...') === -1) html += '<span>...</span>';
            continue; 
        }
        html += `<button onclick="fetchReviews(${i})" class="k-page-link ${i === active ? 'active' : ''}">${i}</button>`;
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

// Initial Load
fetchReviews(1);
</script>
<?php
if ( ! defined( "KLLD_TOOL_RUN" ) ) { ?>
</body>
</html><?php } ?>