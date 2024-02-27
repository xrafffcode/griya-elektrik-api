<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProductBrand;
use App\Models\ProductCategory;
use App\Models\ProductImage;
use App\Models\ProductLink;
use App\Models\User;
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
        $password = '1234567890';
        $user = User::factory()->create(['password' => $password]);

        $this->actingAs($user);

        $api = $this->json('POST', 'api/v1/login', array_merge($user->toArray(), ['password' => $password]));

        $api->assertSuccessful();

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

        $api = $this->json('POST', 'api/v1/products', $product);

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
        $password = '1234567890';
        $user = User::factory()->create(['password' => $password]);

        $this->actingAs($user);

        $api = $this->json('POST', 'api/v1/login', array_merge($user->toArray(), ['password' => $password]));

        $api->assertSuccessful();

        $productCategory = ProductCategory::factory()->create();

        $productBrand = ProductBrand::factory()->create();

        $product = Product::factory()
            ->for($productCategory, 'category')
            ->for($productBrand, 'brand')
            ->make()->toArray();

        $api = $this->json('POST', 'api/v1/products', $product);

        $api->assertSuccessful();

        $product['thumbnail'] = $api['data']['thumbnail'];

        $this->assertDatabaseHas(
            'products', $product
        );
    }

    public function test_product_api_call_create_with_random_code_and_slug_and_product_category_has_children_expect_failure()
    {
        $password = '1234567890';
        $user = User::factory()->create(['password' => $password]);

        $this->actingAs($user);

        $api = $this->json('POST', 'api/v1/login', array_merge($user->toArray(), ['password' => $password]));

        $api->assertSuccessful();

        $productCategory = ProductCategory::factory()
            ->has(ProductCategory::factory()->count(1), 'children')
            ->create();

        $productBrand = ProductBrand::factory()->create();

        $product = Product::factory()
            ->for($productCategory, 'category')
            ->for($productBrand, 'brand')
            ->make()->toArray();

        $api = $this->json('POST', 'api/v1/products', $product);

        $api->assertStatus(400);
    }

    public function test_product_api_call_create_with_existing_code_and_random_slug_expect_failure()
    {
        $password = '1234567890';
        $user = User::factory()->create(['password' => $password]);

        $this->actingAs($user);

        $api = $this->json('POST', 'api/v1/login', array_merge($user->toArray(), ['password' => $password]));

        $api->assertSuccessful();

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

        $api = $this->json('POST', 'api/v1/products', $product);

        $api->assertStatus(422);
    }

    public function test_product_api_call_read_expect_collection()
    {
        $password = '1234567890';
        $user = User::factory()->create(['password' => $password]);

        $this->actingAs($user);

        $api = $this->json('POST', 'api/v1/login', array_merge($user->toArray(), ['password' => $password]));

        $api->assertSuccessful();

        $productCategory = ProductCategory::factory()->create();

        $productBrand = ProductBrand::factory()->create();

        $product = Product::factory()
            ->for($productCategory, 'category')
            ->for($productBrand, 'brand')
            ->count(3)
            ->create();

        $api = $this->json('GET', 'api/v1/products');

        $api->assertSuccessful();

        $api->assertJsonCount(3);

        foreach ($product as $item) {
            $this->assertDatabaseHas(
                'products', $item->toArray()
            );
        }
    }

    public function test_product_api_call_read_all_active_product_expect_collection()
    {
        $password = '1234567890';
        $user = User::factory()->create(['password' => $password]);

        $this->actingAs($user);

        $api = $this->json('POST', 'api/v1/login', array_merge($user->toArray(), ['password' => $password]));

        $api->assertSuccessful();

        $productCategory = ProductCategory::factory()->create();

        $productBrand = ProductBrand::factory()->create();

        $product = Product::factory()
            ->for($productCategory, 'category')
            ->for($productBrand, 'brand')
            ->count(3)
            ->create(['is_active' => true]);

        $api = $this->json('GET', 'api/v1/products/active');

        $api->assertSuccessful();

        $api->assertJsonCount(3);

        foreach ($product as $item) {
            $this->assertDatabaseHas(
                'products', $item->toArray()
            );
        }
    }

    public function test_product_api_call_read_all_active_and_featured_product_expect_collection()
    {
        $password = '1234567890';
        $user = User::factory()->create(['password' => $password]);

        $this->actingAs($user);

        $api = $this->json('POST', 'api/v1/login', array_merge($user->toArray(), ['password' => $password]));

        $api->assertSuccessful();

        $productCategory = ProductCategory::factory()->create();

        $productBrand = ProductBrand::factory()->create();

        $product = Product::factory()
            ->for($productCategory, 'category')
            ->for($productBrand, 'brand')
            ->count(3)
            ->create(['is_active' => true, 'is_featured' => true]);

        $api = $this->json('GET', 'api/v1/products/active-featured');

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
        $password = '1234567890';
        $user = User::factory()->create(['password' => $password]);

        $this->actingAs($user);

        $api = $this->json('POST', 'api/v1/login', array_merge($user->toArray(), ['password' => $password]));

        $api->assertSuccessful();

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

        $api = $this->json('POST', 'api/v1/products/'.$product->id, $productUpdate);

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
        $password = '1234567890';
        $user = User::factory()->create(['password' => $password]);

        $this->actingAs($user);

        $api = $this->json('POST', 'api/v1/login', array_merge($user->toArray(), ['password' => $password]));

        $api->assertSuccessful();

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

        $api = $this->json('POST', 'api/v1/products/'.$product->id, $productUpdate);

        $api->assertSuccessful();

        $productUpdate['thumbnail'] = $api['data']['thumbnail'];

        $this->assertDatabaseHas(
            'products', $productUpdate
        );

        $this->assertTrue(Storage::disk('public')->exists($productUpdate['thumbnail']));
    }

    public function test_product_api_call_set_active_product_expect_successful()
    {
        $password = '1234567890';
        $user = User::factory()->create(['password' => $password]);

        $this->actingAs($user);

        $api = $this->json('POST', 'api/v1/login', array_merge($user->toArray(), ['password' => $password]));

        $api->assertSuccessful();

        $productCategory = ProductCategory::factory()->create();

        $productBrand = ProductBrand::factory()->create();

        $product = Product::factory()
            ->for($productCategory, 'category')
            ->for($productBrand, 'brand')
            ->create(['is_active' => false]);

        $api = $this->json('POST', 'api/v1/products/'.$product->id.'/active', ['is_active' => true]);

        $api->assertSuccessful();

        $this->assertDatabaseHas(
            'products', ['id' => $product->id, 'is_active' => true]
        );
    }

    public function test_product_api_call_set_featured_product_expect_successful()
    {
        $password = '1234567890';
        $user = User::factory()->create(['password' => $password]);

        $this->actingAs($user);

        $api = $this->json('POST', 'api/v1/login', array_merge($user->toArray(), ['password' => $password]));

        $api->assertSuccessful();

        $productCategory = ProductCategory::factory()->create();

        $productBrand = ProductBrand::factory()->create();

        $product = Product::factory()
            ->for($productCategory, 'category')
            ->for($productBrand, 'brand')
            ->create(['is_featured' => false]);

        $api = $this->json('POST', 'api/v1/products/'.$product->id.'/featured', ['is_featured' => true]);

        $api->assertSuccessful();

        $this->assertDatabaseHas(
            'products', ['id' => $product->id, 'is_featured' => true]
        );
    }

    public function test_product_api_call_update_with_existing_code_in_same_product_and_random_slug_and_product_category_has_children_expect_failure()
    {
        $password = '1234567890';
        $user = User::factory()->create(['password' => $password]);

        $this->actingAs($user);

        $api = $this->json('POST', 'api/v1/login', array_merge($user->toArray(), ['password' => $password]));

        $api->assertSuccessful();

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

        $api = $this->json('POST', 'api/v1/products/'.$existingProduct->id, $productUpdate);

        $api->assertStatus(400);
    }

    public function test_product_api_call_update_with_existing_code_in_same_product_and_random_slug_expect_successful()
    {
        $password = '1234567890';
        $user = User::factory()->create(['password' => $password]);

        $this->actingAs($user);

        $api = $this->json('POST', 'api/v1/login', array_merge($user->toArray(), ['password' => $password]));

        $api->assertSuccessful();

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

        $api = $this->json('POST', 'api/v1/products/'.$product->id, $productUpdate);

        $api->assertSuccessful();

        $productUpdate['thumbnail'] = $api['data']['thumbnail'];

        $this->assertDatabaseHas(
            'products', $productUpdate
        );

        $this->assertTrue(Storage::disk('public')->exists($productUpdate['thumbnail']));
    }

    public function test_product_api_call_update_with_existing_code_in_different_product_and_random_slug_expect_failure()
    {
        $password = '1234567890';
        $user = User::factory()->create(['password' => $password]);

        $this->actingAs($user);

        $api = $this->json('POST', 'api/v1/login', array_merge($user->toArray(), ['password' => $password]));

        $api->assertSuccessful();

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

        $api = $this->json('POST', 'api/v1/products/'.$newProduct->id, $productUpdate);

        $api->assertStatus(422);
    }

    public function test_product_api_call_delete_expect_successful()
    {
        $password = '1234567890';
        $user = User::factory()->create(['password' => $password]);

        $this->actingAs($user);

        $api = $this->json('POST', 'api/v1/login', array_merge($user->toArray(), ['password' => $password]));

        $api->assertSuccessful();

        $productCategory = ProductCategory::factory()->create();

        $productBrand = ProductBrand::factory()->create();

        $product = Product::factory()
            ->for($productCategory, 'category')
            ->for($productBrand, 'brand')
            ->create();

        $api = $this->json('DELETE', 'api/v1/products/'.$product->id);

        $api->assertSuccessful();

        $this->assertSoftDeleted(
            'products', $product->toArray()
        );
    }
}
