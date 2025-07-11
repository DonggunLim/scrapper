import { chromium } from "playwright";

async function scrapeHackerNews() {
    let browser;

    try {
        console.error("Starting Hacker News scraper...");

        // 브라우저 실행
        browser = await chromium.launch({
            headless: true,
        });

        const page = await browser.newPage();

        console.error("Going to Hacker News...");

        // 해커뉴스 페이지 로드
        await page.goto("https://news.ycombinator.com/", {
            waitUntil: "networkidle",
            timeout: 30000,
        });

        console.error("Page loaded, extracting articles...");

        // 기사 수집
        const articles = [];

        // 해커뉴스의 기사 목록 가져오기
        const storyElements = await page.locator(".athing").all();
        console.error(`Found ${storyElements.length} stories on page`);

        for (let i = 0; i < storyElements.length; i++) {
            try {
                const story = storyElements[i];

                // 제목과 링크 추출
                const titleLink = story.locator(".titleline > a").first();

                const title = await titleLink.textContent();
                const url = await titleLink.getAttribute("href");

                if (title && url) {
                    // 상대 URL을 절대 URL로 변환
                    let fullUrl = url;
                    if (!url.startsWith("http")) {
                        fullUrl = `https://news.ycombinator.com/${url}`;
                    }

                    articles.push({
                        title: title.trim(),
                        url: fullUrl,
                    });

                    console.error(`  ✓ ${i + 1}. ${title.substring(0, 60)}...`);
                }
            } catch (error) {
                console.error(
                    `⚠️  Error processing story ${i + 1}:`,
                    error.message
                );
            }
        }

        console.error(`✅ Successfully scraped ${articles.length} articles`);

        // 결과를 JSON으로 출력 (PHP가 이 부분을 받음)
        console.log(JSON.stringify(articles, null, 2));
    } catch (error) {
        console.error("❌ Scraping failed:", error.message);
        process.exit(1);
    } finally {
        if (browser) {
            await browser.close();
        }
    }
}

scrapeHackerNews();
