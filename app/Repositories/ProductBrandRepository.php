<?php

namespace App\Repositories;

use App\Interfaces\ProductBrandRepositoryInterface;
use App\Models\ProductBrand;
use Illuminate\Support\Facades\Storage;

class ProductBrandRepository implements ProductBrandRepositoryInterface
{
    public function getAllBrand()
    {
        $productBrand = ProductBrand::orderBy('name', 'asc')->get();

        return $productBrand;
    }

    public function getBrandById(string $id)
    {
        return ProductBrand::find($id);
    }

    public function createBrand(array $data)
    {
        $productBrand = new ProductBrand();
        $productBrand->code = $data['code'];
        $productBrand->name = $data['name'];
        $productBrand->logo = $data['logo'] ? $data['logo']->store('assets/product-brands', 'public') : '';
        $productBrand->slug = $data['slug'];
        $productBrand->save();

        return $productBrand;
    }

    public function updateBrand(string $id, array $data)
    {
        $productBrand = ProductBrand::find($id);

        if ($data['delete_logo']) {
            Storage::disk('public')->delete($productBrand->logo);
        }

        $productBrand->code = $data['code'];
        $productBrand->name = $data['name'];
        if ($data['logo']) {
            $productBrand->logo = $this->updateLogo($productBrand->logo, $data['logo']);
        }
        $productBrand->slug = $data['slug'];
        $productBrand->save();

        return $productBrand;
    }

    public function deleteBrand(string $id)
    {
        return ProductBrand::find($id)->delete();
    }

    public function generateCode(int $tryCount): string
    {
        $count = ProductBrand::count() + $tryCount;
        $code = str_pad($count, 2, '0', STR_PAD_LEFT);

        return $code;
    }

    public function isUniqueCode(string $code, ?string $expectId = null): bool
    {
        if (ProductBrand::count() == 0) {
            return true;
        }

        $result = ProductBrand::where('code', $code);

        if ($expectId) {
            $result->where('id', '!=', $expectId);
        }

        return $result->count() == 0;
    }

    public function isUniqueSlug(string $slug, ?string $expectId = null): bool
    {
        if (ProductBrand::count() == 0) {
            return true;
        }

        $result = ProductBrand::where('slug', $slug);

        if ($expectId) {
            $result->where('id', '!=', $expectId);
        }

        return $result->count() == 0;
    }

    private function updateLogo($oldImage, $newImage): string
    {
        if ($oldImage) {
            Storage::delete($oldImage);
        }

        if ($newImage) {
            return $newImage->store('assets/product-brands', 'public');
        }

        return '';
    }
}
