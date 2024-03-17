<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProductBrand;
use App\Models\ProductCategory;
use App\Models\ProductImage;
use App\Models\ProductLink;
use App\Models\User;
use App\Repositories\ProductCategoryRepository;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductAPITest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
    }

    public function test_product_api_call_create_with_auto_code_and_empty_slug_expect_successful()
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $productCategory = ProductCategory::factory()->create();

        $productBrand = ProductBrand::factory()->create();

        $product = Product::factory()
            ->for($productCategory, 'category')
            ->for($productBrand, 'brand')
            ->make([
                'code' => 'AUTO',
                'slug' => '',
            ])->toArray();

        $productImages = [];
        for ($i = 0; $i < 3; $i++) {
            array_push($productImages, ProductImage::factory()->make()->image);
        }
        $product['product_images'] = $productImages;

        $productLinks = [];
        for ($i = 0; $i < 3; $i++) {
            $productLink = ProductLink::factory()->make();
            array_push($productLinks, [
                'name' => $productLink->name,
                'url' => $productLink->url,
            ]);
        }
        $product['product_links'] = $productLinks;

        $api = $this->json('POST', 'api/v1/product', $product);

        $api->assertSuccessful();

        $product['code'] = $api['data']['code'];
        $product['thumbnail'] = $api['data']['thumbnail'];
        $product['slug'] = $api['data']['slug'];

        $this->assertDatabaseHas(
            'products', Arr::except($product, ['product_images', 'product_links'])
        );

        $this->assertTrue(Storage::disk('public')->exists($product['thumbnail']));

        foreach ($api['data']['product_images'] as $image) {
            $this->assertTrue(Storage::disk('public')->exists($image['image']));
        }

        foreach ($api['data']['product_links'] as $link) {
            $this->assertDatabaseHas(
                'product_links', $link
            );
        }
    }

    public function test_product_api_call_create_with_random_code_and_slug_expect_successful()
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $productCategory = ProductCategory::factory()->create();

        $productBrand = ProductBrand::factory()->create();

        $product = Product::factory()
            ->for($productCategory, 'category')
            ->for($productBrand, 'brand')
            ->make()->toArray();

        $api = $this->json('POST', 'api/v1/product', $product);

        $api->assertSuccessful();

        $product['thumbnail'] = $api['data']['thumbnail'];

        $this->assertDatabaseHas(
            'products', $product
        );
    }

    public function test_product_api_call_create_with_random_code_and_slug_and_product_category_has_children_expect_failure()
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $productCategory = ProductCategory::factory()
            ->has(ProductCategory::factory()->count(1), 'children')
            ->create();

        $productBrand = ProductBrand::factory()->create();

        $product = Product::factory()
            ->for($productCategory, 'category')
            ->for($productBrand, 'brand')
            ->make()->toArray();

        $api = $this->json('POST', 'api/v1/product', $product);

        $api->assertStatus(400);
    }

    public function test_product_api_call_create_with_existing_code_and_random_slug_expect_failure()
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $productCategory = ProductCategory::factory()->create();

        $productBrand = ProductBrand::factory()->create();

        $product = Product::factory()
            ->for($productCategory, 'category')
            ->for($productBrand, 'brand')
            ->create(['code' => 'test']);

        $product = Product::factory()
            ->for($productCategory, 'category')
            ->for($productBrand, 'brand')
            ->make(['code' => 'test'])->toArray();

        $api = $this->json('POST', 'api/v1/product', $product);

        $api->assertStatus(422);
    }

    public function test_product_api_call_read_expect_collection()
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $productCategory = ProductCategory::factory()->create();

        $productBrand = ProductBrand::factory()->create();

        $products = Product::factory()
            ->for($productCategory, 'category')
            ->for($productBrand, 'brand')
            ->count(3)
            ->create();

        $api = $this->json('GET', 'api/v1/product/read/any');

        $api->assertSuccessful();

        $api->assertJsonCount(3);

        foreach ($products as $product) {
            $this->assertDatabaseHas(
                'products', $product->toArray()
            );
        }
    }

    public function test_product_api_call_read_all_active_product_expect_collection()
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $productCategory = ProductCategory::factory()->create();

        $productBrand = ProductBrand::factory()->create();

        $product = Product::factory()
            ->for($productCategory, 'category')
            ->for($productBrand, 'brand')
            ->count(3)
            ->create(['is_active' => true]);

        $api = $this->json('GET', 'api/v1/product/read/active');

        $api->assertSuccessful();

        $api->assertJsonCount(3);

        foreach ($product as $item) {
            $this->assertDatabaseHas(
                'products', $item->toArray()
            );
        }
    }

    public function test_product_api_call_read_all_active_product_with_category_expect_collection()
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $categories = ProductCategory::factory()->getProductCategoryExample();

        $rootCategory = ProductCategory::factory()->setName($categories[0])->create();

        $category = ProductCategory::factory()->for($rootCategory, 'parent')->setName($categories[1])->create();

        ProductCategory::factory()->for($category, 'parent')->count(4)->create();
        $subCategory = ProductCategory::factory()->for($category, 'parent')->setName($categories[2])->create();

        ProductBrand::factory()->count(3)->create();

        Product::factory()
            ->for($subCategory, 'category')
            ->for(ProductBrand::inRandomOrder()->first(), 'brand')
            ->setActive()->count(10)->create();

        $api = $this->json('GET', 'api/v1/product/read/active?categoryId='.$rootCategory->id);

        $api->assertSuccessful();

        $productCategoryRepository = new ProductCategoryRepository();
        $categoryIds = $productCategoryRepository->getDescendantCategories($rootCategory->id);

        $productCount = Product::whereIn('product_category_id', $categoryIds)->where('is_active', true)->count();
        $this->assertEquals($productCount, count($api['data']));
    }

    public function test_product_api_call_read_all_active_product_with_brand_expect_collection()
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $productCategory = ProductCategory::factory()->create();

        ProductBrand::factory()->count(3)->create();

        $brand = ProductBrand::inRandomOrder()->first();

        Product::factory()
            ->for($productCategory, 'category')
            ->for($brand, 'brand')
            ->setActive()->count(5)->create();

        $api = $this->json('GET', 'api/v1/product/read/active?brandId='.$brand->id);

        $api->assertSuccessful();

        $productCount = Product::where('product_brand_id', $brand->id)->where('is_active', true)->count();
        $this->assertEquals($productCount, count($api['data']));
    }

    public function test_product_api_call_read_all_active_and_featured_product_expect_collection()
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $productCategory = ProductCategory::factory()->create();

        $productBrand = ProductBrand::factory()->create();

        $product = Product::factory()
            ->for($productCategory, 'category')
            ->for($productBrand, 'brand')
            ->count(3)
            ->create(['is_active' => true, 'is_featured' => true]);

        $api = $this->json('GET', 'api/v1/product/read/active-featured');

        $api->assertSuccessful();

        $api->assertJsonCount(3);

        foreach ($product as $item) {
            $this->assertDatabaseHas(
                'products', $item->toArray()
            );
        }
    }

    public function test_product_api_call_update_with_auto_code_and_empty_slug_expect_successful()
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $productCategory = ProductCategory::factory()->create();

        $productBrand = ProductBrand::factory()->create();

        $product = Product::factory()
            ->for($productCategory, 'category')
            ->for($productBrand, 'brand')
            ->create();

        $productUpdate = Product::factory()
            ->for($productCategory, 'category')
            ->for($productBrand, 'brand')
            ->make([
                'code' => 'AUTO',
                'slug' => '',
            ])->toArray();

        $productImages = [];
        for ($i = 0; $i < 3; $i++) {
            array_push($productImages, ProductImage::factory()->make()->image);
        }
        $productUpdate['product_images'] = $productImages;

        $productLinks = [];
        for ($i = 0; $i < 3; $i++) {
            $productLink = ProductLink::factory()->make();
            array_push($productLinks, [
                'name' => $productLink->name,
                'url' => $productLink->url,
            ]);
        }
        $productUpdate['product_links'] = $productLinks;

        $api = $this->json('POST', 'api/v1/product/'.$product->id, $productUpdate);

        $api->assertSuccessful();

        $productUpdate['code'] = $api['data']['code'];
        $productUpdate['thumbnail'] = $api['data']['thumbnail'];
        $productUpdate['slug'] = $api['data']['slug'];

        $this->assertDatabaseHas(
            'products', Arr::except($productUpdate, ['product_images', 'product_links'])
        );

        $this->assertTrue(Storage::disk('public')->exists($productUpdate['thumbnail']));

        foreach ($api['data']['product_images'] as $image) {
            $this->assertTrue(Storage::disk('public')->exists($image['image']));
        }

        foreach ($api['data']['product_links'] as $link) {
            $this->assertDatabaseHas(
                'product_links', $link
            );
        }
    }

    public function test_product_api_call_update_with_random_code_and_slug_expect_successful()
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $productCategory = ProductCategory::factory()->create();

        $productBrand = ProductBrand::factory()->create();

        $product = Product::factory()
            ->for($productCategory, 'category')
            ->for($productBrand, 'brand')
            ->create();

        $productCategory = ProductCategory::factory()->create();

        $productBrand = ProductBrand::factory()->create();

        $productUpdate = Product::factory()
            ->for($productCategory, 'category')
            ->for($productBrand, 'brand')
            ->make()->toArray();

        $api = $this->json('POST', 'api/v1/product/'.$product->id, $productUpdate);

        $api->assertSuccessful();

        $productUpdate['thumbnail'] = $api['data']['thumbnail'];

        $this->assertDatabaseHas(
            'products', $productUpdate
        );

        $this->assertTrue(Storage::disk('public')->exists($productUpdate['thumbnail']));
    }

    public function test_product_api_call_update_with_existing_code_in_same_product_expect_successful()
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $productCategory = ProductCategory::factory()->create();

        $productBrand = ProductBrand::factory()->create();

        $product = Product::factory()
            ->for($productCategory, 'category')
            ->for($productBrand, 'brand')
            ->create();

        $productUpdate = Product::factory()
            ->for($productCategory, 'category')
            ->for($productBrand, 'brand')
            ->make(['code' => $product->code])->toArray();

        $api = $this->json('POST', 'api/v1/product/'.$product->id, $productUpdate);

        $api->assertSuccessful();

        $productUpdate['thumbnail'] = $api['data']['thumbnail'];

        $this->assertDatabaseHas(
            'products', $productUpdate
        );

        $this->assertTrue(Storage::disk('public')->exists($productUpdate['thumbnail']));
    }

    public function test_product_api_call_update_change_brand_expect_succesful()
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $productCategory = ProductCategory::factory()->create();

        $productBrands = ProductBrand::factory()->count(2)->create();
        $oldProductBrand = $productBrands->first();
        $newProductBrand = $productBrands->last();

        $product = Product::factory()
            ->for($productCategory, 'category')
            ->for($oldProductBrand, 'brand')
            ->create();

        $productUpdate = $product->toArray();
        $productUpdate['product_brand_id'] = $newProductBrand->id;

        $api = $this->json('POST', 'api/v1/product/'.$product->id, $productUpdate);

        $api->assertSuccessful();

        $productUpdate['thumbnail'] = $api['data']['thumbnail'];

        // $this->assertDatabaseHas(
        //     'products', $productUpdate
        // );

        $this->assertDatabaseHas(
            'products', [
                'code' => $productUpdate['code'],
                'product_category_id' => $productUpdate['product_category_id'],
                'product_brand_id' => $productUpdate['product_brand_id'],
                'name' => $productUpdate['name'],
                'thumbnail' => $productUpdate['thumbnail'],
                'description' => $productUpdate['description'],
                'price' => $productUpdate['price'],
                'is_featured' => $productUpdate['is_featured'],
                'is_active' => $productUpdate['is_active'],
                'slug' => $productUpdate['slug'],
            ]
        );

        $this->assertTrue(Storage::disk('public')->exists($productUpdate['thumbnail']));
    }

    public function test_product_api_call_set_active_product_expect_successful()
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $productCategory = ProductCategory::factory()->create();

        $productBrand = ProductBrand::factory()->create();

        $product = Product::factory()
            ->for($productCategory, 'category')
            ->for($productBrand, 'brand')
            ->create(['is_active' => false]);

        $api = $this->json('POST', 'api/v1/product/'.$product->id.'/active', ['is_active' => true]);

        $api->assertSuccessful();

        $this->assertDatabaseHas(
            'products', ['id' => $product->id, 'is_active' => true]
        );
    }

    public function test_product_api_call_set_featured_product_expect_successful()
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $productCategory = ProductCategory::factory()->create();

        $productBrand = ProductBrand::factory()->create();

        $product = Product::factory()
            ->for($productCategory, 'category')
            ->for($productBrand, 'brand')
            ->create(['is_featured' => false]);

        $api = $this->json('POST', 'api/v1/product/'.$product->id.'/featured', ['is_featured' => true]);

        $api->assertSuccessful();

        $this->assertDatabaseHas(
            'products', ['id' => $product->id, 'is_featured' => true]
        );
    }

    public function test_product_api_call_update_with_existing_code_in_same_product_and_random_slug_and_product_category_has_children_expect_failure()
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $existingProductCategory = ProductCategory::factory()->create();

        $productBrand = ProductBrand::factory()->create();

        $existingProduct = Product::factory()
            ->for($existingProductCategory, 'category')
            ->for($productBrand, 'brand')
            ->create();

        $productCategory = ProductCategory::factory()
            ->has(ProductCategory::factory()->count(1), 'children')
            ->create();

        $productBrand = ProductBrand::factory()->create();

        $productUpdate = Product::factory()
            ->for($productCategory, 'category')
            ->for($productBrand, 'brand')
            ->make(['code' => $existingProduct->code])->toArray();

        $api = $this->json('POST', 'api/v1/product/'.$existingProduct->id, $productUpdate);

        $api->assertStatus(400);
    }

    public function test_product_api_call_update_with_existing_code_in_same_product_and_random_slug_expect_successful()
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $productCategory = ProductCategory::factory()->create();

        $productBrand = ProductBrand::factory()->create();

        $product = Product::factory()
            ->for($productCategory, 'category')
            ->for($productBrand, 'brand')
            ->create();

        $productUpdate = Product::factory()
            ->for($productCategory, 'category')
            ->for($productBrand, 'brand')
            ->make(['code' => $product->code])->toArray();

        $api = $this->json('POST', 'api/v1/product/'.$product->id, $productUpdate);

        $api->assertSuccessful();

        $productUpdate['thumbnail'] = $api['data']['thumbnail'];

        $this->assertDatabaseHas(
            'products', $productUpdate
        );

        $this->assertTrue(Storage::disk('public')->exists($productUpdate['thumbnail']));
    }

    public function test_product_api_call_update_with_existing_code_in_different_product_and_random_slug_expect_failure()
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $productCategory = ProductCategory::factory()->create();

        $productBrand = ProductBrand::factory()->create();

        $existingProduct = Product::factory()
            ->for($productCategory, 'category')
            ->for($productBrand, 'brand')
            ->create();

        $newProduct = Product::factory()
            ->for($productCategory, 'category')
            ->for($productBrand, 'brand')
            ->create();

        $productUpdate = Product::factory()
            ->for($productCategory, 'category')
            ->for($productBrand, 'brand')
            ->make(['code' => $existingProduct->code])->toArray();

        $api = $this->json('POST', 'api/v1/product/'.$newProduct->id, $productUpdate);

        $api->assertStatus(422);
    }

    public function test_product_api_call_delete_expect_successful()
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $productCategory = ProductCategory::factory()->create();

        $productBrand = ProductBrand::factory()->create();

        $product = Product::factory()
            ->for($productCategory, 'category')
            ->for($productBrand, 'brand')
            ->create();

        $api = $this->json('DELETE', 'api/v1/product/'.$product->id);

        $api->assertSuccessful();

        $this->assertSoftDeleted(
            'products', $product->toArray()
        );
    }
}
