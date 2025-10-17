<?php

/** @noinspection SpellCheckingInspection */

namespace App\Models;

use App\Enums\ResponseStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class DataRequestResponse extends Model
{
    use HasFactory, LogsActivity;

    protected $casts = [
        'status' => ResponseStatus::class,
        'due_at' => 'datetime',
    ];

    protected function casts(): array
    {
        return [
            'status' => ResponseStatus::class,
        ];
    }

    public function dataRequest(): BelongsTo
    {
        return $this->belongsTo(DataRequest::class);
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    public function requestee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requestee_id');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(FileAttachment::class, 'data_request_response_id');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['status', 'due_at', 'requester_id', 'requestee_id', 'data_request_id'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
