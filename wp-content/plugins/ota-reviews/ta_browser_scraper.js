/**
 * TripAdvisor Supplier Profile Scraper (Browser Console)
 * 
 * Instructions:
 * 1. Open your TripAdvisor Supplier/Business page.
 * 2. Open Chrome DevTools (F12) -> Console.
 * 3. Paste this script and press Enter.
 * 4. It will scrape the current page and send it to your server.
 * 5. You can then click "Next" on TripAdvisor and run it again, 
 *    or modify the script to auto-click.
 */

(async function() {
    console.log("🚀 Starting TripAdvisor Scraper...");

    const reviews = [];
    const cards = document.querySelectorAll('div[data-automation="reviewCard"]');
    
    cards.forEach(card => {
        // Basic Info
        const id = card.id || Math.random().toString(36).substr(2, 9);
        const name = card.querySelector('span.biGQs')?.innerText || "TripAdvisor Traveler";
        const title = card.querySelector('div[data-automation="reviewTitle"]')?.innerText || "";
        const text = card.querySelector('span.ySdfQ')?.innerText || "";
        
        // Rating (bubbles)
        const bubbles = card.querySelector('span.ui_bubble_rating');
        let rating = 5;
        if (bubbles) {
            const cls = bubbles.className;
            const match = cls.match(/bubble_(\d+)/);
            if (match) rating = parseInt(match[1]) / 10;
        }

        // Date
        const dateText = card.querySelector('div.biGQs.pZpkB.ncFvB')?.innerText || "";
        // Usually "Written March 2024" or "March 2024"
        const date = dateText.replace("Written ", "").trim();

        // Avatar
        const avatar = card.querySelector('img.cyS_u')?.src || null;

        // CRITICAL: "Review of" (Tour Name)
        // Usually looks like "Review of: Similan Islands Early Bird..."
        let reviewOf = "";
        const links = card.querySelectorAll('a');
        links.forEach(link => {
            if (link.innerText.includes("Review of")) {
                reviewOf = link.innerText.replace("Review of:", "").replace("Review of", "").trim();
            }
        });

        reviews.push({
            id: id,
            n: name,
            ti: title,
            co: text,
            rt: rating,
            dt: date,
            av: avatar,
            ro: reviewOf
        });
    });

    console.log(`✅ Scraped ${reviews.length} reviews from this page.`);
    console.log(`Sample "Review of": ${reviews[0]?.ro || "None found"}`);

    if (reviews.length === 0) {
        console.error("❌ No reviews found. Are you on the right page?");
        return;
    }

    // Send to receiver
    const endpoint = "https://khaolaklanddiscovery.com/wp-content/plugins/ota-reviews/ta_receiver.php";
    const batchName = "page_" + (new URLSearchParams(window.location.search).get('filterLang') || 'all') + "_" + Date.now();

    try {
        const response = await fetch(endpoint, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                batch: batchName,
                reviews: reviews
            })
        });
        const result = await response.json();
        console.log("🎉 Server Response:", result);
        console.log("👉 Go to WP and run: php wp-content/plugins/ota-reviews/import_ta_reviews.php");
    } catch (err) {
        console.error("❌ Failed to send to server:", err);
    }
})();
