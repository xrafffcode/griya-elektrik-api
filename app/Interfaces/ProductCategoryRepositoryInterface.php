<?php

namespace App\Interfaces;

interface ProductCategoryRepositoryInterface
{
    public function getAllCategory();

    public function getRootCategories();

    public function getLeafCategories();

    public function getEmptyCategories();

    public function getCategoryById(string $id);

    public function createCategory(array $data);

    public function updateCategory(string $id, array $data);

    public function deleteCategory(string $id);

    public function generateCode(int $tryCount);

    public function isUniqueCode(string $code, ?string $expectId = null);

    public function isUniqueSlug(string $slug, ?string $expectId = null);
}
