<?php
/**
 * AI Review Writer - Summarizes content and generates titles.
 * Uses OpenAI API to process reviews missing headlines.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

$openai_key = get_option('_klld_openai_api_key', '');

// ── AJAX Handler: Generate Title ──────────────────────────────────────────
if (isset($_POST['action']) && $_POST['action'] === 'klld_ai_generate_title') {
    $comment_id = intval($_POST['id']);
    $content = sanitize_textarea_field($_POST['content']);
    
    if (empty($openai_key)) {
        wp_send_json_error('OpenAI API Key is missing. Please save it first.');
    }

    if (empty($content)) {
        wp_send_json_error('Review content is empty.');
    }

    $prompt = "Generate a short, catchy, and professional review headline (maximum 6 words) based on this customer review content. Just give me the headline text, nothing else.\n\nReview: " . $content;

    $response = wp_remote_post('https://api.openai.com/v1/chat/completions', [
        'timeout' => 30,
        'headers' => [
            'Authorization' => 'Bearer ' . $openai_key,
            'Content-Type'  => 'application/json'
        ],
        'body' => json_encode([
            'model' => 'gpt-3.5-turbo',
            'messages' => [
                ['role' => 'system', 'content' => 'You are a helpful assistant for a travel agency.'],
                ['role' => 'user', 'content' => $prompt]
            ],
            'temperature' => 0.7,
            'max_tokens' => 30
        ])
    ]);

    if (is_wp_error($response)) {
        wp_send_json_error($response->get_error_message());
    }

    $body = json_decode(wp_remote_retrieve_body($response), true);
    $headline = $body['choices'][0]['message']['content'] ?? '';
    $headline = trim($headline, ' "');

    if ($headline) {
        update_comment_meta($comment_id, 'comment_title', $headline);
        wp_send_json_success(['title' => $headline]);
    } else {
        wp_send_json_error('Failed to generate headline. Response: ' . print_r($body, true));
    }
}

// Fetch Reviews Missing Titles
global $wpdb;
$missing_titles = $wpdb->get_results("
    SELECT c.comment_ID, c.comment_content, c.comment_author 
    FROM {$wpdb->comments} c 
    LEFT JOIN {$wpdb->commentmeta} m ON c.comment_ID = m.comment_id AND m.meta_key = 'comment_title' 
    WHERE c.comment_type = 'st_reviews' 
    AND (m.meta_value IS NULL OR m.meta_value = '') 
    LIMIT 50
");

?>
<div class="wrap" id="klld-ai-writer">
    <style>
        #klld-ai-writer { margin-top: 20px; font-family: 'Inter', system-ui, sans-serif; }
        .ai-header { background: linear-gradient(135deg, #8b5cf6, #d946ef); color: white; padding: 30px; border-radius: 12px; margin-bottom: 25px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 10px 15px -3px rgba(139, 92, 246, 0.2); }
        .ai-header h1 { color: white; margin: 0; font-size: 28px; }
        
        .ai-card { background: white; border-radius: 12px; padding: 25px; border: 1px solid #e2e8f0; margin-bottom: 25px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); }
        .ai-card h2 { margin-top: 0; font-size: 18px; color: #1e293b; margin-bottom: 15px; display: flex; align-items: center; gap: 10px; }
        
        .k-input { padding: 10px 15px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 14px; transition: all 0.2s; }
        .k-btn { padding: 9px 16px; border-radius: 8px; border: 1px solid transparent; font-size: 13px; font-weight: 600; cursor: pointer; transition: all 0.2s; display: inline-flex; align-items: center; gap: 6px; }
        .k-btn-primary { background: #8b5cf6; color: white; border: 1px solid #7c3aed; }
        .k-btn-primary:hover { background: #7c3aed; transform: translateY(-1px); }
        
        .review-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(400px, 1fr)); gap: 20px; }
        .review-item { background: white; border: 1px solid #e2e8f0; padding: 20px; border-radius: 12px; position: relative; transition: all 0.3s; }
        .review-item:hover { border-color: #8b5cf6; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.05); }
        .author { font-weight: 700; color: #4b5563; font-size: 12px; text-transform: uppercase; margin-bottom: 5px; }
        .content { color: #1f2937; font-size: 14px; line-height: 1.6; margin-bottom: 15px; height: 100px; overflow-y: auto; background: #f9fafb; padding: 10px; border-radius: 6px; }
        .title-preview { font-weight: 800; color: #8b5cf6; margin-top: 10px; font-style: italic; min-height: 20px; }
        
        .status-pill { padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 700; }
        .status-missing { background: #fef2f2; color: #dc2626; }
        .status-done { background: #f0fdf4; color: #16a34a; }
    </style>

    <div class="ai-header">
        <h1>🤖 AI Review Writer</h1>
        <div style="font-size: 13px; opacity: 0.9;">Powered by OpenAI GPT-3.5 & Ahrefs Writing Tools</div>
    </div>

    <div class="ai-card" style="background: #fdf2f2; border-color: #fecaca;">
        <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:15px;">
            <div style="font-size: 14px; color: #991b1b; line-height: 1.5;">
                <b>System Notice:</b> API Keys and Global Configurations have been moved to the centralized settings dashboard.
            </div>
            <a href="?page=klld-ota-settings" class="k-btn k-btn-outline" style="background:white; border-color:#ef4444; color:#ef4444;">⚙️ Configure AI Keys</a>
        </div>
    </div>

    <div class="ai-card">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
            <h2>📝 Reviews Missing Titles (<?php echo count($missing_titles); ?>)</h2>
            <div style="display:flex; gap:10px;">
                <button onclick="generateAllTitles()" id="btn-batch" class="k-btn k-btn-primary" style="background: #10b981; border-color: #059669;">✨ Batch Generate (OpenAI)</button>
                <a href="https://ahrefs.com/writing-tools" target="_blank" class="k-btn k-btn-outline">🛠 Open Ahrefs Tools</a>
            </div>
        </div>
        
        <div class="review-grid">
            <?php if (empty($missing_titles)) : ?>
                <p>All reviews already have titles! Good job.</p>
            <?php else : ?>
                <?php foreach ($missing_titles as $r) : ?>
                    <div class="review-item" id="review-<?php echo $r->comment_ID; ?>">
                        <div class="author"><?php echo esc_html($r->comment_author); ?> • ID #<?php echo $r->comment_ID; ?></div>
                        <div class="content" id="content-<?php echo $r->comment_ID; ?>"><?php echo esc_html($r->comment_content); ?></div>
                        <div id="status-<?php echo $r->comment_ID; ?>">
                            <span class="status-pill status-missing">Missing Headline</span>
                        </div>
                        <div class="title-preview" id="preview-<?php echo $r->comment_ID; ?>"></div>
                        
                        <div style="margin-top:15px; border-top: 1px solid #f1f5f9; padding-top:15px; display:flex; flex-direction:column; gap:12px;">
                            <div>
                                <div style="font-size:10px; font-weight:700; color:#64748b; text-transform:uppercase; margin-bottom:8px;">1. Content Generation (Direct)</div>
                                <div style="display:flex; gap:8px;">
                                    <button onclick="generateTitle(<?php echo $r->comment_ID; ?>)" class="k-btn k-btn-primary" id="btn-<?php echo $r->comment_ID; ?>" style="flex:1;">OpenAI (Auto)</button>
                                    <button onclick="useAhrefs(<?php echo $r->comment_ID; ?>, 'headline')" class="k-btn k-btn-outline" style="flex:1; border-color:#38bdf8; color:#0284c7;">Ahrefs Title</button>
                                    <button onclick="useAhrefs(<?php echo $r->comment_ID; ?>, 'summarizer')" class="k-btn k-btn-outline" style="flex:1; border-color:#818cf8; color:#4f46e5;">Ahrefs Sum</button>
                                </div>
                            </div>
                            
                            <div>
                                <div style="font-size:10px; font-weight:700; color:#64748b; text-transform:uppercase; margin-bottom:8px;">2. Moderation & Marketing</div>
                                <div style="display:flex; gap:8px; flex-wrap:wrap;">
                                    <button onclick="useAhrefs(<?php echo $r->comment_ID; ?>, 'detector')" class="k-btn k-btn-outline" style="flex:1; border-color:#10b981; color:#059669; min-width:100px;" title="Check if content was AI generated">AI Check</button>
                                    <button onclick="useAhrefs(<?php echo $r->comment_ID; ?>, 'humanizer')" class="k-btn k-btn-outline" style="flex:1; border-color:#f59e0b; color:#d97706; min-width:100px;" title="Make text sound more natural">Humanize</button>
                                    <button onclick="useAhrefs(<?php echo $r->comment_ID; ?>, 'instagram')" class="k-btn k-btn-outline" style="flex:1; border-color:#ec4899; color:#db2777; min-width:100px;" title="Generate Instagram Caption">IG Post</button>
                                    <button onclick="useAhrefs(<?php echo $r->comment_ID; ?>, 'emoji')" class="k-btn k-btn-outline" style="flex:1; border-color:#8b5cf6; color:#7c3aed; min-width:100px;" title="Add Emojis to text">Add 🎨</button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
async function generateTitle(id) {
    const content = document.getElementById('content-' + id).innerText;
    const btn = document.getElementById('btn-' + id);
    const preview = document.getElementById('preview-' + id);
    const status = document.getElementById('status-' + id);

    btn.disabled = true;
    btn.innerText = 'Writing...';
    preview.innerText = 'Consulting AI...';

    const formData = new FormData();
    formData.append('action', 'klld_ai_generate_title');
    formData.append('id', id);
    formData.append('content', content);

    try {
        const resp = await fetch(window.location.href, { method: 'POST', body: formData });
        const res = await resp.json();
        
        if (res.success) {
            preview.innerText = '"' + res.data.title + '"';
            status.innerHTML = '<span class="status-pill status-done">Headline Generated</span>';
            btn.style.display = 'none';
        } else {
            preview.innerText = 'Error: ' + res.data;
            btn.disabled = false;
            btn.innerText = 'Retry';
        }
    } catch (e) {
        preview.innerText = 'Network error.';
        btn.disabled = false;
        btn.innerText = 'Retry';
    }
}

function useAhrefs(id, tool) {
    const content = document.getElementById('content-' + id).innerText;
    const encoded = encodeURIComponent(content);
    
    let url = 'https://ahrefs.com/writing-tools';
    switch(tool) {
        case 'headline':   url = `https://ahrefs.com/writing-tools/catchy-headline-generator?input=${encoded}`; break;
        case 'summarizer': url = `https://ahrefs.com/writing-tools/summarizer?input=${encoded}`; break;
        case 'detector':   url = `https://ahrefs.com/writing-tools/ai-content-detector?input=${encoded}`; break;
        case 'humanizer':  url = `https://ahrefs.com/writing-tools/ai-text-humanizer?input=${encoded}`; break;
        case 'instagram':  url = `https://ahrefs.com/writing-tools/instagram-caption-generator?input=${encoded}`; break;
        case 'emoji':      url = `https://ahrefs.com/writing-tools/emoji-translator?input=${encoded}`; break;
    }
    
    window.open(url, '_blank');
}


async function generateAllTitles() {
    const items = document.querySelectorAll('.review-item:not(.processed)');
    const btn = document.getElementById('btn-batch');
    btn.disabled = true;
    btn.innerText = 'Processing Batch...';

    for (const item of items) {
        const id = item.id.replace('review-', '');
        await generateTitle(id);
        item.classList.add('processed');
    }

    btn.innerText = 'Batch Complete!';
}
</script>
