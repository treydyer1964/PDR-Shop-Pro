<?php

namespace App\Livewire\WorkOrders;

use App\Enums\PhotoCategory;
use App\Models\WorkOrder;
use App\Models\WorkOrderPhoto;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithFileUploads;

class WorkOrderPhotos extends Component
{
    use WithFileUploads;

    public WorkOrder $workOrder;

    public string $activeTab      = 'before';
    public string $uploadCategory = 'before';
    public array  $uploads        = []; // temporary files from Livewire

    public function mount(WorkOrder $workOrder): void
    {
        $this->workOrder = $workOrder;
    }

    #[Computed]
    public function categories(): array
    {
        return PhotoCategory::cases();
    }

    #[Computed]
    public function photos()
    {
        return $this->workOrder->photos()
            ->get()
            ->groupBy(fn($p) => $p->category->value);
    }

    #[Computed]
    public function activePhotos()
    {
        return $this->photos->get($this->activeTab, collect());
    }

    #[Computed]
    public function totalCount(): int
    {
        return $this->workOrder->photos()->count();
    }

    #[Computed]
    public function countByCategory(): array
    {
        return $this->workOrder->photos()
            ->reorder()
            ->selectRaw('category, count(*) as total')
            ->groupBy('category')
            ->pluck('total', 'category')
            ->all();
    }

    public function setTab(string $tab): void
    {
        $this->activeTab      = $tab;
        $this->uploadCategory = $tab;
        $this->uploads        = [];
        $this->resetErrorBag();
    }

    public function updatedUploads(): void
    {
        $this->validate([
            'uploads.*' => 'image|max:20480', // 20 MB per file
        ]);

        $this->saveUploads();
    }

    private function saveUploads(): void
    {
        $tenantId = $this->workOrder->tenant_id;
        $woId     = $this->workOrder->id;
        $dir      = "photos/{$tenantId}/{$woId}";
        $category = $this->uploadCategory;

        foreach ($this->uploads as $file) {
            $uuid      = Str::uuid();
            $ext       = $file->getClientOriginalExtension() ?: 'jpg';
            $filename  = "{$uuid}.{$ext}";
            $path      = $file->storeAs($dir, $filename, 'public');

            WorkOrderPhoto::create([
                'tenant_id'         => $tenantId,
                'work_order_id'     => $woId,
                'uploaded_by'       => auth()->id(),
                'path'              => $path,
                'disk'              => 'public',
                'original_filename' => $file->getClientOriginalName(),
                'mime_type'         => $file->getMimeType(),
                'size'              => $file->getSize(),
                'category'          => $category,
            ]);
        }

        $this->uploads = [];
        $this->workOrder->load('photos');
    }

    public function deletePhoto(int $photoId): void
    {
        $photo = WorkOrderPhoto::findOrFail($photoId);
        abort_unless($photo->work_order_id === $this->workOrder->id, 403);

        $photo->deleteFile();
        $photo->delete();

        $this->workOrder->load('photos');
    }

    public function shareUrl(): string
    {
        return URL::signedRoute('photos.share', [
            'workOrder' => $this->workOrder->id,
        ]);
    }

    public function render()
    {
        return view('livewire.work-orders.work-order-photos');
    }
}
