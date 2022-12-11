<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Shopify\AppReviewsService;

class ShopifyAppReviews extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'shopify-app:reviews {--S|slug=} {--P|page=1}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Shopify App reviews 监控';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(public AppReviewsService $service)
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $slug = $this->option('slug');
        $page = (int)$this->option('page');

        if (empty($slug)) {
            $this->error('slug 不能为空');
            return 100;
        }

        if ($page < 1) {
            $this->error('page 不能小于 1');
            return 101;
        }

        $this->info("[" . now()->format('Y-m-d H:i:s') . "]>>> 准备抓取：{$slug} 的第 {$page} 页评论");
        $data = $this->service->getReviewsData($slug, $page);
        $this->service->saveToDB($data);
        $this->info("[" . now()->format('Y-m-d H:i:s') . "]<<< 抓取完成");

        return 0;
    }
}
