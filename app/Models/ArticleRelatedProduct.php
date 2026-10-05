<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Baris pivot `article_product`: produk terkait sebuah artikel beserta urutan tampilnya.
 */
class ArticleRelatedProduct extends Model
{
    protected $table = 'article_product';

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'article_id',
        'product_id',
        'sort_order',
    ];

    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
