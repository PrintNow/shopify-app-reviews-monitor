<?php

namespace App\Services\Shopify;

use App\Models\ShopifyAppReview;
use App\Lib\Shopify\AppReviewsHtml;
use App\Lib\Shopify\AppReviewsParser;

class AppReviewsService
{
    /**
     * @param string $slug
     * @param int|null $page 页码，默认是 1
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Exception
     */
    public function getReviewsData(
        string   $slug,
        int|null $page = null
    ): array
    {
        $page ??= 1;

        $shopifyReviewsHtml = new AppReviewsHtml($slug, $page);

        $shopifyReviewsParser = new AppReviewsParser(
            html: $shopifyReviewsHtml->getHtml(),
            slug: $slug
        );

        return $shopifyReviewsParser->getReviewsData();
    }

    /**
     * 保存到数据库中
     *
     * @param array $data
     * @return void
     */
    public function saveToDB(array $data): void
    {
        foreach ($data as $datum) {
            ShopifyAppReview::updateOrCreate([
                'rid' => $datum['rid'],
            ], $datum);
        }
    }
}
