<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Lead dari form "Hitung Estimasi Penghematan" di home. Input mentah
 * (tagihan/peralatan) dan hasil hitungan server disimpan terpisah agar bisa
 * difilter & dilaporkan. `assumptions` adalah snapshot asumsi tarif/coverage
 * yang berlaku saat lead ini dihitung — dibiarkan berbeda dari lead lain
 * yang dihitung sebelum/sesudah admin mengubah CalculatorSettings.
 */
class CalculatorLead extends Model
{
    use HasFactory;

    public const CATEGORY_RESIDENTIAL = 'residential';

    public const CATEGORY_INDUSTRIAL = 'industrial';

    public const METHOD_BILL = 'bill';

    public const METHOD_APPLIANCE = 'appliance';

    public const STATUS_NEW = 'new';

    public const STATUS_CONTACTED = 'contacted';

    public const STATUS_QUALIFIED = 'qualified';

    public const STATUS_WON = 'won';

    public const STATUS_LOST = 'lost';

    /**
     * @var array<int, string>
     */
    public const STATUSES = [
        self::STATUS_NEW,
        self::STATUS_CONTACTED,
        self::STATUS_QUALIFIED,
        self::STATUS_WON,
        self::STATUS_LOST,
    ];

    protected $fillable = [
        'name',
        'phone',
        'email',
        'area',
        'category',
        'method',
        'monthly_bill',
        'va_capacity',
        'appliances',
        'total_watt',
        'estimated_monthly_bill',
        'savings_year1',
        'total_savings_25y',
        'estimated_investment',
        'breakeven_years',
        'annual_kwh',
        'assumptions',
        'status',
        'follow_up_notes',
        'followed_up_by_id',
        'followed_up_at',
        'ip_address',
        'user_agent',
        'referrer',
        'utm',
    ];

    protected $casts = [
        'appliances' => AsArrayObject::class,
        'assumptions' => AsArrayObject::class,
        'utm' => AsArrayObject::class,
        'monthly_bill' => 'integer',
        'total_watt' => 'integer',
        'estimated_monthly_bill' => 'integer',
        'savings_year1' => 'integer',
        'total_savings_25y' => 'integer',
        'estimated_investment' => 'integer',
        'breakeven_years' => 'decimal:1',
        'annual_kwh' => 'decimal:1',
        'followed_up_at' => 'datetime',
    ];

    public function followedUpBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'followed_up_by_id');
    }

    public function isFollowedUp(): bool
    {
        return $this->status !== self::STATUS_NEW;
    }
}
