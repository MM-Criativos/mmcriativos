<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VeeActionApproval extends Model
{
    protected $fillable = [
        'approval_id',
        'action_name',
        'tool_name',
        'status',
        'summary',
        'request_payload',
        'meta',
        'approved_by',
        'approved_at',
        'rejected_by',
        'rejected_at',
        'rejection_reason',
        'executed_at',
        'expires_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'request_payload' => 'array',
            'meta' => 'array',
            'approved_at' => 'datetime',
            'rejected_at' => 'datetime',
            'executed_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }
}
