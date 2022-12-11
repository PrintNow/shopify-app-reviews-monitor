<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateShopifyAppReviewsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shopify_app_reviews', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('rid')->comment('Reviews ID');
            $table->string('slug', 200)->comment('App Slug');
            $table->string('name')->comment('评论者名字');
            $table->smallInteger('rating')->comment('评论星级')
                ->default(0);
            $table->text('comment')->comment('评论内容');
            $table->integer('status')->default(0)
                ->comment('评论状态，0：正常');

            // 钉钉机器人提醒
            $table->smallInteger('notify')->default(0)
                ->comment('是否通知，0：未通知，1：已通知');

            $table->date('posted_at')->comment('评论发布时间，格式：2022-02-01。没有则为空')
                ->nullable();
            $table->date('edited_at')->comment('评论修改时间，格式：2022-02-01。没有则为空')
                ->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('shopify_app_reviews');
    }
}
