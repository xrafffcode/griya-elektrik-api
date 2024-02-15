<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\Interfaces\ProductRepositoryInterface;
use App\Models\ProductCategory;

class ProductController extends Controller
{
    private ProductRepositoryInterface $product;

    public function __construct(ProductRepositoryInterface $product)
    {
        $this->product = $product;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {
            $products = $this->product->getAllProducts();

            return ResponseHelper::jsonResponse(true, 'Success', ProductResource::collection($products), 200);
        } catch (\Exception $exception) {
            return ResponseHelper::jsonResponse(false, $exception->getMessage(), null, 500);
        }
    }

    /**
     *  Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     * */
    public function store(StoreProductRequest $request)
    {
        try {
            $code = $request['code'];
            if ($code == 'AUTO') {
                $tryCount = 1;
                do {
                    $code = $this->product->generateCode($tryCount);
                    $tryCount++;
                } while (! $this->product->isUniqueCode($code));
                $request['code'] = $code;
            }

            $productCategory = ProductCategory::find($request['product_category_id']);
            if ($productCategory->children()->exists()) {
                return ResponseHelper::jsonResponse(false, 'Cannot use product category with children. Please remove the children first.', null, 400);
            }

            $product = $this->product->createProduct($request->all());

            return ResponseHelper::jsonResponse(true, 'Success', new ProductResource($product), 200);
        } catch (\Exception $exception) {
            return ResponseHelper::jsonResponse(false, $exception->getMessage(), null, 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     * */
    public function show($id)
    {
        try {
            $product = $this->product->getProductById($id);

            if ($product) {
                return ResponseHelper::jsonResponse(true, 'Success', new ProductResource($product), 200);
            }

            return ResponseHelper::jsonResponse(false, 'Data not found', null, 404);
        } catch (\Exception $exception) {
            return ResponseHelper::jsonResponse(false, $exception->getMessage(), null, 500);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     * */
    public function update(UpdateProductRequest $request, $id)
    {
        try {
            $code = $request['code'];
            if ($code == 'AUTO') {
                $tryCount = 1;
                do {
                    $code = $this->product->generateCode($tryCount);
                    $tryCount++;
                } while (! $this->product->isUniqueCode($code, $id));
                $request['code'] = $code;
            }

            $productCategory = ProductCategory::find($request['product_category_id']);
            if ($productCategory->children()->exists()) {
                return ResponseHelper::jsonResponse(false, 'Cannot use product category with children. Please remove the children first.', null, 400);
            }

            $product = $this->product->updateProduct($id, $request->all());

            return ResponseHelper::jsonResponse(true, 'Success', new ProductResource($product), 200);
        } catch (\Exception $exception) {
            return ResponseHelper::jsonResponse(false, $exception->getMessage(), null, 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            $this->product->deleteProduct($id);

            return ResponseHelper::jsonResponse(true, 'Success', null, 200);
        } catch (\Exception $exception) {
            return ResponseHelper::jsonResponse(false, $exception->getMessage(), null, 500);
        }
    }

    public function deleteProductImage($id)
    {
        try {
            $this->product->deleteProductImage($id);

            return ResponseHelper::jsonResponse(true, 'Success', null, 200);
        } catch (\Exception $exception) {
            return ResponseHelper::jsonResponse(false, $exception->getMessage(), null, 500);
        }
    }
}
