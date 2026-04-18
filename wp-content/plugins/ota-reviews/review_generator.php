<?php
/**
 * KLLD Review Generator
 * Seeds tour reviews from GMB templates with auto-date parsing.
 */

if ( ! defined( 'ABSPATH' ) ) {
    require_once dirname(__FILE__, 5) . '/wp-load.php';
}

if (!current_user_can('manage_options') && !defined('KLLD_TOOL_RUN')) die('Unauthorized');

// AJAX Handler
if (isset($_POST['action']) && $_POST['action'] === 'generate_custom_reviews') {
    $post_ids = isset($_POST['post_ids']) ? array_map('intval', explode(',', $_POST['post_ids'])) : [];
    if (empty($post_ids) && isset($_POST['post_id'])) {
        $post_ids = [intval($_POST['post_id'])];
    }
    
    $count = intval($_POST['count'] ?? 1);
    $approve = ($_POST['approve'] === 'true');
    
    $templates = null;
    if (!empty($_POST['custom_templates'])) {
        $templates = json_decode(stripslashes($_POST['custom_templates']), true);
    }

    if (!$templates) {
        $template_file = dirname(__FILE__) . '/gmb_reviews.json';
        if (!file_exists($template_file)) {
            wp_send_json_error(['message' => 'Template file not found.']);
        }
        $templates = json_decode(file_get_contents($template_file), true);
    }

    if (!$templates) {
        wp_send_json_error(['message' => 'Invalid template JSON.']);
    }
    
    $total_imported = 0;
    $results = [];

    foreach ($post_ids as $post_id) {
        $tour_templates = $templates;
        shuffle($tour_templates);
        $selected = array_slice($tour_templates, 0, $count);
        $tour_imported = 0;

        foreach ($selected as $tpl) {
            $relative_date = $tpl['date'] ?? '1 month ago';
            $timestamp = klld_get_relative_timestamp($relative_date);
            $full_date = date('Y-m-d H:i:s', $timestamp);
            $formatted_date = date('d-M-Y', $timestamp); // Format: 17-Apr-2026
            
            $comment_data = [
                'comment_post_ID' => $post_id,
                'comment_author' => $tpl['author'] ?? 'Guest',
                'comment_content' => $tpl['text'] ?? '',
                'comment_type' => 'st_reviews',
                'comment_parent' => 0,
                'user_id' => 0,
                'comment_author_IP' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
                'comment_agent' => 'KLLD-Review-Gen',
                'comment_date' => $full_date,
                'comment_approved' => $approve ? 1 : 0,
            ];

            $comment_id = wp_insert_comment($comment_data);
            if ($comment_id) {
                $rating = 5;
                if (preg_match('/(\d+)/', $tpl['rating'] ?? '5', $rm)) {
                    $rating = intval($rm[1]);
                }
                
                update_comment_meta($comment_id, 'comment_rate', $rating);
                update_comment_meta($comment_id, 'st_stat_distance', $rating);
                update_comment_meta($comment_id, 'st_stat_accommodation', $rating);
                update_comment_meta($comment_id, 'st_stat_meals', $rating);
                update_comment_meta($comment_id, 'st_stat_tour-guide', $rating);
                update_comment_meta($comment_id, 'st_stat_service', $rating);
                update_comment_meta($comment_id, 'st_stat_driver', $rating);
                update_comment_meta($comment_id, 'gmb_review_id', 'gen_' . uniqid());
                update_comment_meta($comment_id, 'ota_source', 'google');
                update_comment_meta($comment_id, 'review_date_formatted', $formatted_date);
                
                $tour_imported++;
                $total_imported++;
            }
        }
        
        if ($tour_imported > 0) {
            klld_update_tour_review_summary($post_id);
            $results[] = get_the_title($post_id) . ": $tour_imported";
        }
    }

    if ($total_imported > 0) {
        wp_send_json_success([
            'message' => "Successfully generated $total_imported reviews across " . count($post_ids) . " tours.",
            'details' => implode(', ', $results)
        ]);
    } else {
        wp_send_json_error(['message' => 'No reviews were generated. Check tour selection.']);
    }
}

function klld_get_relative_timestamp($s) {
    if (empty($s)) return time();
    $s = strtolower(trim($s));
    
    // Handle "X months ago" etc.
    if (preg_match('/(\d+)\s+(year|month|week|day|hour|minute|second)s?\s+ago/', $s, $m)) {
        $amount = (int)$m[1];
        $unit = $m[2];
        return strtotime("-$amount $unit");
    }
    
    // Fallback to standard strtotime
    $ts = strtotime($s);
    if (!$ts) {
        // Handle variations like "5 months" (without ago)
        if (preg_match('/(\d+)\s+(year|month|week|day)s?/', $s, $m)) {
             return strtotime("-" . $m[1] . " " . $m[2]);
        }
    }
    
    return $ts ? $ts : time();
}

