(async function() {
    console.log("🚀 Starting TripAdvisor Scraper...");
    const reviews = [];
    const cards = document.querySelectorAll('div[data-automation="reviewCard"]');
    
    cards.forEach(card => {
        const id = card.id || Math.random().toString(36).substr(2, 9);
        const name = card.querySelector('span.biGQs')?.innerText || "TripAdvisor Traveler";
        const title = card.querySelector('div[data-automation="reviewTitle"]')?.innerText || "";
        const text = card.querySelector('span.ySdfQ')?.innerText || "";
        const bubbles = card.querySelector('span.ui_bubble_rating');
        let rating = 5;
        if (bubbles) {
            const cls = bubbles.className;
            const match = cls.match(/bubble_(\d+)/);
            if (match) rating = parseInt(match[1]) / 10;
        }
        const dateText = card.querySelector('div.biGQs.pZpkB.ncFvB')?.innerText || "";
        const date = dateText.replace("Written ", "").trim();
        const avatar = card.querySelector('img.cyS_u')?.src || null;
        const photos = [];
        card.querySelectorAll('img').forEach(img => {
            if (img.src && img.src.includes('/media/photo-') && !img.src.includes('avatar')) {
                photos.push(img.src);
            }
        });
        let reviewOf = "";
        const links = card.querySelectorAll('a');
        links.forEach(link => {
            if (link.innerText.includes("Review of")) {
                reviewOf = link.innerText.replace("Review of:", "").replace("Review of", "").trim();
            }
        });
        reviews.push({ id, n: name, ti: title, co: text, rt: rating, dt: date, av: avatar, ro: reviewOf, ph: photos });
    });

    if (reviews.length > 0) {
        console.log("✅ SUCCESS! Copy the JSON block below:");
        console.log(JSON.stringify(reviews));

        // AUTOCLICK LOGIC
        try {
            const currentBtn = document.querySelector('button[disabled][aria-label]');
            if (currentBtn) {
                const currentPage = parseInt(currentBtn.getAttribute('aria-label'));
                const nextPage = currentPage + 1;
                const nextBtn = document.querySelector(`button[aria-label="${nextPage}"]`);
                if (nextBtn) {
                    console.log(`➡️ Page ${currentPage} complete. Clicking Page ${nextPage} in 3 seconds...`);
                    setTimeout(() => {
                        nextBtn.click();
                        console.log("💡 Clicked! Now run the script again.");
                    }, 3000);
                } else {
                    console.log("🏁 Last page reached.");
                }
            }
        } catch (err) {
            console.warn("Could not find next button automatically.");
        }
    } else {
        console.error("❌ No reviews found.");
    }
})();
