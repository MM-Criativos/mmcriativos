<?php

use App\Http\Controllers\Vee\VeeActionApprovalController;
use App\Http\Controllers\Vee\VeeMcpAuditController;
use Illuminate\Support\Facades\Route;

Route::prefix('internal/vee')->middleware('vee.internal')->group(function () {
    Route::post('mcp/calls', [VeeMcpAuditController::class, 'store'])->name('vee.mcp.calls.store');

    Route::prefix('mcp/approvals')->name('vee.mcp.approvals.')->group(function () {
        Route::get('/', [VeeActionApprovalController::class, 'index'])->name('index');
        Route::post('/', [VeeActionApprovalController::class, 'store'])->name('store');
        Route::get('{approvalId}', [VeeActionApprovalController::class, 'show'])->name('show');
        Route::post('{approvalId}/approve', [VeeActionApprovalController::class, 'approve'])->name('approve');
        Route::post('{approvalId}/reject', [VeeActionApprovalController::class, 'reject'])->name('reject');
        Route::post('{approvalId}/executed', [VeeActionApprovalController::class, 'markExecuted'])->name('executed');
    });
});