function klld_update_tour_review_summary($post_id) {
    global $wpdb;
    $stats = $wpdb->get_row($wpdb->prepare(
        "SELECT COUNT(*) as total, AVG(CAST(m.meta_value AS DECIMAL(10,1))) as avg 
         FROM $wpdb->comments c 
         JOIN $wpdb->commentmeta m ON c.comment_ID = m.comment_id 
         WHERE c.comment_post_ID = %d AND c.comment_approved = '1' AND m.meta_key = 'comment_rate'",
        $post_id
    ));

    if ($stats) {
        update_post_meta($post_id, 'total_review', intval($stats->total));
        update_post_meta($post_id, 'rate_review', round($stats->avg, 1));
    }
}

// UI Rendering
?>
<div class="wrap" id="klld-review-gen">
    <style>
        #klld-review-gen { max-width: 1000px; margin-top: 20px; font-family: 'Inter', sans-serif; }
        .gen-card { background: white; padding: 30px; border-radius: 12px; border: 1px solid #e2e8f0; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); }
        .gen-header { margin-bottom: 25px; border-bottom: 1px solid #f1f5f9; padding-bottom: 15px; display: flex; justify-content: space-between; align-items: center; }
        .gen-header h1 { margin: 0; color: #1e293b; font-size: 24px; }
        .gen-layout { display: grid; grid-template-columns: 350px 1fr; gap: 30px; }
        
        .form-panel { background: #f8fafc; padding: 20px; border-radius: 8px; border: 1px solid #e2e8f0; }
        .form-row { margin-bottom: 15px; }
        .form-row label { display: block; font-weight: 600; margin-bottom: 6px; color: #475569; font-size: 13px; }
        .form-row select, .form-row input[type="number"] { width: 100%; padding: 8px; border: 1px solid #cbd5e1; border-radius: 6px; }
        
        .gen-btn { background: #0ea5e9; color: white; border: none; padding: 12px 24px; border-radius: 6px; font-weight: 700; cursor: pointer; transition: all 0.2s; width: 100%; margin-top: 10px; }
        .gen-btn:hover { background: #0284c7; transform: translateY(-1px); }
        .gen-btn:disabled { background: #94a3b8; cursor: not-allowed; }
        
        .list-panel h3 { margin-top: 0; font-size: 18px; color: #1e293b; margin-bottom: 20px; }
        .templates-list { max-height: 600px; overflow-y: auto; display: grid; gap: 15px; }
        .tpl-item { background: white; border: 1px solid #e2e8f0; padding: 15px; border-radius: 8px; position: relative; transition: border-color 0.2s; }
        .tpl-item:hover { border-color: #0ea5e9; }
        .tpl-item input[type="checkbox"] { position: absolute; top: 15px; right: 15px; }
        .tpl-header { display: flex; align-items: center; gap: 10px; margin-bottom: 8px; }
        .tpl-author { font-weight: 700; color: #1e293b; }
        .tpl-date { font-size: 11px; color: #0ea5e9; font-weight: 600; background: #f0f9ff; padding: 2px 6px; border-radius: 4px; }
        .tpl-text { font-size: 13px; color: #475569; line-height: 1.5; }
        .tpl-rating { color: #f59e0b; font-size: 12px; }
        
        #gen-status { margin-top: 15px; padding: 12px; border-radius: 6px; display: none; margin-bottom: 20px; font-weight: 600; }
        .status-success { background: #f0fdf4; color: #16a34a; border: 1px solid #bbf7d0; }
        .status-error { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; }
    </style>

    <div class="gen-card">
        <div class="gen-header">
            <div>
                <h1>🚀 Review Generator</h1>
                <p>Select templates and seed them to your tours.</p>
            </div>
            <button class="button" id="btn-refresh-tpls">🔄 Refresh Templates</button>
        </div>

        <div id="gen-status"></div>

        <div class="gen-layout">
            <div class="form-panel">
                <div class="form-row">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px;">
                        <label style="margin:0;">Target Tours [Batch Select]</label>
                        <a href="#" id="select-all-tours" style="font-size:11px; text-decoration:none; color:#0ea5e9;">Select All</a>
                    </div>
                    <select id="gen-tour-ids" multiple style="height: 250px;">
                        <?php
                        $tours = get_posts(['post_type' => 'st_tours', 'posts_per_page' => -1, 'orderby' => 'title', 'order' => 'ASC', 'post_status' => 'publish']);
                        foreach ($tours as $t) {
                            echo '<option value="' . $t->ID . '">' . esc_html($t->post_title) . '</option>';
                        }
                        ?>
                    </select>
                </div>

                <div class="form-row">
                    <label>Reviews per Tour</label>
                    <input type="number" id="gen-count" value="3" min="1" max="50">
                    <p style="font-size:11px; color:#64748b; margin-top:5px;">Seeding limit per selected tour.</p>
                </div>

                <div class="form-row" style="display: flex; gap: 8px; align-items: center; margin-top:20px;">
                    <input type="checkbox" id="gen-approve" checked>
                    <label for="gen-approve" style="margin: 0;">Approve Directly</label>
                </div>

                <button class="gen-btn" id="btn-run-gen">🚀 Launch Batch Seeding</button>
            </div>

            <div class="list-panel">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
                    <h3 style="margin:0;">Template Library Preview</h3>
                    <div style="display:flex; align-items:center; gap:10px;">
                        <input type="checkbox" id="select-all-templates" checked>
                        <label for="select-all-templates" style="font-size:12px; font-weight:600;">Select All</label>
                    </div>
                </div>
                <div class="templates-list" id="tpl-container">
                    <!-- Templates injected here -->
                </div>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const btn = document.getElementById('btn-run-gen');
        const status = document.getElementById('gen-status');
        const tourSelect = document.getElementById('gen-tour-ids');
        const count = document.getElementById('gen-count');
        const approve = document.getElementById('gen-approve');
        const tplContainer = document.getElementById('tpl-container');
        const refreshBtn = document.getElementById('btn-refresh-tpls');
        const selectAllTours = document.getElementById('select-all-tours');
        const selectAllTemplates = document.getElementById('select-all-templates');
 
        const templates = <?php echo file_get_contents(dirname(__FILE__) . '/gmb_reviews.json'); ?>;

        function renderTemplates() {
            tplContainer.innerHTML = '';
            templates.forEach((tpl, idx) => {
                const item = document.createElement('div');
                item.className = 'tpl-item';
                
                const relative = tpl.date || 'unknown';
                
                item.innerHTML = `
                    <input type="checkbox" class="tpl-checkbox" data-idx="${idx}" checked>
                    <div class="tpl-header">
                        <span class="tpl-author">${tpl.author}</span>
                        <span class="tpl-date">${relative}</span>
                    </div>
                    <div class="tpl-rating">${tpl.rating}</div>
                    <div class="tpl-text">${tpl.text.substring(0, 150)}${tpl.text.length > 150 ? '...' : ''}</div>
                `;
                tplContainer.appendChild(item);
            });
        }

        renderTemplates();

        // Select All Tours Logic
        selectAllTours.addEventListener('click', function(e) {
            e.preventDefault();
            const allSelected = Array.from(tourSelect.options).every(opt => opt.selected);
            for (let i = 0; i < tourSelect.options.length; i++) {
                tourSelect.options[i].selected = !allSelected;
            }
            this.textContent = allSelected ? 'Select All' : 'Deselect All';
        });

        // Select All Templates Logic
        selectAllTemplates.addEventListener('change', function() {
            const checks = document.querySelectorAll('.tpl-checkbox');
            checks.forEach(c => c.checked = this.checked);
        });

        btn.addEventListener('click', async function() {
            const selectedTours = Array.from(tourSelect.selectedOptions).map(opt => opt.value);
            if (selectedTours.length === 0) {
                alert('Please select at least one target tour.');
                return;
            }

            const selectedTplIndices = Array.from(document.querySelectorAll('.tpl-checkbox:checked')).map(c => parseInt(c.dataset.idx));
            if (selectedTplIndices.length === 0) {
                alert('Please select at least one template.');
                return;
            }

            btn.disabled = true;
            btn.innerHTML = '<span class="spinner is-active" style="float:none; margin:0 5px 0 0;"></span> Processing Batch...';
            status.style.display = 'none';

            // Filter templates based on selection
            const activeTemplates = templates.filter((_, idx) => selectedTplIndices.includes(idx));

            const formData = new FormData();
            formData.append('action', 'generate_custom_reviews');
            formData.append('post_ids', selectedTours.join(','));
            formData.append('approve', approve.checked);
            formData.append('count', count.value);
            
            // We need to pass the specific templates if we want the backend to use them, 
            // OR we just handle the seeding in a more customized way.
            // For now, let's just use the count and assume random from the full list on backend to stay simple, 
            // OR update the backend to accept specific template indices.
            // Actually, the backend shuffles from the FILE.
            
            // Let's update the backend to accept a templates_json param to be more precise.
            formData.append('custom_templates', JSON.stringify(activeTemplates));

            try {
                const response = await fetch(ajaxurl, { method: 'POST', body: formData });
                const result = await response.json();
                status.style.display = 'block';
                if (result.success) {
                    status.className = 'status-success';
                    status.innerHTML = `<strong>${result.data.message}</strong><br><small>${result.data.details}</small>`;
                    status.scrollIntoView({ behavior: 'smooth' });
                } else {
                    status.className = 'status-error';
                    status.textContent = 'Error: ' + result.data.message;
                }
            } catch (e) {
                status.style.display = 'block';
                status.className = 'status-error';
                status.textContent = 'Request failed. Check server logs.';
            } finally {
                btn.disabled = false;
                btn.innerHTML = '🚀 Launch Batch Seeding';
            }
        });

        refreshBtn.addEventListener('click', () => {
            renderTemplates();
        });
    });
    </script>
</div>
