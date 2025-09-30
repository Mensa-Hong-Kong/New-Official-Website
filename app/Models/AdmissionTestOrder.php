<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class AdmissionTestOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'product_name',
        'price_name',
        'price',
        'quota',
        'status',
        'expired_at',
        'gatewayable_type',
        'gatewayable_id',
        'reference_number',
    ];

    protected $casts = [
        'expired_at' => 'datetime',
    ];

    public function gatewayable()
    {
        return $this->morphTo();
    }

    public function tests()
    {
        return $this->belongsToMany(AdmissionTest::class, AdmissionTestHasCandidate::class, 'order_id', 'test_id');
    }

    public function attendedTests()
    {
        return $this->tests()->where('is_present', true);
    }

    public function scopeWhereHasUnusedQuota()
    {
        $thisTable = $this->getTable();
        $return = $this->where('status', 'succeeded')
            ->whereHas(
                'attendedTests', null, '<=',
                DB::raw("$thisTable.quota")
            );
        $quotaValidityMonths = config('app.admissionTestQuotaValidityMonths');
        if ($quotaValidityMonths) {
            $return->leftJoinRelation('attendedTests as attendedTests.type as type')
                ->where(
                    DB::raw("
                        if(
                            attendedTests.testing_at IS NOT NULL,
                            DATE_ADD(
                                attendedTests.testing_at,
                                INTERVAL type.interval_month + $quotaValidityMonths MONTH
                            ),
                            DATE_ADD(
                                $thisTable.created_at,
                                INTERVAL $quotaValidityMonths MONTH
                            )
                        )
                    "), '>=', now()
                );
        }

        return $return;
    }
}
