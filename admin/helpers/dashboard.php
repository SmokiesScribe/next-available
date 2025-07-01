<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * The callback for the top-level menu.
 * 
 * @since 1.0.28
 */
function nextav_dashboard_content() {
    ?>
    <div class="nextav-admin-wrap">
        <h1 class="nextav-admin-title">Display your next available date from your connected calendar.</h1>

        <section class="nextav-section">
            <h2>📌 Shortcodes</h2>
            <p>Use this shortcode to display your next available date:</p>
            <div class="nextav-code-block"><code>[next_available]</code></div>

            <p>To show the last time the date was updated:</p>
            <div class="nextav-code-block"><code>[next_available_updated]</code></div>
        </section>

        <section class="nextav-section">
            <h3>🛠 Available Parameters</h3>
            <ul>
                <li><code>format</code> — Optional. PHP date format. Default is <code>F j, Y</code>.</li>
            </ul>
        </section>

        <section class="nextav-section">
            <h3>🧪 Examples</h3>
            <ul class="nextav-code-list">
                <li><code>[next_available format="Y-m-d"]</code> → 2025-07-01</li>
                <li><code>[next_available format="m/d/Y"]</code> → 07/01/2025</li>
                <li><code>[next_available format="d/m/Y"]</code> → 01/07/2025</li>
                <li><code>[next_available format="F j, Y"]</code> → July 1, 2025</li>
                <li><code>[next_available format="j F Y"]</code> → 1 July 2025</li>
                <li><code>[next_available format="D, M j, Y"]</code> → Tue, Jul 1, 2025</li>
                <li><code>[next_available format="l, F j, Y"]</code> → Tuesday, July 1, 2025</li>
            </ul>
        </section>

        <section class="nextav-section">
            <h2>⚙️ Setup Instructions</h2>
            <ol>
                <li>Go to <strong>Settings → Integrations</strong>.</li>
                <li>Connect your Google account and authorize calendar access.</li>
                <li>Select the calendar you want to use.</li>
                <li>Paste the <code>[next_available]</code> shortcode into any post, page, or widget.</li>
            </ol>
        </section>

        <section class="nextav-section">
            <h2>🔍 Notes</h2>
            <ul>
                <li>The plugin fetches real-time availability from your calendar.</li>
                <li>You can change the display format using the <code>format</code> parameter.</li>
            </ul>
        </section>
    </div>
    <?php
}
