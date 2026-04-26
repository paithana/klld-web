<?php
/**
 * OTAs Manager - Centralized Settings
 * Manages all API keys, tokens, and system configurations.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// ── AJAX Handler: Save All Settings ───────────────────────────────────────
if (isset($_POST['action']) && $_POST['action'] === 'klld_save_ota_settings') {
    // API Keys
    if (isset($_POST['openai_key'])) update_option('_klld_openai_api_key', sanitize_text_field($_POST['openai_key']));
    if (isset($_POST['ta_key']))     update_option('_ta_api_key', sanitize_text_field($_POST['ta_key']));
    if (isset($_POST['gyg_key']))    update_option('_gyg_partner_api_key', sanitize_text_field($_POST['gyg_key']));
    if (isset($_POST['gyg_token']))  update_option('_gyg_explorer_token', sanitize_text_field($_POST['gyg_token']));
    if (isset($_POST['omkar_key']))  update_option('_omkar_api_key', sanitize_text_field($_POST['omkar_key']));

    // SFTP Settings
    if (isset($_POST['sftp_host'])) update_option('_gttd_sftp_host', sanitize_text_field($_POST['sftp_host']));
    if (isset($_POST['sftp_port'])) update_option('_gttd_sftp_port', intval($_POST['sftp_port']));
    if (isset($_POST['sftp_user'])) update_option('_gttd_sftp_user', sanitize_text_field($_POST['sftp_user']));
    if (isset($_POST['sftp_pass'])) update_option('_gttd_sftp_pass', sanitize_text_field($_POST['sftp_pass']));
    if (isset($_POST['sftp_key']))  update_option('_gttd_sftp_key', sanitize_text_field($_POST['sftp_key']));
    if (isset($_POST['sftp_file'])) update_option('_gttd_sftp_file', sanitize_text_field($_POST['sftp_file']));

    wp_send_json_success('Settings saved successfully.');
}

// Load current values
$openai_key = get_option('_klld_openai_api_key', '');
$ta_key     = get_option('_ta_api_key', '');
$gyg_key    = get_option('_gyg_partner_api_key', '');
$gyg_token  = get_option('_gyg_explorer_token', '');
$omkar_key  = get_option('_omkar_api_key', '');

$sftp_host  = get_option('_gttd_sftp_host', 'partnerupload.google.com');
$sftp_port  = get_option('_gttd_sftp_port', 19321);
$sftp_user  = get_option('_gttd_sftp_user', 'mc-sftp-5520609361');
$sftp_pass  = get_option('_gttd_sftp_pass', '');
$sftp_key   = get_option('_gttd_sftp_key', '/home/u451564824/.ssh/gttd_rsa');
$sftp_file  = get_option('_gttd_sftp_file', 'tours_feed.xml');
?>

<div class="wrap" id="klld-ota-settings">
    <style>
        #klld-ota-settings { margin-top: 20px; font-family: 'Inter', system-ui, sans-serif; max-width: 1000px; }
        .s-header { background: linear-gradient(135deg, #0ea5e9, #6366f1); color: white; padding: 30px; border-radius: 12px; margin-bottom: 25px; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.1); }
        .s-header h1 { color: white; margin: 0; font-size: 28px; }
        
        .s-card { background: white; border-radius: 12px; padding: 25px; border: 1px solid #e2e8f0; margin-bottom: 25px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); }
        .s-card h2 { margin-top: 0; font-size: 18px; color: #0f172a; margin-bottom: 20px; border-bottom: 1px solid #f1f5f9; padding-bottom: 10px; }
        
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; font-size: 12px; font-weight: 700; color: #64748b; text-transform: uppercase; margin-bottom: 8px; }
        
        .k-input { width: 100%; padding: 10px 15px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 14px; transition: all 0.2s; }
        .k-input:focus { border-color: #0ea5e9; outline: none; box-shadow: 0 0 0 3px rgba(14, 165, 233, 0.15); }
        
        .k-btn-save { padding: 12px 30px; background: #0ea5e9; color: white; border-radius: 8px; font-weight: 700; border: none; cursor: pointer; transition: all 0.2s; font-size: 15px; }
        .k-btn-save:hover { background: #0284c7; transform: translateY(-1px); box-shadow: 0 4px 12px rgba(14, 165, 233, 0.3); }

        .help-link { color: #0ea5e9; text-decoration: none; font-size: 11px; margin-left: 5px; }
        .help-link:hover { text-decoration: underline; }
        
        @media (max-width: 768px) { .form-grid { grid-template-columns: 1fr; } }
    </style>

    <div class="s-header">
        <h1>⚙️ System Settings</h1>
        <p style="margin-top:5px; opacity:0.9;">Manage all API connections, security tokens, and delivery endpoints.</p>
    </div>

    <form id="ota-settings-form">
        <input type="hidden" name="action" value="klld_save_ota_settings">

        <!-- Section: AI & Marketing -->
        <div class="s-card">
            <h2>🤖 AI & Content Tools</h2>
            <div class="form-group">
                <label>OpenAI API Key <span style="font-weight:400;">(For Batch Headlines)</span></label>
                <input type="password" name="openai_key" class="k-input" value="<?php echo esc_attr($openai_key); ?>" placeholder="sk-...">
                <p class="description">Used by the AI Review Writer for automated summarization.</p>
            </div>
        </div>

        <!-- Section: OTA Platforms -->
        <div class="s-card">
            <h2>🔄 OTA Platform Connectivity</h2>
            <div class="form-grid">
                <div class="form-group">
                    <label>TripAdvisor API Key <a href="https://www.tripadvisor.com/ContentAPIRequest" target="_blank" class="help-link">(Get Key)</a></label>
                    <input type="password" name="ta_key" class="k-input" value="<?php echo esc_attr($ta_key); ?>" placeholder="Enter key...">
                </div>
                <div class="form-group">
                    <label>GYG Partner API Key <a href="https://partner.getyourguide.com/api-management" target="_blank" class="help-link">(Get Key)</a></label>
                    <input type="password" name="gyg_key" class="k-input" value="<?php echo esc_attr($gyg_key); ?>" placeholder="Enter key...">
                </div>
                <div class="form-group">
                    <label>GYG Explorer Token <span style="font-weight:400; opacity:0.7;">(Required for Discovery)</span></label>
                    <input type="password" name="gyg_token" class="k-input" value="<?php echo esc_attr($gyg_token); ?>" placeholder="Basic ...">
                </div>
                <div class="form-group">
                    <label>Omkar Scraper API Key <a href="https://www.omkar.cloud/tripadvisor/reviews/api/" target="_blank" class="help-link">(Dashboard)</a></label>
                    <input type="password" name="omkar_key" class="k-input" value="<?php echo esc_attr($omkar_key); ?>" placeholder="ok_...">
                </div>
            </div>
        </div>

        <!-- Section: GTTD Feed -->
        <div class="s-card">
            <h2>📡 Google Things to Do (SFTP)</h2>
            <div class="form-grid">
                <div class="form-group">
                    <label>SFTP Host</label>
                    <input type="text" name="sftp_host" class="k-input" value="<?php echo esc_attr($sftp_host); ?>">
                </div>
                <div class="form-group">
                    <label>SFTP Port</label>
                    <input type="number" name="sftp_port" class="k-input" value="<?php echo esc_attr($sftp_port); ?>">
                </div>
                <div class="form-group">
                    <label>SFTP Username</label>
                    <input type="text" name="sftp_user" class="k-input" value="<?php echo esc_attr($sftp_user); ?>">
                </div>
                <div class="form-group">
                    <label>SFTP Password</label>
                    <input type="password" name="sftp_pass" class="k-input" value="<?php echo esc_attr($sftp_pass); ?>">
                </div>
                <div class="form-group">
                    <label>Private Key Path</label>
                    <input type="text" name="sftp_key" class="k-input" value="<?php echo esc_attr($sftp_key); ?>">
                </div>
                <div class="form-group">
                    <label>Target Filename (.xml)</label>
                    <input type="text" name="sftp_file" class="k-input" value="<?php echo esc_attr($sftp_file); ?>">
                </div>
            </div>
        </div>

        <div style="margin-top: 30px; text-align: right; padding-bottom: 50px;">
            <button type="submit" class="k-btn-save">💾 Save All System Settings</button>
        </div>
    </form>
</div>

<script>
document.getElementById('ota-settings-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn = e.target.querySelector('button');
    const originalText = btn.innerText;
    
    btn.disabled = true;
    btn.innerText = 'Saving...';
    
    const formData = new FormData(this);
    try {
        const resp = await fetch(window.location.href, { method: 'POST', body: formData });
        const data = await resp.json();
        if (data.success) {
            alert('✅ ' + data.data);
        } else {
            alert('❌ Error: ' + data.data);
        }
    } catch (err) {
        alert('❌ Network Error: ' + err.message);
    } finally {
        btn.disabled = false;
        btn.innerText = originalText;
    }
});
</script>
