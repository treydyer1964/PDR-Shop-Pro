<?php

namespace App\Livewire\Settings;

use App\Models\ExpenseCategory;
use Livewire\Attributes\Computed;
use Livewire\Component;

class ExpenseCategorySettings extends Component
{
    public bool   $showAddForm = false;
    public string $addName     = '';

    public ?int   $editingId   = null;
    public string $editName    = '';

    #[Computed]
    public function categories()
    {
        return ExpenseCategory::where('tenant_id', auth()->user()->tenant_id)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }

    public function startEdit(int $id): void
    {
        $cat = ExpenseCategory::findOrFail($id);
        abort_unless($cat->tenant_id === auth()->user()->tenant_id, 403);

        $this->editingId   = $id;
        $this->editName    = $cat->name;
        $this->showAddForm = false;
    }

    public function saveEdit(): void
    {
        $this->validate(['editName' => 'required|string|max:100']);

        $cat = ExpenseCategory::findOrFail($this->editingId);
        abort_unless($cat->tenant_id === auth()->user()->tenant_id, 403);

        $cat->update(['name' => $this->editName]);
        $this->editingId = null;
    }

    public function cancelEdit(): void
    {
        $this->editingId = null;
        $this->resetErrorBag();
    }

    public function toggleActive(int $id): void
    {
        $cat = ExpenseCategory::findOrFail($id);
        abort_unless($cat->tenant_id === auth()->user()->tenant_id, 403);
        $cat->update(['active' => !$cat->active]);
    }

    public function addCategory(): void
    {
        $this->validate(['addName' => 'required|string|max:100']);

        $maxSort = ExpenseCategory::where('tenant_id', auth()->user()->tenant_id)->max('sort_order') ?? 10;

        ExpenseCategory::create([
            'tenant_id'  => auth()->user()->tenant_id,
            'name'       => $this->addName,
            'slug'       => null,
            'is_system'  => false,
            'sort_order' => $maxSort + 1,
            'active'     => true,
        ]);

        $this->addName     = '';
        $this->showAddForm = false;
    }

    public function deleteCategory(int $id): void
    {
        $cat = ExpenseCategory::findOrFail($id);
        abort_unless($cat->tenant_id === auth()->user()->tenant_id, 403);
        abort_if($cat->is_system, 403); // system categories cannot be deleted

        // Only allow deletion if no expenses reference this category
        if ($cat->expenses()->exists()) {
            $this->addError('delete_' . $id, 'Cannot delete — expenses reference this category.');
            return;
        }

        $cat->delete();
    }

    public function render()
    {
        return view('livewire.settings.expense-category-settings');
    }
}
