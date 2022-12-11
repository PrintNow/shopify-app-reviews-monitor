<?php

namespace App\Events\Shopify;

use App\Models\ShopifyAppReview;
use Illuminate\Support\Facades\Log;
use App\Services\Shopify\AppReviewsNotifyService;

class AppReviewsCreatedEvent
{
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(public ShopifyAppReview $shopifyAppReview)
    {
    }

    public function handle()
    {
        if ($this->shopifyAppReview->notify === 1) {
            Log::warning('AppReviewsCreatedEvent:通知过了', ['rid' => $this->shopifyAppReview->rid]);
            return;
        }

        if (config('notify.dingtalk.enabled')) {
            $notifyService = new AppReviewsNotifyService($this->shopifyAppReview);
            $notifyService->handle();
        }

        $this->shopifyAppReview->notify = 1;
        $this->shopifyAppReview->save();
    }
}
