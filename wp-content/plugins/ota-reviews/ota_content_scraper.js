/**
 * OTA Content & Itinerary Scraper (Browser Console)
 * Supports: GetYourGuide, Viator, TripAdvisor
 * 
 * Instructions:
 * 1. Open a Tour/Activity page on GYG, Viator, or TA.
 * 2. Open Console (F12) and paste this script.
 * 3. It will extract descriptions, highlights, and the full itinerary.
 * 4. Data is sent to ota-content.php for automatic WordPress import.
 */

(async function() {
    console.log("🛠 Starting OTA Content Scraper...");

    const url = window.location.href;
    let source = "";
    if (url.includes("getyourguide.com")) source = "gyg";
    else if (url.includes("viator.com")) source = "viator";
    else if (url.includes("tripadvisor.com")) source = "tripadvisor";

    if (!source) {
        console.error("❌ Unsupported URL source.");
        return;
    }

    const data = {
        title: document.title.split('|')[0].trim(),
        description: "",
        highlights: [],
        inclusions: [],
        exclusions: [],
        itinerary: [],
        images: [],
        source: source,
        source_url: url
    };

    if (source === "gyg") {
        // --- GetYourGuide Extraction ---
        data.description = document.querySelector('.activity-description__content')?.innerText || "";
        
        document.querySelectorAll('.activity-highlights__list-item').forEach(item => {
            data.highlights.push(item.innerText.trim());
        });

        document.querySelectorAll('.activity-inclusions__item--included').forEach(item => {
            data.inclusions.push(item.innerText.trim());
        });
        document.querySelectorAll('.activity-inclusions__item--not-included').forEach(item => {
            data.exclusions.push(item.innerText.trim());
        });

        // Itinerary
        document.querySelectorAll('.itinerary__item').forEach(item => {
            const title = item.querySelector('.itinerary__item-title')?.innerText;
            const desc = item.querySelector('.itinerary__item-description')?.innerText;
            if (title) data.itinerary.push({ title, desc });
        });

        // Images
        document.querySelectorAll('.view-photos__gallery-image img').forEach(img => {
            if (img.src) data.images.push(img.src);
        });

    } else if (source === "viator") {
        // --- Viator Extraction ---
        data.description = document.querySelector('[data-automation="product-description"]')?.innerText || "";
        
        document.querySelectorAll('.highlight-item').forEach(item => {
            data.highlights.push(item.innerText.trim());
        });

        document.querySelectorAll('[data-automation="inclusion-item"]').forEach(item => {
            data.inclusions.push(item.innerText.trim());
        });
        document.querySelectorAll('[data-automation="exclusion-item"]').forEach(item => {
            data.exclusions.push(item.innerText.trim());
        });

        // Itinerary
        document.querySelectorAll('.itinerary-item').forEach(item => {
            const title = item.querySelector('.itinerary-step-title')?.innerText;
            const desc = item.querySelector('.itinerary-step-description')?.innerText;
            if (title) data.itinerary.push({ title, desc });
        });
    } else if (source === "tripadvisor") {
        // --- TripAdvisor Extraction ---
        data.description = document.querySelector('div[data-automation="WebPresentation_AboutThisActivity"]')?.innerText || 
                           document.querySelector('.WlY7H')?.innerText || "";
        
        document.querySelectorAll('div[data-automation="WebPresentation_InclusionsExclusions"] li').forEach(item => {
            data.inclusions.push(item.innerText.trim());
        });

        // Itinerary
        document.querySelectorAll('div[data-automation="WebPresentation_ItineraryItem"]').forEach(item => {
            const title = item.querySelector('h3')?.innerText || item.querySelector('.biGQs')?.innerText;
            const desc = item.querySelector('.biGQs')?.innerText;
            if (title) data.itinerary.push({ title, desc });
        });

        // Images
        document.querySelectorAll('img').forEach(img => {
            if (img.src && img.src.includes('/media/photo-') && !img.src.includes('avatar')) {
                data.images.push(img.src);
            }
        });
    }

    console.log("📦 Data Extracted:", data);

    // Send to Server
    const endpoint = "https://khaolaklanddiscovery.com/wp-content/plugins/ota-reviews/ota-content.php?action=import_json";
    
    try {
        const response = await fetch(endpoint, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        const result = await response.json();
        console.log("🎉 Server Response:", result);
        if (result.success) {
            console.log("✅ Content imported successfully! Refresh your WordPress editor.");
        }
    } catch (err) {
        console.error("❌ Failed to send to server:", err);
        console.log("💡 You can manually copy this JSON and process it:", JSON.stringify(data));
    }
})();
