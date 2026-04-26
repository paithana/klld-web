const puppeteer = require("puppeteer-extra");
const StealthPlugin = require('puppeteer-extra-plugin-stealth');
puppeteer.use(StealthPlugin());

const fs = require('fs');
const path = require('path');

// Configuration
const outputPath = path.join(__dirname, '../../data/source_gmb.json');
const scrollDelay = 3000; 
const maxScrollAttempts = 25;

async function autoScroll(page, containerSelector) {
    console.log(`Scrolling container: ${containerSelector}`);
    let previousHeight = 0;
    let attempts = 0;

    while (attempts < maxScrollAttempts) {
        let currentHeight = await page.evaluate((selector) => {
            const container = document.querySelector(selector);
            if (!container) return -1;
            container.scrollBy(0, 1000);
            return container.scrollHeight;
        }, containerSelector);

        if (currentHeight === -1) break;
        if (currentHeight === previousHeight) {
            await new Promise(resolve => setTimeout(resolve, 2000));
            currentHeight = await page.evaluate((selector) => {
                const container = document.querySelector(selector);
                return container ? container.scrollHeight : -1;
            }, containerSelector);
            if (currentHeight === previousHeight) break;
        }

        previousHeight = currentHeight;
        attempts++;
        await new Promise(resolve => setTimeout(resolve, scrollDelay));
        console.log(`Scrolled attempt ${attempts}, current height: ${currentHeight}`);
    }
}

function parseRelativeTime(relativeTime) {
    if (!relativeTime) return new Date().toISOString();
    const now = new Date();
    const parts = relativeTime.split(' ');
    const amount = parseInt(parts[0]) || 1;
    const unit = (parts[1] || 'day').toLowerCase();

    let date = new Date(now);
    if (unit.includes('day') || unit.includes('tag')) date.setDate(now.getDate() - amount);
    else if (unit.includes('week') || unit.includes('woche')) date.setDate(now.getDate() - (amount * 7));
    else if (unit.includes('month') || unit.includes('monat')) date.setMonth(now.getMonth() - amount);
    else if (unit.includes('year') || unit.includes('jahr')) date.setFullYear(now.getFullYear() - amount);
    
    return date.toISOString();
}

async function scrapeGMB() {
    console.log("🚀 Starting ThinkWeb GMB Scraper (Desktop Mode)...");
    const browser = await puppeteer.launch({
        headless: "new", 
        executablePath: "/home/u451564824/.cache/puppeteer/chrome/linux-147.0.7727.57/chrome-linux64/chrome",
        args: [
            "--no-sandbox", 
            "--disable-setuid-sandbox", 
            "--window-size=1920,1080",
            "--lang=en-US"
        ],
    });

    const [page] = await browser.pages();
    await page.setViewport({ width: 1920, height: 1080 });
    await page.setUserAgent('Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36');

    try {
        const url = "https://www.google.com/maps/place/Khao+Lak+Land+Discovery/@8.653424,98.243555,17z/data=!4m8!3m7!1s0x3050c95026210f01:0x39659085526e0e64!8m2!3d8.653424!4d98.24613!9m1!1b1?hl=en";
        console.log(`Visiting: ${url}`);
        
        await page.goto(url, { waitUntil: "networkidle2", timeout: 60000 });
        
        // Handle Consent
        if ((await page.title()).includes("Before you continue")) {
            console.log("Handling consent page...");
            const btns = await page.$$('button');
            for (const btn of btns) {
                const text = await page.evaluate(el => el.innerText, btn);
                if (text.includes("Accept all") || text.includes("Agree")) {
                    await btn.click();
                    await page.waitForNavigation({ waitUntil: "networkidle2" });
                    break;
                }
            }
        }

        console.log("Current Page:", await page.title());

        // Try to click Reviews tab if not active
        try {
            await page.waitForSelector('.jftiEf', { visible: true, timeout: 10000 });
            console.log("Reviews found.");
        } catch (e) {
            console.log("Attempting to find Reviews tab...");
            const tabs = await page.$$('button[role="tab"]');
            for (const tab of tabs) {
                const text = await page.evaluate(el => el.innerText, tab);
                if (text.includes('Reviews') || text.includes('Rezensionen')) {
                    await tab.click();
                    await page.waitForSelector('.jftiEf', { visible: true, timeout: 10000 });
                    break;
                }
            }
        }

        const reviews = await page.evaluate(() => {
            return Array.from(document.querySelectorAll(".jftiEf")).map((el) => {
                const author = el.querySelector(".d4r55")?.innerText || "Anonymous";
                const text = el.querySelector(".wiI7pd")?.innerText || "";
                const ratingEl = el.querySelector(".kvMYJc");
                let rating = "5";
                if (ratingEl) {
                    const label = ratingEl.getAttribute("aria-label");
                    const match = label ? label.match(/[0-9]/) : null;
                    if (match) rating = match[0];
                }
                const date_raw = el.querySelector(".rsqaWe")?.innerText || "";
                return { id: el.getAttribute("data-review-id"), author, text, rating, date_raw };
            });
        });

        if (reviews.length > 0) {
            const processedReviews = reviews.filter(r => r.text.length > 2).map(r => ({
                ...r,
                date: parseRelativeTime(r.date_raw)
            }));
            fs.writeFileSync(outputPath, JSON.stringify(processedReviews, null, 4));
            console.log(`✅ Success! Extracted ${processedReviews.length} reviews.`);
        } else {
            console.log("❌ No reviews found. Google might be blocking automated rendering.");
        }

    } catch (error) {
        console.error("❌ Scraper Error:", error.message);
    } finally {
        await browser.close();
    }
}
scrapeGMB();
