<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Http\Resources\ProductResource;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Services\ProductService;
use App\Services\ImportExportService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    use ApiResponse;

    protected $productService;
    protected $importExportService;

    public function __construct(ProductService $productService, ImportExportService $importExportService)
    {
        $this->productService = $productService;
        $this->importExportService = $importExportService;
    }

    public function index(Request $request)
    {
        // $this->authorize('viewAny', Product::class); // Publicly accessible usually, but good practice if Auth middleware applied

        $perPage = $request->get('per_page', 20);
        $perPage = min(max((int)$perPage, 1), 100);
        
        $query = Product::with(['category', 'variants.inventory', 'images']);
        
        // Filter by status if provided
        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        } 
        // If not admin, FORCE active status (Storefront view)
        else if (!$request->user()?->isAdmin()) {
            $query->where('status', 'active');
        }
        
        $products = $query->paginate($perPage);
            
        return $this->success(ProductResource::collection($products)->response()->getData(true), 'Products retrieved', 200, [], 'products');
    }

    public function store(StoreProductRequest $request)
    {
        $this->authorize('create', Product::class);
        try {
            $product = $this->productService->createProduct($request->validated());
            return $this->success(new ProductResource($product), 'Product created successfully', 201, [], 'product');
        } catch (\Exception $e) {
            return $this->error('Failed to create product: ' . $e->getMessage(), 500);
        }
    }

    public function show($id)
    {
        // Support lookup by ID or slug
        $product = Product::with(['category', 'variants.inventory', 'variants.image', 'images', 'collections'])
            ->where('id', $id)
            ->orWhere('slug', $id)
            ->first();
            
        if (!$product) return $this->error('Product not found', 404);
        return $this->success(new ProductResource($product), 'Product details', 200, [], 'product');
    }

    public function update(UpdateProductRequest $request, $id)
    {
        $product = Product::find($id);
        if (!$product) return $this->error('Product not found', 404);
        
        $this->authorize('update', $product);

        // Validation handled by UpdateProductRequest
        $validated = $request->validated();

        try {
            $product = $this->productService->updateProduct($product, $validated);
            return $this->success(new ProductResource($product), 'Product updated successfully', 200, [], 'product');
        } catch (\Exception $e) {
            return $this->error('Failed to update product: ' . $e->getMessage(), 500);
        }
    }

    public function destroy($id)
    {
        try {
            $product = Product::findOrFail($id);
            $this->authorize('delete', $product);
            $this->productService->deleteProduct($id);
            return $this->success([], 'Product deleted successfully');
        } catch (\Exception $e) {
            return $this->error('Failed to delete product: ' . $e->getMessage(), 500);
        }
    }

    public function export()
    {
        return $this->importExportService->exportProducts();
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt'
        ]);

        try {
            $result = $this->importExportService->importProducts($request->file('file')->getPathname());
            return $this->success($result, 'Products imported successfully');
        } catch (\Exception $e) {
            return $this->error('Import failed: ' . $e->getMessage(), 500);
        }
    }
}
