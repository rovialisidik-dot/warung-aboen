<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Purchase extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_number',
        'user_id',
        'supplier_name',
        'total_amount',
        'payment_method',
        'status',
        'notes',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public static function generatePurchaseNumber(): string
    {
        $prefix = 'PO-' . date('Ymd') . '-';
        $latest = self::where('purchase_number', 'LIKE', $prefix . '%')->latest('id')->first();
        if ($latest) {
            $num = (int) substr($latest->purchase_number, -4) + 1;
        } else {
            $num = 1;
        }
        return $prefix . str_pad($num, 4, '0', STR_PAD_LEFT);
    }
}
