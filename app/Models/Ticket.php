<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Ticket extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $fillable = [
        'customer_id',
        'assigned_to',
        'subject',
        'content',
        'status',
        'admin_response',
        'responded_at',
    ];

    protected $casts = [
        'responded_at' => 'datetime',
    ];

    // ── Relationships ────────────────────────────────────────────────────────

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    // ── Eloquent scopes ──────────────────────────────────────────────────────

    public function scopeDaily(Builder $query): Builder
    {
        return $query->whereDate('created_at', today());
    }

    public function scopeWeekly(Builder $query): Builder
    {
        return $query->whereBetween('created_at', [
            now()->startOfWeek(),
            now()->endOfWeek(),
        ]);
    }

    public function scopeMonthly(Builder $query): Builder
    {
        return $query->whereMonth('created_at', now()->month)
                     ->whereYear('created_at', now()->year);
    }

    public function scopeOfStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    public function scopeForCustomerEmail(Builder $query, string $email): Builder
    {
        return $query->whereHas('customer', fn (Builder $q) => $q->where('email', $email));
    }

    public function scopeForCustomerPhone(Builder $query, string $phone): Builder
    {
        return $query->whereHas('customer', fn (Builder $q) => $q->where('phone', $phone));
    }

    // ── Media ────────────────────────────────────────────────────────────────

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('attachments');
    }
}
