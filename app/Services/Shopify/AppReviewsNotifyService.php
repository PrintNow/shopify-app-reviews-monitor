<?php

namespace App\Services\Shopify;

use Guanguans\Notify\Factory;
use App\Models\ShopifyAppReview;

class AppReviewsNotifyService
{
    public function __construct(public ShopifyAppReview $shopifyAppReview)
    {
    }

    public function handle()
    {
        $date = "**Edited at**: {$this->shopifyAppReview->edited_at}";
        if ($this->shopifyAppReview->posted_at !== null) {
            $date = "**Posted at**: {$this->shopifyAppReview->posted_at}";
        }
        $discoverTime = $this->shopifyAppReview->created_at
            ?->setTimezone('Asia/Shanghai')
            ->format('Y-m-d H:i:s') ?? '';

        Factory::dingTalk()
            ->setToken(config('notify.dingtalk.token'))
            ->setSecret(config('notify.dingtalk.secret'))
            ->setMessage(new \Guanguans\Notify\Messages\DingTalk\MarkdownMessage([
                'title' => "{$this->shopifyAppReview->rating}-star & {$this->shopifyAppReview->slug} | {$this->shopifyAppReview->name}",
                'text' => <<<MARKDOWN
                            ## {$this->shopifyAppReview->slug}
                            ---
                            #### **Customer**: {$this->shopifyAppReview->name}
                            #### **Reviews rating**: {$this->shopifyAppReview->rating}-star
                            #### {$date}
                            ---
                            #### **Comment**: {$this->shopifyAppReview->comment}
                            #### **Discover time**: {$discoverTime}

                            ---

                            [Click to view the reviews page](https://apps.shopify.com/{$this->shopifyAppReview->slug})
                          MARKDOWN
                ,
            ]))
            ->send();
    }
}
