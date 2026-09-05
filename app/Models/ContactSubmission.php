<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactSubmission extends Model
{
    use HasFactory;

    public const STATUS_NEW = 'new';

    public const STATUS_CONTACTED = 'contacted';

    public const STATUS_CLOSED = 'closed';

    /**
     * @var array<int, string>
     */
    public const STATUSES = [
        self::STATUS_NEW,
        self::STATUS_CONTACTED,
        self::STATUS_CLOSED,
    ];

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'phone',
        'topic',
        'message',
        'status',
    ];
}
