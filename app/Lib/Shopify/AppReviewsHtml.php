<?php

namespace App\Lib\Shopify;

use GuzzleHttp\Client;

class AppReviewsHtml
{
    public Client $client;

    /**
     * @param string $slug App Slug，这是唯一的
     * @param int $page 页码
     */
    public function __construct(
        protected string $slug,
        protected int    $page
    )
    {
        $this->client = new Client([
            'timeout' => 8.0,
            'verify' => false,
            'headers' => [
                'user-agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/108.0.0.0 Safari/537.36',
            ],
        ]);
    }

    /**
     * 获取评论页面 HTML
     *
     * @return string
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getHtml(): string
    {
        $response = $this->client->get("https://apps.shopify.com/{$this->slug}/reviews?page={$this->page}", [
            'verify' => false,
        ]);

        return $response->getBody()->getContents();
    }

    public function genReviewsUrl(): string
    {
        return sprintf("https://apps.shopify.com/%s/reviews?page=%d", $this->slug, $this->page);
    }

    /**
     * @return string
     */
    public function getSlug(): string
    {
        return $this->slug;
    }

    /**
     * @return int
     */
    public function getPage(): int
    {
        return $this->page;
    }

    /**
     * @param int $page
     * @return \App\Lib\Shopify\AppReviewsHtml
     */
    public function setPage(int $page): self
    {
        $this->page = $page;
        return $this;
    }
}
