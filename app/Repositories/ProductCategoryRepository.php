<?php

namespace App\Repositories;

use App\Helpers\ImageHelper\ImageHelper;
use App\Interfaces\ProductCategoryRepositoryInterface;
use App\Models\ProductCategory;
use Illuminate\Support\Facades\Storage;

class ProductCategoryRepository implements ProductCategoryRepositoryInterface
{
    public function getAllCategory()
    {
        $productCategory = ProductCategory::orderBy('name', 'desc')->get();

        return $productCategory;
    }

    public function getRootCategories()
    {
        $rootCategories = ProductCategory::getRootCategories();

        return $rootCategories;
    }

    public function getLeafCategories()
    {
        $leafCategories = ProductCategory::getLeafCategories();

        return $leafCategories;
    }

    public function getEmptyCategories()
    {
        $emptyCategories = ProductCategory::getEmptyCategories();

        return $emptyCategories;
    }

    public function getDescendantCategories(string $productCategoryId)
    {
        $productCategory = [$productCategoryId];
        $result = [];

        while (! empty($productCategory)) {
            $currentCategoryId = array_shift($productCategory);

            $category = ProductCategory::find($currentCategoryId);
            if (! $category) {
                continue;
            }

            $result[] = $currentCategoryId;

            $childrenIds = $category->children()->pluck('id')->toArray();
            $productCategory = array_merge($productCategory, $childrenIds);
        }

        foreach ($result as $categoryId) {
            $category = ProductCategory::find($categoryId);
            if (! $category->isLeaf()) {
                unset($result[array_search($categoryId, $result)]);
            }
        }

        return $result;
    }

    public function getCategoryById(string $id)
    {
        return ProductCategory::find($id);
    }

    public function getCategoryBySlug(string $slug)
    {
        return ProductCategory::where('slug', $slug)->first();
    }

    public function createCategory(array $data)
    {
        $productCategory = new ProductCategory();
        $productCategory->parent_id = $data['parent_id'];
        $productCategory->code = $data['code'];
        $productCategory->name = $data['name'];
        $productCategory->image = $this->saveImage($data['image']);
        $productCategory->sort_order = $data['sort_order'];
        $productCategory->slug = $data['slug'];
        $productCategory->save();

        return $productCategory;
    }

    private function saveImage($image)
    {
        if ($image) {
            $path = $image->store('assets/product-categories', 'public');

            // $storagePath = storage_path('app/public/'.$path);
            $storagePath = Storage::disk('public')->path($path);
            $imageHelper = new ImageHelper();
            $imageHelper->resizeImage($storagePath, $storagePath, 500, 500);

            return $path;
        } else {
            return null;
        }
    }

    public function updateCategory(string $id, array $data)
    {
        $productCategory = ProductCategory::find($id);

        $productCategory->parent_id = $data['parent_id'];
        $productCategory->code = $data['code'];
        $productCategory->name = $data['name'];
        $productCategory->image = $this->updateImage($productCategory->image, $data['image']);
        $productCategory->sort_order = $data['sort_order'];
        $productCategory->slug = $data['slug'];
        $productCategory->save();

        return $productCategory;
    }

    // private function updateImage($oldImage, $newImage): string
    // {
    //     if ($oldImage) {
    //         Storage::disk('public')->delete($oldImage);
    //     }

    //     return $newImage->store('assets/product-categories', 'public');
    // }

    private function updateImage($oldImage, $newImage)
    {
        if ($newImage) {
            if ($oldImage) {
                Storage::disk('public')->delete($oldImage);
            }

            $path = $newImage->store('assets/product-categories', 'public');

            // $storagePath = storage_path('app/public/'.$path);
            $storagePath = Storage::disk('public')->path($path);
            $imageHelper = new ImageHelper();
            $imageHelper->resizeImage($storagePath, $storagePath, 500, 500);

            return $path;
        } else {
            return $oldImage;
        }
    }

    public function deleteCategory(string $id)
    {
        return ProductCategory::find($id)->delete();
    }

    public function generateCode(int $tryCount): string
    {
        $count = ProductCategory::withTrashed()->count() + $tryCount;
        $code = str_pad($count, 2, '0', STR_PAD_LEFT);

        return $code;
    }

    public function isUniqueCode(string $code, ?string $expectId = null): bool
    {
        if (ProductCategory::count() == 0) {
            return true;
        }

        $result = ProductCategory::where('code', $code);

        if ($expectId) {
            $result = $result->where('id', '!=', $expectId);
        }

        return $result->count() == 0 ? true : false;
    }

    public function isUniqueSlug(string $slug, ?string $expectId = null): bool
    {
        if (ProductCategory::count() == 0) {
            return true;
        }

        $result = ProductCategory::where('slug', $slug);

        if ($expectId) {
            $result = $result->where('id', '!=', $expectId);
        }

        return $result->count() == 0 ? true : false;
    }

    public function isDescendantCategory($ancestorId, $categoryId)
    {
        $category = $this->getCategoryById($categoryId);

        if (! $category) {
            return false;
        }

        while ($category) {
            if ($category->parent_id === $ancestorId) {
                return true;
            }

            $category = $this->getCategoryById($category->parent_id);
        }

        return false;
    }

    public function isAncestor($parentId, $categoryId): bool
    {
        $currentParentId = $parentId;
        while (! is_null($currentParentId)) {
            if ($currentParentId == $categoryId) {
                return true;
            }
            $currentParentId = ProductCategory::find($currentParentId)->parent_id;
        }

        return false;
    }
}
