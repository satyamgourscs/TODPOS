<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SubscriptionInvoice extends Model
{
    use HasFactory;

    protected $table = 'subscription_invoices';

    protected $fillable = [
        'business_id',
        'plan_subscribe_id',
        'invoice_count',
        'user_count',
        'storage_used_mb',
        'amount',
        'status',
        'invoice_date',
        'due_date',
        'paid_at',
        'payment_method',
        'transaction_id',
        'notes',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'due_date' => 'date',
        'paid_at' => 'datetime',
    ];

    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function planSubscribe()
    {
        return $this->belongsTo(PlanSubscribe::class);
    }

    public function isPaid()
    {
        return $this->status === 'paid';
    }

    public function isOverdue()
    {
        return $this->status === 'overdue' || (now()->isAfter($this->due_date) && !$this->isPaid());
    }
}
