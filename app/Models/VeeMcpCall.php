<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VeeMcpCall extends Model
{
    protected $fillable = [
        'call_id',
        'tool_name',
        'status',
        'permission_level',
        'is_write',
        'duration_ms',
        'request_payload',
        'response_payload',
        'error_message',
        'meta',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_write' => 'boolean',
            'duration_ms' => 'integer',
            'request_payload' => 'array',
            'response_payload' => 'array',
            'meta' => 'array',
        ];
    }
}
