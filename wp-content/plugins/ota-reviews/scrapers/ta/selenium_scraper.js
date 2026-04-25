const { Builder, By, until } = require('selenium-webdriver');
const chrome = require('selenium-webdriver/chrome');
const fs = require('fs');
const path = require('path');

// Configuration
const businessUrl = "https://www.tripadvisor.com/Attraction_Review-g297914-d1960808-Reviews-Khao_Lak_Land_Discovery-Khao_Lak_Takua_Pa_Phang_Nga_Province.html";
const chromeBinaryPath = "/home/u451564824/.cache/puppeteer/chrome/linux-147.0.7727.57/chrome-linux64/chrome";

async function runSelenium() {
    console.log("🚀 Starting Selenium Scraper with ChromeDriver...");

    let options = new chrome.Options();
    options.addArguments('--no-sandbox');
    options.addArguments('--disable-dev-shm-usage');
    options.addArguments('--headless=new');
    options.setChromeBinaryPath(chromeBinaryPath);
    // Add realistic User Agent
    options.addArguments('--user-agent=Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36');

    let driver = await new Builder()
        .forBrowser('chrome')
        .setChromeOptions(options)
        .build();

    try {
        console.log("Visiting: " + businessUrl);
        await driver.get(businessUrl);

        // Wait up to 10 seconds for the title to be sure page is loading
        await driver.wait(until.titleContains('Tripadvisor'), 10000);
        console.log("Page title found: " + await driver.getTitle());

        // Check if we hit the DataDome block
        let bodyText = await driver.findElement(By.tagName('body')).getText();
        if (bodyText.includes('captcha') || bodyText.includes('DataDome')) {
            console.error("❌ Blocked: DataDome CAPTCHA detected via Selenium.");
            let html = await driver.getPageSource();
            fs.writeFileSync(path.join(__dirname, 'debug_selenium.html'), html);
            return;
        }

        // Try to find reviews
        try {
            await driver.wait(until.elementLocated(By.css('div[data-automation="reviewCard"]')), 15000);
            let reviews = await driver.findElements(By.css('div[data-automation="reviewCard"]'));
            console.log("✅ Success! Found " + reviews.length + " reviews via Selenium.");
        } catch (e) {
            console.error("❌ Reviews not found: " + e.message);
            let html = await driver.getPageSource();
            fs.writeFileSync(path.join(__dirname, 'debug_selenium.html'), html);
        }

    } catch (error) {
        console.error("❌ Selenium Error: " + error.message);
    } finally {
        await driver.quit();
    }
}

runSelenium();
