const puppeteer = require("puppeteer");
const fs = require('fs');
const path = require('path');

// Configuration
const outputPath = path.join(__dirname, '../../data/source_gmb.json');
const scrollDelay = 2000; // 2 seconds
const maxScrollAttempts = 20;

async function autoScroll(page, containerSelector) {
    let previousHeight = 0;
    let attempts = 0;

    while (attempts < maxScrollAttempts) {
        let currentHeight = await page.evaluate((selector) => {
            const container = document.querySelector(selector);
            if (!container) return 0;
            container.scrollBy(0, 1000);
            return container.scrollHeight;
        }, containerSelector);

        if (currentHeight === previousHeight) break;
        previousHeight = currentHeight;
        attempts++;
        await new Promise(resolve => setTimeout(resolve, scrollDelay));
        console.log(`Scrolled attempt ${attempts}, height: ${currentHeight}`);
    }
}

function parseRelativeTime(relativeTime) {
    const now = new Date();
    const parts = relativeTime.split(' ');
    const amount = parseInt(parts[0]) || 1;
    const unit = parts[1] || 'day';

    let date = new Date(now);
    if (unit.includes('day')) date.setDate(now.getDate() - amount);
    else if (unit.includes('week')) date.setDate(now.getDate() - (amount * 7));
    else if (unit.includes('month')) date.setMonth(now.getMonth() - amount);
    else if (unit.includes('year')) date.setFullYear(now.getFullYear() - amount);
    
    return date.toISOString();
}

async function scrapeGMB() {
    console.log("🚀 Starting Google Maps Scraper...");
    const browser = await puppeteer.launch({
        headless: "new",
        args: ["--no-sandbox", "--disable-setuid-sandbox", "--window-size=1920,1080"],
    });

    const [page] = await browser.pages();
    await page.setViewport({ width: 1920, height: 1080 });
    
    try {
        // Direct Reviews URL
        const url = "https://www.google.com/maps/place/Khao+Lak+Land+Discovery/@8.653424,98.243555,17z/data=!4m8!3m7!1s0x3050c95026210f01:0x39659085526e0e64!8m2!3d8.653424!4d98.24613!9m1!1b1?hl=en-GB";
        await page.goto(url, { waitUntil: "networkidle2" });
        console.log("Page loaded.");

        // Handle Cookie Consent
        try {
            await page.waitForSelector('button[aria-label*="Accept"], button[aria-label*="Agree"]', { timeout: 3000 });
            await page.click('button[aria-label*="Accept"], button[aria-label*="Agree"]');
            console.log("Accepted cookies.");
            await new Promise(resolve => setTimeout(resolve, 2000));
        } catch (e) {}

        try {
            await page.waitForSelector('.wiI7pd', { visible: true, timeout: 10000 });
            console.log("Reviews found.");
        } catch (e) {
            const html = await page.content();
            fs.writeFileSync('debug_source.html', html);
            await page.screenshot({ path: 'debug_error.png' });
            throw new Error("Could not find reviews text (.wiI7pd). HTML saved to debug_source.html, Screenshot saved to debug_error.png");
        }

        
        console.log("Starting scroll...");
        await autoScroll(page, '.m67qEc');

        console.log("Extracting reviews...");
        const reviews = await page.evaluate(() => {
            const items = document.querySelectorAll(".jftiEf, .G66BAb, [data-review-id]");
            return Array.from(items).map((el) => {
                // Click "More" button if exists
                const moreBtn = el.querySelector("button.w8nwRe, button[aria-label*='More'], button.al6Kxe");
                if (moreBtn) moreBtn.click();

                // Selectors Fallbacks
                const author = el.querySelector(".d4r55, .TSr39, .W8S98")?.innerText || "Anonymous";
                const text = el.querySelector(".wiI7pd, .MyEned, .K70oP")?.innerText || "";
                
                // Rating extraction from aria-label
                const ratingEl = el.querySelector(".kvMYJc, .mmu39, [aria-label*='stars']");
                let rating = "5";
                if (ratingEl) {
                    const label = ratingEl.getAttribute("aria-label");
                    const match = label.match(/[0-9]/);
                    if (match) rating = match[0];
                }

                const date_raw = el.querySelector(".rsqaWe, .x399s, .f30vB")?.innerText || "";

                return {
                    id: el.getAttribute("data-review-id") || Math.random().toString(36).substr(2, 9),
                    author,
                    text,
                    rating,
                    date_raw
                };
            });
        });

        // Process dates
        const processedReviews = reviews.map(r => ({
            ...r,
            date: parseRelativeTime(r.date_raw)
        }));

        fs.writeFileSync(outputPath, JSON.stringify(processedReviews, null, 4));
        console.log(`✅ Success! Extracted ${processedReviews.length} reviews to ${outputPath}`);

    } catch (error) {
        console.error("❌ Scraper Error:", error.message);
    } finally {
        await browser.close();
    }
}

scrapeGMB();
