<?php

namespace App\Repositories;

use App\Interfaces\BranchRepositoryInterface;
use App\Models\Branch;
use App\Models\BranchImage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class BranchRepository implements BranchRepositoryInterface
{
    public function getAllBranch()
    {
        return Branch::orderBy('sort', 'asc')->get();
    }

    public function getBranchById(string $id)
    {
        return Branch::find($id);
    }

    public function createBranch(array $data)
    {
        try {
            DB::beginTransaction();

            $branch = new Branch();
            $branch->code = $data['code'];
            $branch->name = $data['name'];
            $branch->map_url = $data['map_url'];
            $branch->iframe_map = $data['iframe_map'];
            $branch->address = $data['address'];
            $branch->city = $data['city'];
            $branch->email = $data['email'];
            $branch->phone = $data['phone'];
            $branch->facebook = $data['facebook'];
            $branch->instagram = $data['instagram'];
            $branch->youtube = $data['youtube'];
            $branch->sort = $data['sort'];
            $branch->is_main = $data['is_main'];
            $branch->is_active = $data['is_active'];
            $branch->save();

            if (isset($data['branch_images'])) {
                foreach ($data['branch_images'] as $image) {
                    $branchImage = new BranchImage();
                    $branchImage->branch_id = $branch->id;
                    $branchImage->image = $image->store('assets/branches/images', 'public');
                    $branchImage->save();
                }
            }

            DB::commit();

            return $branch;
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    public function updateBranch(string $id, array $data)
    {
        try {
            DB::beginTransaction();

            $branch = Branch::find($id);
            $branch->code = $data['code'];
            $branch->name = $data['name'];
            $branch->map_url = $data['map_url'];
            $branch->iframe_map = $data['iframe_map'];
            $branch->address = $data['address'];
            $branch->city = $data['city'];
            $branch->email = $data['email'];
            $branch->phone = $data['phone'];
            $branch->facebook = $data['facebook'];
            $branch->instagram = $data['instagram'];
            $branch->youtube = $data['youtube'];
            $branch->sort = $data['sort'];
            $branch->is_main = $data['is_main'];
            $branch->is_active = $data['is_active'];
            $branch->save();

            if (count($data['deleted_images']) > 0) {
                $this->deleteBranchImages($data['deleted_images']);
            }
            if (isset($data['branch_images'])) {
                foreach ($data['branch_images'] as $image) {
                    $branchImage = new BranchImage();
                    $branchImage->branch_id = $branch->id;
                    $branchImage->image = $image->store('assets/branches/images', 'public');
                    $branchImage->save();
                }
            }

            DB::commit();

            return $branch;
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    public function updateMainBranch(string $id, bool $isMain)
    {
        Branch::where([
            ['id', '!=', $id],
            ['is_main', true],
        ])->update(['is_main' => false]);

        $branch = Branch::find($id);
        $branch->is_main = $isMain;
        $branch->save();

        return $branch;
    }

    public function updateActiveBranch(string $id, bool $isActive)
    {
        $branch = Branch::find($id);
        $branch->is_active = $isActive;
        $branch->save();

        return $branch;
    }

    public function getMainBranch()
    {
        return Branch::where('is_main', true)->first();
    }

    public function getActiveBranch()
    {
        return Branch::where('is_active', true)
            ->orderBy('is_main', 'desc')
            ->orderBy('id', 'asc')
            ->get();
    }

    public function deleteBranch(string $id)
    {
        return Branch::find($id)->delete();
    }

    public function generateCode(int $tryCount): string
    {
        $count = Branch::count() + $tryCount;
        $code = str_pad($count, 2, '0', STR_PAD_LEFT);

        return $code;
    }

    public function isUniqueCode(string $code, ?string $expectId = null): bool
    {
        if (Branch::count() == 0) {
            return true;
        }

        $result = Branch::where('code', $code);

        if ($expectId) {
            $result->where('id', '!=', $expectId);
        }

        return $result->count() == 0;
    }

    private function deleteBranchImages(array $imageIds)
    {
        $branchImages = BranchImage::whereIn('id', $imageIds)->get();
        foreach ($branchImages as $branchImage) {
            Storage::disk('public')->delete($branchImage->image);
        }

        return BranchImage::whereIn('id', $imageIds)->delete();
    }
}
