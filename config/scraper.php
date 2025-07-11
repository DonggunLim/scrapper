<?php
return [
    'enabled' => env('SCRAPER_ENABLED', false),
    'max_articles' => env('SCRAPER_MAX_ARTICLES', 50),
    'timeout' => env('SCRAPER_TIMEOUT', 30000),
    
    'sites' => [
        'hackernews' => [
            'url' => 'https://news.ycombinator.com/',
            'selectors' => [
                'article' => '.athing',
                'title' => '.titleline > a',
                'link' => '.titleline > a'
            ]
        ]
    ]
];