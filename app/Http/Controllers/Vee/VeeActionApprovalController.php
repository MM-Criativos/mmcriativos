<?php

namespace App\Http\Controllers\Vee;

use App\Http\Controllers\Controller;
use App\Models\VeeActionApproval;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class VeeActionApprovalController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'status' => ['nullable', Rule::in(['pending', 'approved', 'rejected', 'executed'])],
            'limit' => ['nullable', 'integer', 'min:1', 'max:200'],
            'action_name' => ['nullable', 'string', 'max:120'],
            'tool_name' => ['nullable', 'string', 'max:160'],
        ]);

        $limit = (int) ($validated['limit'] ?? 50);
        $query = VeeActionApproval::query()
            ->orderByDesc('created_at')
            ->limit($limit);

        if (! empty($validated['status'])) {
            $query->where('status', $validated['status']);
        }
        if (! empty($validated['action_name'])) {
            $query->where('action_name', 'like', '%' . $validated['action_name'] . '%');
        }
        if (! empty($validated['tool_name'])) {
            $query->where('tool_name', 'like', '%' . $validated['tool_name'] . '%');
        }

        $approvals = $query->get();

        return response()->json([
            'ok' => true,
            'total' => $approvals->count(),
            'approvals' => $approvals,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'action_name' => ['required', 'string', 'max:120'],
            'tool_name' => ['required', 'string', 'max:160'],
            'summary' => ['nullable', 'string'],
            'request_payload' => ['nullable', 'array'],
            'meta' => ['nullable', 'array'],
            'expires_at' => ['nullable', 'date'],
        ]);

        $approval = VeeActionApproval::query()->create([
            'approval_id' => (string) Str::uuid(),
            'action_name' => $validated['action_name'],
            'tool_name' => $validated['tool_name'],
            'status' => 'pending',
            'summary' => $validated['summary'] ?? null,
            'request_payload' => $validated['request_payload'] ?? null,
            'meta' => $validated['meta'] ?? null,
            'expires_at' => $validated['expires_at'] ?? null,
        ]);

        return response()->json([
            'ok' => true,
            'approval_id' => $approval->approval_id,
            'status' => $approval->status,
        ]);
    }

    public function show(string $approvalId): JsonResponse
    {
        $approval = VeeActionApproval::query()
            ->where('approval_id', $approvalId)
            ->first();

        if (! $approval) {
            return response()->json([
                'ok' => false,
                'message' => 'Approval not found.',
            ], 404);
        }

        return response()->json([
            'ok' => true,
            'approval' => $approval,
        ]);
    }

    public function approve(Request $request, string $approvalId): JsonResponse
    {
        $validated = $request->validate([
            'approved_by' => ['nullable', 'string', 'max:120'],
            'meta' => ['nullable', 'array'],
        ]);

        $approval = VeeActionApproval::query()
            ->where('approval_id', $approvalId)
            ->first();

        if (! $approval) {
            return response()->json([
                'ok' => false,
                'message' => 'Approval not found.',
            ], 404);
        }

        if ($approval->status !== 'pending') {
            return response()->json([
                'ok' => false,
                'message' => "Approval is in status '{$approval->status}'.",
            ], 409);
        }

        $meta = is_array($approval->meta) ? $approval->meta : [];
        if (is_array($validated['meta'] ?? null)) {
            $meta = array_merge($meta, $validated['meta']);
        }

        $approval->fill([
            'status' => 'approved',
            'approved_by' => $validated['approved_by'] ?? 'manual',
            'approved_at' => now(),
            'meta' => $meta,
        ])->save();

        return response()->json([
            'ok' => true,
            'approval_id' => $approval->approval_id,
            'status' => $approval->status,
        ]);
    }

    public function reject(Request $request, string $approvalId): JsonResponse
    {
        $validated = $request->validate([
            'rejected_by' => ['nullable', 'string', 'max:120'],
            'reason' => ['nullable', 'string'],
        ]);

        $approval = VeeActionApproval::query()
            ->where('approval_id', $approvalId)
            ->first();

        if (! $approval) {
            return response()->json([
                'ok' => false,
                'message' => 'Approval not found.',
            ], 404);
        }

        if ($approval->status !== 'pending') {
            return response()->json([
                'ok' => false,
                'message' => "Approval is in status '{$approval->status}'.",
            ], 409);
        }

        $approval->fill([
            'status' => 'rejected',
            'rejected_by' => $validated['rejected_by'] ?? 'manual',
            'rejected_at' => now(),
            'rejection_reason' => $validated['reason'] ?? null,
        ])->save();

        return response()->json([
            'ok' => true,
            'approval_id' => $approval->approval_id,
            'status' => $approval->status,
        ]);
    }

    public function markExecuted(Request $request, string $approvalId): JsonResponse
    {
        $validated = $request->validate([
            'meta' => ['nullable', 'array'],
            'status' => ['nullable', Rule::in(['executed'])],
        ]);

        $approval = VeeActionApproval::query()
            ->where('approval_id', $approvalId)
            ->first();

        if (! $approval) {
            return response()->json([
                'ok' => false,
                'message' => 'Approval not found.',
            ], 404);
        }

        if ($approval->status === 'executed') {
            return response()->json([
                'ok' => true,
                'approval_id' => $approval->approval_id,
                'status' => $approval->status,
            ]);
        }

        if ($approval->status !== 'approved') {
            return response()->json([
                'ok' => false,
                'message' => "Approval must be approved before execution. Current status: '{$approval->status}'.",
            ], 409);
        }

        $meta = is_array($approval->meta) ? $approval->meta : [];
        if (is_array($validated['meta'] ?? null)) {
            $meta = array_merge($meta, $validated['meta']);
        }

        $approval->fill([
            'status' => 'executed',
            'executed_at' => now(),
            'meta' => $meta,
        ])->save();

        return response()->json([
            'ok' => true,
            'approval_id' => $approval->approval_id,
            'status' => $approval->status,
        ]);
    }
}
