<?php

namespace App\Livewire\WorkOrders;

use App\Models\ExpenseCategory;
use App\Models\WorkOrder;
use App\Models\WorkOrderExpense;
use Livewire\Attributes\Computed;
use Livewire\Component;

class WorkOrderExpenses extends Component
{
    public WorkOrder $workOrder;

    // Invoice total editing
    public string $invoiceTotal  = '';
    public bool   $editingInvoice = false;

    // Add expense form
    public string $addCategoryId = '';
    public string $addAmount     = '';
    public string $addNotes      = '';
    public bool   $showAddForm   = false;

    public function mount(WorkOrder $workOrder): void
    {
        $this->workOrder    = $workOrder;
        $this->invoiceTotal = $workOrder->invoice_total !== null
            ? number_format((float) $workOrder->invoice_total, 2, '.', '')
            : '';
    }

    #[Computed]
    public function categories()
    {
        return ExpenseCategory::forTenant(auth()->user()->tenant_id)
            ->active()
            ->where(fn ($q) => $q->whereNull('slug')->orWhere('slug', '!=', ExpenseCategory::SLUG_RENTAL))
            ->orderBy('sort_order')
            ->get();
    }

    #[Computed]
    public function expenses()
    {
        return $this->workOrder->expenses()->with('category')->orderBy('id')->get();
    }

    #[Computed]
    public function totalExpenses(): float
    {
        return $this->expenses->sum('amount');
    }

    #[Computed]
    public function netTotal(): ?float
    {
        if ($this->invoiceTotal === '' && $this->workOrder->invoice_total === null) {
            return null;
        }

        $invoice = $this->editingInvoice
            ? (is_numeric($this->invoiceTotal) ? (float) $this->invoiceTotal : null)
            : (float) $this->workOrder->invoice_total;

        if ($invoice === null) {
            return null;
        }

        return $invoice - $this->totalExpenses;
    }

    public function saveInvoiceTotal(): void
    {
        $this->validate([
            'invoiceTotal' => 'required|numeric|min:0',
        ], [
            'invoiceTotal.required' => 'Please enter an invoice total.',
            'invoiceTotal.numeric'  => 'Invoice total must be a number.',
            'invoiceTotal.min'      => 'Invoice total cannot be negative.',
        ]);

        $this->workOrder->update(['invoice_total' => (float) $this->invoiceTotal]);
        $this->workOrder->refresh();
        $this->editingInvoice = false;
        $this->invoiceTotal   = number_format((float) $this->workOrder->invoice_total, 2, '.', '');
    }

    public function cancelInvoiceEdit(): void
    {
        $this->editingInvoice = false;
        $this->invoiceTotal   = $this->workOrder->invoice_total !== null
            ? number_format((float) $this->workOrder->invoice_total, 2, '.', '')
            : '';
        $this->resetErrorBag('invoiceTotal');
    }

    public function addExpense(): void
    {
        $this->validate([
            'addCategoryId' => 'required|exists:expense_categories,id',
            'addAmount'     => 'required|numeric|min:0.01',
            'addNotes'      => 'nullable|string|max:500',
        ], [
            'addCategoryId.required' => 'Please select a category.',
            'addAmount.required'     => 'Please enter an amount.',
            'addAmount.numeric'      => 'Amount must be a number.',
            'addAmount.min'          => 'Amount must be at least $0.01.',
        ]);

        WorkOrderExpense::create([
            'tenant_id'           => $this->workOrder->tenant_id,
            'work_order_id'       => $this->workOrder->id,
            'expense_category_id' => (int) $this->addCategoryId,
            'amount'              => (float) $this->addAmount,
            'notes'               => $this->addNotes ?: null,
            'created_by'          => auth()->id(),
        ]);

        $this->workOrder->load('expenses.category');
        $this->reset(['addCategoryId', 'addAmount', 'addNotes']);
        $this->showAddForm = false;
    }

    public function deleteExpense(int $expenseId): void
    {
        $expense = WorkOrderExpense::findOrFail($expenseId);
        abort_unless($expense->work_order_id === $this->workOrder->id, 403);

        $expense->delete();
        $this->workOrder->load('expenses.category');
    }

    public function render()
    {
        return view('livewire.work-orders.work-order-expenses');
    }
}
