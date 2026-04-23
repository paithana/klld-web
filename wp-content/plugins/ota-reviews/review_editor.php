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
    if (get_post_type($post_id) === 'st_tours') {
        $wpdb->update($wpdb->comments, ['comment_post_ID' => $post_id], ['comment_ID' => $id]);
        wp_send_json_success('Post ID updated.');
    } else {
        wp_send_json_error('Invalid Tour ID.');
    }
}

// ── AJAX Handler: Delete Review ──────────────────────────────────────────
if (isset($_POST['action']) && $_POST['action'] === 'klld_delete_review') {
    $id = intval($_POST['id']);
    wp_delete_comment($id, true);
    wp_send_json_success('Review deleted permanently.');
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
        
        .tour-select { width: 200px; font-size: 12px; }
        .status-select { font-size: 11px; font-weight: bold; border-radius: 4px; }
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

    <div class="k-table-card">
        <table class="k-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Source</th>
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

    body.innerHTML = '<tr><td colspan="7" style="text-align:center; padding:50px;">Loading reviews...</td></tr>';

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
        body.innerHTML = '<tr><td colspan="7" style="color:red; text-align:center;">Failed to load reviews.</td></tr>';
    }
}

function renderTable(reviews) {
    const body = document.getElementById('reviews-body');
    if (reviews.length === 0) {
        body.innerHTML = '<tr><td colspan="7" style="text-align:center; padding:50px;">No reviews found matching your search.</td></tr>';
        return;
    }

    body.innerHTML = reviews.map(r => `
        <tr id="review-row-${r.id}">
            <td>${r.id}</td>
            <td><span class="badge ota-badge badge-gyg">${r.source}</span></td>
            <td>
                <div style="font-weight:700;">${r.author}</div>
                <div style="font-size:11px; color:#64748b;">${r.date}</div>
            </td>
            <td style="max-width:300px;">
                <div title="${r.content}">${r.content}</div>
            </td>
            <td>
                <select onchange="updatePostId(${r.id}, this.value)" class="k-input tour-select">
                    <?php foreach ($tours as $t) { ?>
                        <option value="<?php echo $t->ID; ?>" \${r.post_id == <?php echo $t->ID; ?> ? 'selected' : ''}>
                            <?php echo esc_js($t->post_title); ?>
                        </option>
                    <?php } ?>
                </select>
                <div style="font-size:10px; color:#94a3b8; margin-top:4px;">ID: \${r.post_id}</div>
            </td>
            <td>
                <select onchange="updateStatus(${r.id}, this.value)" class="k-input status-select">
                    <option value="1" \${r.status == '1' ? 'selected' : ''}>Approved</option>
                    <option value="0" \${r.status == '0' ? 'selected' : ''}>Pending</option>
                    <option value="trash" \${r.status == 'trash' ? 'selected' : ''}>Trash</option>
                </select>
            </td>
            <td>
                <button onclick="deleteReview(${r.id})" class="k-btn k-btn-danger">Delete</button>
            </td>
        </tr>
    `).join('');
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