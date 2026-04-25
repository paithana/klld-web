const puppeteer = require("puppeteer");
const fs = require('fs');
const path = require('path');

// Configuration
const outputPath = path.join(__dirname, '../../data/manual_import.json');
const businessUrl = "https://www.tripadvisor.com/Attraction_Review-g297914-d1960808-Reviews-Khao_Lak_Land_Discovery-Khao_Lak_Takua_Pa_Phang_Nga_Province.html";
const chromePath = "/home/u451564824/.cache/puppeteer/chrome/linux-147.0.7727.57/chrome-linux64/chrome";

async function scrapeTA() {
    console.log("🚀 Starting Headless TripAdvisor Scraper...");
    
    const browser = await puppeteer.launch({
        headless: "new",
        executablePath: chromePath,
        args: [
            "--no-sandbox", 
            "--disable-setuid-sandbox", 
            "--disable-blink-features=AutomationControlled",
            "--window-size=1920,1080"
        ],
    });

    const [page] = await browser.pages();
    await page.setUserAgent("Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36");
    await page.setViewport({ width: 1920, height: 1080 });
    
    // Hide webdriver
    await page.evaluateOnNewDocument(() => {
        Object.defineProperty(navigator, 'webdriver', { get: () => false });
    });

    try {
        console.log("Visiting: " + businessUrl);
        await page.goto(businessUrl, { waitUntil: "networkidle2", timeout: 60000 });

        // Save a screenshot for debugging
        await page.screenshot({ path: path.join(__dirname, 'debug_screenshot.png') });
        console.log("Screenshot saved.");

        // Wait for body first
        await page.waitForSelector('body');

        console.log("Waiting for reviews to load...");
        await page.waitForSelector('div[data-automation="reviewCard"]', { timeout: 15000 });

        const reviews = await page.evaluate(() => {
            const cards = document.querySelectorAll('div[data-automation="reviewCard"]');
            return Array.from(cards).map(card => {
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

                return { id, n: name, ti: title, co: text, rt: rating, dt: date, av: avatar, ro: reviewOf, ph: photos };
            });
        });

        console.log("✅ Extracted " + reviews.length + " reviews.");
        fs.writeFileSync(outputPath, JSON.stringify(reviews, null, 4));
        console.log("💾 Saved to " + outputPath);

    } catch (error) {
        console.error("❌ Headless Scraper Error: " + error.message);
        const html = await page.content();
        fs.writeFileSync(path.join(__dirname, 'debug_ta.html'), html);
        console.log("Debug HTML saved to scrapers/ta/debug_ta.html");
    } finally {
        await browser.close();
    }
}

scrapeTA();
