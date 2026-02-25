<?php

namespace App\Services;

use App\Enums\Role;
use App\Models\ExpenseCategory;
use App\Models\User;
use App\Models\WorkOrder;
use App\Models\WorkOrderCommission;

class CommissionService
{
    /**
     * Calculate (or recalculate) all commissions for a work order.
     *
     * Returns an array of error strings if calculation cannot proceed.
     * Returns an empty array on success.
     *
     * Locked commissions are NOT recalculated — unlock first.
     */
    public function calculate(WorkOrder $workOrder): array
    {
        // ── Pre-flight checks ───────────────────────────────────────────────

        if ($workOrder->commissionsLocked()) {
            return ['Commissions are locked. Unlock them before recalculating.'];
        }

        if ($workOrder->invoice_total === null) {
            return ['Invoice total must be set before calculating commissions.'];
        }

        $workOrder->load('expenses.category', 'assignments.user');

        $net = (float) $workOrder->netTotal();

        // ── Delete any existing unlocked commissions ─────────────────────────

        WorkOrderCommission::where('work_order_id', $workOrder->id)
            ->where('is_paid', false)
            ->delete();

        $rows     = [];
        $tenantId = $workOrder->tenant_id;

        // ── PDR Techs ─────────────────────────────────────────────────────────

        $pdrAssignments = $workOrder->assignmentsForRole(Role::PDR_TECH);

        foreach ($pdrAssignments as $assignment) {
            $user      = $assignment->user;
            $rate      = (float) ($user->commission_rate ?? 0);
            $split     = (float) ($assignment->split_pct ?? 100);
            $amount    = round($net * ($split / 100) * ($rate / 100), 2);

            $rows[] = [
                'tenant_id'      => $tenantId,
                'work_order_id'  => $workOrder->id,
                'user_id'        => $user->id,
                'role'           => Role::PDR_TECH->value,
                'amount'         => $amount,
                'split_pct'      => $split,
                'rate_pct'       => $rate,
                'notes'          => "Net \${$this->fmt($net)} × {$split}% split × {$rate}% rate",
                'is_paid'        => false,
            ];
        }

        // ── Sales Advisors ────────────────────────────────────────────────────

        $advisorAssignments = $workOrder->assignmentsForRole(Role::SALES_ADVISOR);
        $advisorCount       = $advisorAssignments->count();

        foreach ($advisorAssignments as $assignment) {
            $user     = $assignment->user;
            $rate     = (float) ($user->commission_rate ?? 0);
            $split    = (float) ($assignment->split_pct ?? 100);
            $bonus    = (float) ($user->per_car_bonus ?? 0);

            $commissionPart = round($net * ($split / 100) * ($rate / 100), 2);
            $bonusPart      = $advisorCount > 0 ? round($bonus / $advisorCount, 2) : 0;
            $amount         = $commissionPart + $bonusPart;

            $noteParts = ["Net \${$this->fmt($net)} × {$split}% split × {$rate}% rate = \${$this->fmt($commissionPart)}"];
            if ($bonusPart > 0) {
                $noteParts[] = "per-car bonus \${$this->fmt($bonus)} ÷ {$advisorCount} advisor(s) = \${$this->fmt($bonusPart)}";
            }

            $rows[] = [
                'tenant_id'      => $tenantId,
                'work_order_id'  => $workOrder->id,
                'user_id'        => $user->id,
                'role'           => Role::SALES_ADVISOR->value,
                'amount'         => $amount,
                'split_pct'      => $split,
                'rate_pct'       => $rate,
                'notes'          => implode(' + ', $noteParts),
                'is_paid'        => false,
            ];
        }

        // ── Sales Manager override ────────────────────────────────────────────
        // Manager earns override % on Net × split_pct for each advisor
        // that has subject_to_manager_override = true.

        $overriddenAdvisors = $advisorAssignments->filter(
            fn($a) => (bool) ($a->user->subject_to_manager_override ?? false)
        );

        if ($overriddenAdvisors->isNotEmpty()) {
            $managers = User::where('tenant_id', $tenantId)
                ->where('active', true)
                ->whereHas('roles', fn($q) => $q->where('name', Role::SALES_MANAGER->value))
                ->get();

            foreach ($managers as $manager) {
                $overrideRate = (float) ($manager->sales_manager_override_rate ?? 0);
                if ($overrideRate <= 0) continue;

                $managerAmount = 0;
                $noteParts     = [];

                foreach ($overriddenAdvisors as $assignment) {
                    $split        = (float) ($assignment->split_pct ?? 100);
                    $advisorShare = round($net * ($split / 100), 2);
                    $portion      = round($advisorShare * ($overrideRate / 100), 2);
                    $managerAmount += $portion;

                    $noteParts[] = "{$assignment->user->name} net share \${$this->fmt($advisorShare)} × {$overrideRate}% = \${$this->fmt($portion)}";
                }

                $rows[] = [
                    'tenant_id'      => $tenantId,
                    'work_order_id'  => $workOrder->id,
                    'user_id'        => $manager->id,
                    'role'           => Role::SALES_MANAGER->value,
                    'amount'         => round($managerAmount, 2),
                    'split_pct'      => null,
                    'rate_pct'       => $overrideRate,
                    'notes'          => 'Override: ' . implode('; ', $noteParts),
                    'is_paid'        => false,
                ];
            }
        }

        // ── R&I Tech — 100% of R&I expense ───────────────────────────────────

        $riCategoryId  = ExpenseCategory::where('tenant_id', $tenantId)
            ->where('slug', ExpenseCategory::SLUG_RI)
            ->value('id');

        if ($riCategoryId) {
            $riExpense = $workOrder->expenses
                ->where('expense_category_id', $riCategoryId)
                ->sum('amount');

            if ($riExpense > 0) {
                $riAssignments = $workOrder->assignmentsForRole(Role::RI_TECH);

                foreach ($riAssignments as $assignment) {
                    $rows[] = [
                        'tenant_id'      => $tenantId,
                        'work_order_id'  => $workOrder->id,
                        'user_id'        => $assignment->user_id,
                        'role'           => Role::RI_TECH->value,
                        'amount'         => (float) $riExpense,
                        'split_pct'      => null,
                        'rate_pct'       => 100,
                        'notes'          => "R&I expense passthrough: \${$this->fmt($riExpense)}",
                        'is_paid'        => false,
                    ];
                }
            }
        }

        // ── Porter — 100% of Porter Fee expense ───────────────────────────────

        $porterCategoryId = ExpenseCategory::where('tenant_id', $tenantId)
            ->where('slug', ExpenseCategory::SLUG_PORTER_FEE)
            ->value('id');

        if ($porterCategoryId) {
            $porterExpense = $workOrder->expenses
                ->where('expense_category_id', $porterCategoryId)
                ->sum('amount');

            if ($porterExpense > 0) {
                $porterAssignments = $workOrder->assignmentsForRole(Role::PORTER);

                foreach ($porterAssignments as $assignment) {
                    $rows[] = [
                        'tenant_id'      => $tenantId,
                        'work_order_id'  => $workOrder->id,
                        'user_id'        => $assignment->user_id,
                        'role'           => Role::PORTER->value,
                        'amount'         => (float) $porterExpense,
                        'split_pct'      => null,
                        'rate_pct'       => 100,
                        'notes'          => "Porter Fee passthrough: \${$this->fmt($porterExpense)}",
                        'is_paid'        => false,
                    ];
                }
            }
        }

        // ── Insert all rows ───────────────────────────────────────────────────

        $now = now();
        foreach ($rows as &$row) {
            $row['created_at'] = $now;
            $row['updated_at'] = $now;
        }

        WorkOrderCommission::insert($rows);

        $workOrder->events()->create([
            'tenant_id'   => $tenantId,
            'user_id'     => auth()->id(),
            'type'        => 'commissions_calculated',
            'description' => 'Commissions calculated. Net: $' . $this->fmt($net) . ' · ' . count($rows) . ' line item(s).',
        ]);

        return [];
    }

    private function fmt(float $value): string
    {
        return number_format($value, 2);
    }
}
