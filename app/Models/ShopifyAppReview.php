<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * App\Models\ShopifyAppReview
 *
 * @property int $id
 * @property int $rid Reviews ID
 * @property string $slug App Slug
 * @property string $name 评论者名字
 * @property int $rating 评论星级
 * @property string $comment 评论内容
 * @property int $status 评论状态，0：正常
 * @property int $notify 是否通知，0：未通知，1：已通知
 * @property string|null $posted_at 评论发布时间，格式：2022-02-01。没有则为空
 * @property string|null $edited_at 评论修改时间，格式：2022-02-01。没有则为空
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|ShopifyAppReview newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ShopifyAppReview newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ShopifyAppReview query()
 * @method static \Illuminate\Database\Eloquent\Builder|ShopifyAppReview whereComment($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ShopifyAppReview whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ShopifyAppReview whereEditedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ShopifyAppReview whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ShopifyAppReview whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ShopifyAppReview whereNotify($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ShopifyAppReview wherePostedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ShopifyAppReview whereRating($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ShopifyAppReview whereRid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ShopifyAppReview whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ShopifyAppReview whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ShopifyAppReview whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class ShopifyAppReview extends Model
{
    use HasFactory;

    protected $table = 'shopify_app_reviews';
    protected $fillable = ['rid', 'slug', 'name', 'rating', 'comment', 'edited_at', 'posted_at'];
}
