const puppeteer = require('puppeteer-extra');
const StealthPlugin = require('puppeteer-extra-plugin-stealth');
puppeteer.use(StealthPlugin());

const fs = require('fs');
const path = require('path');

// Configuration
const outputPath = path.join(__dirname, '../../data/source_gmb.json');
const businessUrl = "https://www.google.com/maps/place/Khao+Lak+Land+Discovery/@8.653424,98.243555,17z/data=!4m8!3m7!1s0x3050c95026210f01:0x39659085526e0e64!8m2!3d8.653424!4d98.24613!9m1!1b1?hl=en-GB";
const chromePath = "/home/u451564824/.cache/puppeteer/chrome/linux-147.0.7727.57/chrome-linux64/chrome";

async function scrapeGMBStealth() {
    console.log("🚀 Starting ThinkWeb GMB Scraper (High Stealth)...");
    
    const browser = await puppeteer.launch({
        headless: "new",
        executablePath: chromePath,
        args: [
            "--no-sandbox", 
            "--disable-setuid-sandbox",
            "--disable-blink-features=AutomationControlled",
            "--window-size=1920,1080"
        ]
    });

    const [page] = await browser.pages();
    
    try {
        await page.setViewport({ width: 1920, height: 1080 });
        await page.setUserAgent("Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36");

        console.log("Visiting: " + businessUrl);
        await page.goto(businessUrl, { waitUntil: 'networkidle2', timeout: 60000 });

        // Wait a bit
        await new Promise(resolve => setTimeout(resolve, 5000));

        // Handle Cookie Consent
        try {
            const acceptBtn = await page.waitForSelector('button[aria-label*="Accept"], button[aria-label*="Agree"], button[aria-label*="Alle akzeptieren"]', { timeout: 10000 });
            if (acceptBtn) {
                await acceptBtn.click();
                console.log("Accepted cookies.");
                await new Promise(resolve => setTimeout(resolve, 5000));
            }
        } catch (e) {
            console.log("Cookie button not found or already accepted.");
        }

        await page.screenshot({ path: path.join(__dirname, 'debug_gmb_stealth_v2.png') });

        console.log("Looking for reviews pane...");
        // Google Maps often uses these classes for the review list
        const reviewListSelector = '.m67qEc, .wiI7pd, div[role="main"]';
        try {
            await page.waitForSelector(reviewListSelector, { visible: true, timeout: 20000 });
            console.log("Found review container.");
        } catch (e) {
            console.warn("Review container not found, checking body text...");
            const bodyText = await page.evaluate(() => document.body.innerText);
            if (bodyText.includes('CAPTCHA') || bodyText.includes('unusual traffic')) {
                throw new Error("Google blocked us with CAPTCHA/Unusual Traffic.");
            }
        }

        const html = await page.content();
        fs.writeFileSync(path.join(__dirname, 'debug_gmb_stealth_v2.html'), html);
        console.log("Debug HTML saved.");

        const reviews = await page.evaluate(() => {
            const items = document.querySelectorAll(".jftiEf");
            return Array.from(items).map((el) => {
                const author = el.querySelector(".d4r55")?.innerText || "Anonymous";
                const text = el.querySelector(".wiI7pd")?.innerText || "";
                const ratingEl = el.querySelector(".kvMYJc");
                let rating = "5";
                if (ratingEl) {
                    const label = ratingEl.getAttribute("aria-label");
                    const match = label.match(/[0-9]/);
                    if (match) rating = match[0];
                }
                return { author, text, rating };
            });
        });

        if (reviews.length > 0) {
            console.log("✅ Extracted " + reviews.length + " reviews.");
            fs.writeFileSync(outputPath, JSON.stringify(reviews, null, 4));
        } else {
            console.log("⚠️ No reviews extracted from the current view.");
        }

    } catch (err) {
        console.error("Scrape Error: " + err.message);
    } finally {
        await browser.close();
    }
}

scrapeGMBStealth();
