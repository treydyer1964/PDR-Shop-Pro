<?php

namespace App\Services;

use App\Models\ExpenseCategory;
use App\Models\RentalReimbursement;
use App\Models\WorkOrder;
use App\Models\WorkOrderExpense;
use App\Models\WorkOrderRental;
use App\Models\WorkOrderCommission;

class RentalService
{
    /**
     * Sync the rental expense line item on a work order.
     * Called whenever segments are added/changed/removed.
     * Creates or updates the single "Rental" expense row.
     */
    public function syncExpense(WorkOrderRental $rental): void
    {
        $workOrder = $rental->workOrder;
        $rental->load('segments', 'vehicle');

        $totalCost = $rental->totalInternalCost();

        $category = ExpenseCategory::where('tenant_id', $workOrder->tenant_id)
            ->where('slug', ExpenseCategory::SLUG_RENTAL)
            ->first();

        if (! $category) {
            return;
        }

        $existing = WorkOrderExpense::where('work_order_id', $workOrder->id)
            ->where('expense_category_id', $category->id)
            ->first();

        if ($totalCost > 0) {
            $days = $rental->totalDays();
            $dailyRate = $rental->vehicle?->internal_daily_cost ?? 0;
            $hasOpen = $rental->segments->whereNull('end_date')->isNotEmpty();
            $note = "{$days} day(s) @ \${$dailyRate}/day" . ($hasOpen ? ' (running)' : '');

            if ($existing) {
                $existing->update(['amount' => $totalCost, 'notes' => $note]);
            } else {
                WorkOrderExpense::create([
                    'tenant_id'           => $workOrder->tenant_id,
                    'work_order_id'       => $workOrder->id,
                    'expense_category_id' => $category->id,
                    'amount'              => $totalCost,
                    'notes'               => $note,
                    'created_by'          => auth()->id(),
                ]);
            }
        } else {
            // No days yet — remove the expense placeholder
            $existing?->delete();
        }
    }

    /**
     * Remove the rental expense when a rental is deleted.
     */
    public function removeExpense(WorkOrder $workOrder): void
    {
        $category = ExpenseCategory::where('tenant_id', $workOrder->tenant_id)
            ->where('slug', ExpenseCategory::SLUG_RENTAL)
            ->first();

        if ($category) {
            WorkOrderExpense::where('work_order_id', $workOrder->id)
                ->where('expense_category_id', $category->id)
                ->delete();
        }
    }

    /**
     * Calculate how much each commission earner should be reimbursed
     * when insurance pays back the rental cost.
     *
     * Logic: each earner was docked (rental expense / net) × their commission.
     * We distribute the insurance payment proportionally to how much each
     * person was impacted, capped at their actual rental impact.
     *
     * Returns array: [ user_id => reimbursement_amount, ... ]
     */
    public function calculateReimbursements(WorkOrderRental $rental): array
    {
        $workOrder  = $rental->workOrder()->with('commissions', 'expenses')->first();
        $rental->load('segments', 'vehicle');

        $rentalCost = $rental->totalInternalCost();
        $insurancePaid = (float) ($rental->reimbursement?->insurance_amount_received ?? 0);

        if ($rentalCost <= 0 || $insurancePaid <= 0) {
            return [];
        }

        $net = (float) $workOrder->netTotal();
        if ($net <= 0) {
            return [];
        }

        // Each commission earner's rental "deduction impact":
        // their commission was calculated on (net - rental), so they lost
        // rentalCost × (their_commission / net_before_rental_deduction)
        // Approximation: proportion = their_commission / sum_of_all_commissions
        $commissions = $workOrder->commissions()->with('user')->get();
        $totalCommission = $commissions->sum('amount');

        if ($totalCommission <= 0) {
            return [];
        }

        $reimbursements = [];
        $totalAllocated = 0;

        foreach ($commissions as $commission) {
            // Their proportional share of the rental cost impact
            $proportion = (float) $commission->amount / $totalCommission;
            $rentalImpact = round($rentalCost * $proportion, 2);

            // They get back min(their impact, their share of insurance paid)
            $theirShare = round($insurancePaid * $proportion, 2);
            $reimbursements[$commission->user_id] = min($rentalImpact, $theirShare);
            $totalAllocated += $reimbursements[$commission->user_id];
        }

        // If insurance paid more than total rental cost, shop keeps excess (no action needed)
        return $reimbursements;
    }
}
