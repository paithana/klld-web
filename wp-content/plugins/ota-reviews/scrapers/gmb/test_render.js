const puppeteer = require("puppeteer-extra");
const StealthPlugin = require('puppeteer-extra-plugin-stealth');
puppeteer.use(StealthPlugin());

async function test() {
    const browser = await puppeteer.launch({
        headless: "new",
        args: ["--no-sandbox", "--disable-setuid-sandbox", "--window-size=1920,1080"]
    });
    const page = await browser.newPage();
    await page.setViewport({ width: 1920, height: 1080 });
    await page.goto("https://www.google.com/maps/place/Khao+Lak+Land+Discovery/@8.653424,98.243555,17z/data=!4m8!3m7!1s0x3050c95026210f01:0x39659085526e0e64!8m2!3d8.653424!4d98.24613!9m1!1b1?hl=en", { waitUntil: "networkidle2" });
    
    // Wait for something to render
    await new Promise(resolve => setTimeout(resolve, 10000));
    
    await page.screenshot({ path: 'test_render.png', fullPage: true });
    const title = await page.title();
    console.log("Title:", title);
    
    await browser.close();
}
test();
