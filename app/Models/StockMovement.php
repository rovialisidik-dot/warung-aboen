<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'user_id',
        'type',
        'reference_type',
        'reference_id',
        'quantity',
        'stock_before',
        'stock_after',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'stock_before' => 'integer',
        'stock_after' => 'integer',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function recordMovement(Product $product, int $quantity, string $type, string $referenceType, ?int $referenceId = null, ?string $notes = null): self
    {
        $before = $product->stock;
        if ($type === 'in') {
            $after = $before + $quantity;
        } elseif ($type === 'out') {
            $after = $before - $quantity;
        } else {
            $after = $quantity; // adjustment
            $quantity = abs($after - $before);
        }

        $product->update(['stock' => $after]);

        return self::create([
            'product_id' => $product->id,
            'user_id' => auth()->id(),
            'type' => $type,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'quantity' => $quantity,
            'stock_before' => $before,
            'stock_after' => $after,
            'notes' => $notes,
        ]);
    }
}
