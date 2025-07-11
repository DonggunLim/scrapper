<?php

namespace App\Console\Commands;

use App\Services\ScraperService;
use Exception;
use Illuminate\Console\Command;

class ScraperCommand extends Command
{
    protected $signature = 'scrape:hacker-news';
    protected $description = 'Hacker News에서 기사들 스크래핑';

    public function handle(ScraperService $scraperService)
    {
        $this->info('🔍 Starting Hacker News scraping...');

        try {
        $result = $scraperService->scrapeHackerNews();

        $this->displayResults($result);
        $this->info("✅ Scraping completed successfully!");

        }catch(Exception $error){
            $this->error($error->getMessage());
            return 1;
        }
    }

    protected function displayResults(array $result): void
    {
        $this->info('📊 Results:');
        $this->table(
            ['', 'Count'],
            [
                ['총 스크랩 기사', $result['total_scraped']],
                ['새롭게 추가된 기사', $result['saved']],
                ['중복 기사', $result['duplicates']],
                ['에러', $result['errors']]
            ]
        );
    }
}
