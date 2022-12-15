<?php

namespace App\Services\Shopify;

use App\Lib\Shopify\AppName;
use Guanguans\Notify\Factory;
use App\Models\ShopifyAppReview;

class AppReviewsNotifyService
{
    public function __construct(public ShopifyAppReview $shopifyAppReview)
    {
    }

    public function handle()
    {
        $date = "**📅 Edited at**: {$this->shopifyAppReview->edited_at}";
        if ($this->shopifyAppReview->posted_at !== null) {
            $date = "**📅 Posted at**: {$this->shopifyAppReview->posted_at}";
        }
        $discoverTime = $this->shopifyAppReview->created_at
            ?->setTimezone('Asia/Shanghai')
            ->format('Y-m-d H:i:s') ?? '';

        $appName = AppName::name($this->shopifyAppReview->slug);

        Factory::dingTalk()
            ->setToken(config('notify.dingtalk.token'))
            ->setSecret(config('notify.dingtalk.secret'))
            ->setMessage(new \Guanguans\Notify\Messages\DingTalk\MarkdownMessage([
                'title' => "{$this->shopifyAppReview->rating}-star & {$this->shopifyAppReview->slug} | {$this->shopifyAppReview->name}",
                'text' => <<<MARKDOWN
                            ##  {$appName}
                            ---
                            #### **👓 Customer**: {$this->shopifyAppReview->name}
                            #### **✨ Rating**: {$this->shopifyAppReview->rating}-star
                            #### **🔍 Discover time**: {$discoverTime}
                            ---
                            #### {$date}
                            #### **📝 Comment**: {$this->shopifyAppReview->comment}
                            ---
                            [🧭 Click to view the reviews page](https://apps.shopify.com/{$this->shopifyAppReview->slug})
                          MARKDOWN
                ,
            ]))
            ->send();
    }
}
