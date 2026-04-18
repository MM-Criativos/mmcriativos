<?php

namespace App\Http\Controllers\Vee;

use App\Http\Controllers\Controller;
use App\Models\VeeMcpCall;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class VeeMcpAuditController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'call_id' => ['required', 'uuid'],
            'tool_name' => ['required', 'string', 'max:160'],
            'status' => ['required', 'string', Rule::in(['success', 'error', 'blocked'])],
            'permission_level' => ['nullable', 'string', Rule::in(['read_only', 'safe_execute', 'approval_required', 'restricted'])],
            'is_write' => ['required', 'boolean'],
            'duration_ms' => ['nullable', 'integer', 'min:0'],
            'request_payload' => ['nullable', 'array'],
            'response_payload' => ['nullable', 'array'],
            'error_message' => ['nullable', 'string'],
            'meta' => ['nullable', 'array'],
        ]);

        $meta = is_array($validated['meta'] ?? null) ? $validated['meta'] : [];
        $meta = array_merge($meta, [
            'source_ip' => $request->ip(),
            'user_agent' => (string) ($request->userAgent() ?? ''),
        ]);
        $validated['meta'] = $meta;

        $call = VeeMcpCall::query()->create($validated);

        return response()->json([
            'ok' => true,
            'id' => $call->id,
        ]);
    }
}
