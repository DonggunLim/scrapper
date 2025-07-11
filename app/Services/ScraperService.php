<?php

namespace App\Services;

use App\Models\News;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;

class ScraperService
{
   public function scrapeHackerNews(): array
    {
        try {            
            // hacker-news-script 실행
            $scriptPath = base_path('scripts/playwright/hacker-news-scraper.js');
            
            Log::info("Excuting Hacker News scraping [{$scriptPath}]");
            
            // 스크래핑 결과
            $result = Process::run([
                'node',
                $scriptPath
            ]);

            if ($result->failed()) {
                throw new Exception("hacker-news-script failed: " . $result->errorOutput());
            }

            // JSON 결과 파싱
            $articles = json_decode($result->output(), true);
            
            if (!is_array($articles)) {
                throw new Exception("Invalid response from scraper script");
            }

            Log::info("스크랩된 해커뉴스 기사 개수 : " . count($articles) );

            // 데이터베이스에 저장
            return $this->saveArticles($articles);
            
        } catch (Exception $error) {
            Log::error("Scraping failed: " . $error->getMessage());
            throw $error;
        }
    }

    protected function saveArticles(array $articles): array
    {
        $savedCount = 0;
        $duplicateCount = 0;
        $errorCount = 0;

        foreach ($articles as $article) {
            try {
                // 중복 URL 체크
                $existing = News::where('url', $article['url'])->first();
                
                // 중복 기사가 있다면 로그 찍고 continue
                if ($existing) {
                    $duplicateCount++;
                    Log::debug("이미 등록된 {$article['title']} 기사는 추가되지 않습니다.");
                    continue;
                }

                // 새 기사 저장
                News::create([
                    'title' => $article['title'],
                    'url' => $article['url']
                ]);

                $savedCount++;
                Log::debug("{$article['title']} 기사를 저장 하였습니다." );

            } catch (Exception $error) {
                $errorCount++;
                Log::error("기사 저장 실패: " . $error->getMessage());
            }
        }

        $result = [
            'total_scraped' => count($articles),
            'saved' => $savedCount,
            'duplicates' => $duplicateCount,
            'errors' => $errorCount
        ];

        Log::info("해커 뉴스 스크래핑 완료", $result);

        return $result;
    }
}
