<?php

namespace App\Interfaces;

interface ProductRepositoryInterface
{
    public function getAllProducts();

    public function getAllActiveProducts($search = null, $categorySlug = null, $brandSlug = null, $sort = null);

    public function getAllActiveAndFeaturedProducts();

    public function getProductById(string $id);

    public function getProductBySlug(string $slug);

    public function createProduct(array $data);

    public function updateProduct(string $id, array $data);

    public function updateFeaturedProduct(string $id, bool $is_featured);

    public function updateActiveProduct(string $id, bool $is_active);

    public function deleteProduct(string $id);

    public function generateCode(int $tryCount);

    public function isUniqueCode(string $code, ?string $expectId = null);

    public function isUniqueSlug(string $slug, ?string $expectId = null);
}
