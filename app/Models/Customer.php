<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'phone', 'address', 'type', 'deposit', 'balance',
        'member_since', 'member_expiry',
    ];

    protected $casts = [
        'deposit'       => 'decimal:2',
        'balance'       => 'decimal:2',
        'member_since'  => 'date',
        'member_expiry' => 'date',
    ];

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    // Helper methods
    public function isMember(): bool
    {
        return $this->type === 'member' &&
        ! is_null($this->member_expiry) &&
        $this->member_expiry->isFuture();
    }

    public function getMemberStatus()
    {
        if (! $this->isMember()) {
            return 'Non-Member';
        }

        return 'Member - Exp: ' . $this->member_expiry->format('d/m/Y');
    }

    public function canUseDeposit($amount)
    {
        return $this->isMember() && $this->balance >= $amount;
    }

    public function deductBalance($amount)
    {
        if ($this->canUseDeposit($amount)) {
            $this->balance -= $amount;
            return $this->save();
        }
        return false;
    }
}
