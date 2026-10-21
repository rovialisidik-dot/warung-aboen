<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CashTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'source',
        'reference_id',
        'amount',
        'balance_after',
        'description',
        'transaction_date',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'balance_after' => 'decimal:2',
        'transaction_date' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function getCurrentBalance(): float
    {
        $last = self::latest('id')->first();
        if ($last) {
            return (float) $last->balance_after;
        }

        $initialSetting = Setting::where('key', 'initial_cash_balance')->value('value');
        return (float) ($initialSetting ?? 0);
    }

    public static function record(string $type, string $source, float $amount, string $description, ?int $referenceId = null, ?int $userId = null): self
    {
        $currentBalance = self::getCurrentBalance();
        $newBalance = ($type === 'in') ? ($currentBalance + $amount) : ($currentBalance - $amount);

        return self::create([
            'user_id' => $userId ?? auth()->id(),
            'type' => $type,
            'source' => $source,
            'reference_id' => $referenceId,
            'amount' => $amount,
            'balance_after' => $newBalance,
            'description' => $description,
            'transaction_date' => now(),
        ]);
    }
}
