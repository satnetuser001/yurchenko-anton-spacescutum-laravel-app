<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\StoreProductRequest;
use App\Http\Requests\API\UpdateProductRequest;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class ProductController extends Controller implements HasMiddleware
{
    /**
     * Get the middleware that should be assigned to the controller.
     */
    public static function middleware(): array
    {
        return [
            new Middleware('auth:sanctum', except: ['index', 'show']),
        ];
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Product::query();

        // Apply category filter
        if ($request->has('category')) {
            $query->where('category', $request->input('category'));
        }

        // Apply price range filter
        if ($request->has('min_price')) {
            $query->where('price', '>=', $request->input('min_price'));
        }
        if ($request->has('max_price')) {
            $query->where('price', '<=', $request->input('max_price'));
        }

        // Apply popularity filter
        if ($request->has('popular')) {
            $direction = $request->input('popular') === 'asc' ? 'asc' : 'desc';
            $query->withCount('orders')->orderBy('orders_count', $direction);
        }

        $paginatedProducts = $query->paginate(5);

        // Add _links for each product
        $productsWithLinks = $paginatedProducts->getCollection()->map(function ($product) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'description' => $product->description,
                'price' => $product->price,
                'category' => $product->category,
                'image_url' => $product->image_url,
                'created_at' => $product->created_at,
                'updated_at' => $product->updated_at,
                '_links' => [
                    'self' => [
                        'href' => route('products.show', ['product' => $product->id]),
                    ],
                    'update' => [
                        'href' => route('products.update', ['product' => $product->id]),
                        'method' => 'PUT',
                    ],
                    'delete' => [
                        'href' => route('products.destroy', ['product' => $product->id]),
                        'method' => 'DELETE',
                    ],
                ],
            ];
        });

        // Replace the original collection with a new one with _links
        $paginatedProducts->setCollection($productsWithLinks);

        // Add filter links
        $filterLinks = [
            'category' => [
                'href' => route('products.index', ['category' => 'example-category']),
            ],
            'min_price' => [
                'href' => route('products.index', ['min_price' => 'example-min-price']),
            ],
            'max_price' => [
                'href' => route('products.index', ['max_price' => 'example-max-price']),
            ],
            'popular_asc' => [
                'href' => route('products.index', ['popular' => 'asc']),
            ],
            'popular_desc' => [
                'href' => route('products.index', ['popular' => 'desc']),
            ],
        ];

        return response()->json([
            'data' => $paginatedProducts,
            '_links_filters' => $filterLinks,
        ], Response::HTTP_OK)->header('Cache-Control', 'private, max-age=600');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProductRequest $request)
    {
        $validated = $request->validated();

        $product = Product::create($validated);

        return response()->json([
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'description' => $product->description,
                'price' => $product->price,
                'category' => $product->category,
                'image_url' => $product->image_url,
                'created_at' => $product->created_at,
                'updated_at' => $product->updated_at,
                '_links' => [
                    'self' => [
                        'href' => route('products.show', ['product' => $product->id]),
                    ],
                    'update' => [
                        'href' => route('products.update', ['product' => $product->id]),
                        'method' => 'PUT',
                    ],
                    'delete' => [
                        'href' => route('products.destroy', ['product' => $product->id]),
                        'method' => 'DELETE',
                    ],
                ],
            ],
        ], Response::HTTP_CREATED)->header('Cache-Control', 'private, max-age=600');
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        return response()->json([
            'id' => $product->id,
            'name' => $product->name,
            'description' => $product->description,
            'price' => $product->price,
            'category' => $product->category,
            'image_url' => $product->image_url,
            'created_at' => $product->created_at,
            'updated_at' => $product->updated_at,
            '_links' => [
                'self' => [
                    'href' => route('products.show', ['product' => $product->id]),
                ],
                'update' => [
                    'href' => route('products.update', ['product' => $product->id]),
                    'method' => 'PUT',
                ],
                'delete' => [
                    'href' => route('products.destroy', ['product' => $product->id]),
                    'method' => 'DELETE',
                ],
            ],
        ], Response::HTTP_OK)->header('Cache-Control', 'private, max-age=600');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProductRequest $request, Product $product)
    {
        $validated = $request->validated();

        $product->update($validated);
        
        return response()->json([
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'description' => $product->description,
                'price' => $product->price,
                'category' => $product->category,
                'image_url' => $product->image_url,
                'created_at' => $product->created_at,
                'updated_at' => $product->updated_at,
                '_links' => [
                    'self' => [
                        'href' => route('products.show', ['product' => $product->id]),
                    ],
                    'update' => [
                        'href' => route('products.update', ['product' => $product->id]),
                        'method' => 'PUT',
                    ],
                    'delete' => [
                        'href' => route('products.destroy', ['product' => $product->id]),
                        'method' => 'DELETE',
                    ],
                ],
            ],
        ], Response::HTTP_OK)->header('Cache-Control', 'private, max-age=600');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        $product->delete();
        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
