const puppeteer = require('puppeteer-extra');
const StealthPlugin = require('puppeteer-extra-plugin-stealth');
puppeteer.use(StealthPlugin());

const url = process.argv[2];

if (!url) {
    console.error("Usage: node thinkweb_scraper.js <url>");
    process.exit(1);
}

const chromePath = "/home/u451564824/.cache/puppeteer/chrome/linux-147.0.7727.57/chrome-linux64/chrome";

(async () => {
    const browser = await puppeteer.launch({
        headless: "new",
        executablePath: chromePath,
        args: ["--no-sandbox", "--disable-setuid-sandbox"]
    });

    try {
        const [page] = await browser.pages();
        await page.setUserAgent("Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36");
        
        await page.goto(url, { waitUntil: 'networkidle2', timeout: 60000 });
        
        // Output the raw HTML to stdout for PHP to read
        const html = await page.content();
        console.log(html);
        
    } catch (err) {
        console.error("Scrape Error: " + err.message);
        process.exit(1);
    } finally {
        await browser.close();
    }
})();
