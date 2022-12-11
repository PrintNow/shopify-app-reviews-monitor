<?php

namespace App\Observers\Shopify;

use App\Models\ShopifyAppReview;
use App\Events\Shopify\AppReviewsCreatedEvent;

class AppReviewObserver
{
    /**
     * Handle the ShopifyAppReview "created" event.
     *
     * @param \App\Models\ShopifyAppReview $shopifyAppReview
     * @return void
     */
    public function created(ShopifyAppReview $shopifyAppReview)
    {
        $appReviewsCreatedEvent = new AppReviewsCreatedEvent($shopifyAppReview);
        $appReviewsCreatedEvent->handle();
    }

    /**
     * Handle the ShopifyAppReview "updated" event.
     *
     * @param \App\Models\ShopifyAppReview $shopifyAppReview
     * @return void
     */
    public function updated(ShopifyAppReview $shopifyAppReview)
    {
    }

    /**
     * Handle the ShopifyAppReview "deleted" event.
     *
     * @param \App\Models\ShopifyAppReview $shopifyAppReview
     * @return void
     */
    public function deleted(ShopifyAppReview $shopifyAppReview)
    {
        //
    }

    /**
     * Handle the ShopifyAppReview "restored" event.
     *
     * @param \App\Models\ShopifyAppReview $shopifyAppReview
     * @return void
     */
    public function restored(ShopifyAppReview $shopifyAppReview)
    {
        //
    }

    /**
     * Handle the ShopifyAppReview "force deleted" event.
     *
     * @param \App\Models\ShopifyAppReview $shopifyAppReview
     * @return void
     */
    public function forceDeleted(ShopifyAppReview $shopifyAppReview)
    {
        //
    }
}
