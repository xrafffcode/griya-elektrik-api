<?php

namespace App\Repositories;

use App\Interfaces\ProductRepositoryInterface;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductLink;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProductRepository implements ProductRepositoryInterface
{
    public function getAllProducts()
    {
        $query = Product::with('category', 'brand');

        return $query->get();
    }

    public function getAllActiveProducts($search = null, $categoryId = null, $brandId = null, $sort = null)
    {
        $query = Product::with('category', 'brand');

        if ($search) {
            $query->where('name', 'like', '%'.$search.'%');
        }

        if ($categoryId) {
            $productCategoryRepository = new ProductCategoryRepository();
            $categoryIds = $productCategoryRepository->getDescendantCategories($categoryId);

            $query->whereIn('product_category_id', $categoryIds);
        }

        if ($brandId) {
            $query->where('product_brand_id', $brandId);
        }

        $query->where('is_active', true);

        if ($sort === 'price_asc') {
            $query->orderBy('price', 'asc');
        } elseif ($sort === 'price_desc') {
            $query->orderBy('price', 'desc');
        } elseif ($sort === 'latest') {
            $query->orderBy('created_at', 'desc');
        } elseif ($sort === 'oldest') {
            $query->orderBy('created_at', 'asc');
        }

        return $query->get();
    }

    public function getAllActiveAndFeaturedProducts()
    {
        $products = Product::with('category', 'brand')
            ->where('is_active', true)
            ->where('is_featured', true)
            ->get();

        return $products;
    }

    public function getProductById(string $id)
    {
        return Product::with('category', 'brand')->find($id);
    }

    public function getProductBySlug(string $slug)
    {
        return Product::with('category', 'brand')->where('slug', $slug)->first();
    }

    public function createProduct(array $data)
    {
        DB::beginTransaction();

        try {
            $product = new Product();
            $product->code = $data['code'];
            $product->product_category_id = $data['product_category_id'];
            $product->product_brand_id = $data['product_brand_id'];
            $product->name = $data['name'];
            $product->thumbnail = $data['thumbnail']->store('assets/products/thumbnails', 'public');
            $product->description = $data['description'];
            $product->price = $data['price'];
            $product->is_featured = $data['is_featured'];
            $product->is_active = $data['is_active'];
            $product->slug = $data['slug'];
            $product->save();

            if (isset($data['product_images'])) {
                foreach ($data['product_images'] as $image) {
                    $productImage = new ProductImage();
                    $productImage->product_id = $product->id;
                    $productImage->image = $image->store('assets/products/images', 'public');
                    $productImage->save();
                }
            }

            if (isset($data['product_links'])) {
                foreach ($data['product_links'] as $link) {
                    $productLink = new ProductLink();
                    $productLink->product_id = $product->id;
                    $productLink->name = $link['name'];
                    $productLink->url = $link['url'];
                    $productLink->save();
                }
            }

            DB::commit();

            return $product;
        } catch (\Exception $e) {
            DB::rollBack();

            throw $e;
        }
    }

    public function updateProduct(string $id, array $data)
    {
        DB::beginTransaction();

        try {
            $product = Product::find($id);
            $product->code = $data['code'];
            $product->product_category_id = $data['product_category_id'];
            $product->product_brand_id = $data['product_brand_id'];
            $product->name = $data['name'];
            $product->thumbnail = $this->updateThumbnail($product->thumbnail, $data['thumbnail']);
            $product->description = $data['description'];
            $product->price = $data['price'];
            $product->is_featured = $data['is_featured'];
            $product->is_active = $data['is_active'];
            $product->slug = $data['slug'];
            $product->save();

            if (count($data['deleted_images']) > 0) {
                $this->deleteProductImages($data['deleted_images']);
            }
            if (isset($data['product_images'])) {
                foreach ($data['product_images'] as $image) {
                    $productImage = new ProductImage();
                    $productImage->product_id = $product->id;
                    $productImage->image = $image->store('assets/products/images', 'public');
                    $productImage->save();
                }
            }

            if (isset($data['product_links'])) {
                foreach ($data['product_links'] as $link) {
                    $productLink = new ProductLink();
                    $productLink->product_id = $product->id;
                    $productLink->name = $link['name'];
                    $productLink->url = $link['url'];
                    $productLink->save();
                }
            }

            DB::commit();

            return $product;
        } catch (\Exception $e) {
            DB::rollBack();

            throw $e;
        }

    }

    public function updateFeaturedProduct(string $id, bool $is_featured)
    {
        $product = Product::find($id);
        $product->is_featured = $is_featured;
        $product->save();

        return $product;
    }

    public function updateActiveProduct(string $id, bool $is_active)
    {
        $product = Product::find($id);
        $product->is_active = $is_active;
        $product->save();

        return $product;
    }

    public function deleteProduct(string $id)
    {
        return Product::find($id)->delete();
    }

    public function generateCode(int $tryCount): string
    {
        $count = Product::count() + $tryCount;
        $code = str_pad($count, 2, '0', STR_PAD_LEFT);

        return $code;
    }

    public function isUniqueCode(string $code, ?string $expectId = null): bool
    {
        if (Product::count() == 0) {
            return true;
        }

        $result = Product::where('code', $code);

        if ($expectId) {
            $result = $result->where('id', '!=', $expectId);
        }

        return $result->count() == 0 ? true : false;
    }

    public function isUniqueSlug(string $slug, ?string $expectId = null): bool
    {
        if (Product::count() == 0) {
            return true;
        }

        $result = Product::where('slug', $slug);

        if ($expectId) {
            $result = $result->where('id', '!=', $expectId);
        }

        return $result->count() == 0 ? true : false;
    }

    private function updateThumbnail($oldThumbnail, $newThumbnail)
    {
        if ($oldThumbnail) {
            Storage::disk('public')->delete($oldThumbnail);
        }

        return $newThumbnail->store('assets/products/thumbnails', 'public');
    }

    private function deleteProductImages(array $imageIds)
    {
        $productImages = ProductImage::whereIn('id', $imageIds)->get();
        foreach ($productImages as $productImage) {
            Storage::disk('public')->delete($productImage->image);
        }

        return ProductImage::whereIn('id', $imageIds)->delete();
    }
}
