<?php

namespace App\Http\Controllers;

use App\Contracts\FavoriteServiceInterface;
use App\Contracts\ProductServiceInterface;
use App\DTOs\FavoriteDTO;
use App\Http\Requests\AddFavoriteRequest;
use Illuminate\Http\JsonResponse;

class FavoriteController extends Controller
{
    public function __construct(
        private FavoriteServiceInterface $favoriteService,
        private ProductServiceInterface $productService
    ) {}


    /**
     * @OA\Post(
     *     path="/api/favorites",
     *     summary="Add favorite customer",
     *     tags={"Favorites"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="customer_id", type="integer", example="1"),
     *             @OA\Property(property="product_id", type="integer", example="1"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Profile updated successfull",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Product 'Mens Casual Slim Fit' added to favorites."),
     *             @OA\Property(property="favorite", type="object"),
     *             @OA\Property(property="product_details", type="object"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error"
     *     ),
     *      @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     ) 
     * )
     */
    public function addToFavorites(AddFavoriteRequest $request): JsonResponse
    {
        try {
            $customer = $request->user();
            
            $favoriteDTO = new FavoriteDTO(
                customer_id: $customer->id,
                product_id: $request->product_id
            );

            $result = $this->favoriteService->addToFavorites($favoriteDTO);

            return response()->json($result->toArray(), 201);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Product not found.',
                'error' => $e->getMessage()
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error adding product to favorites.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /** 
     *  @OA\Delete(
     *     path="/api/favorites/{id}",
     *     summary="Delete favorite",
     *     tags={"Favorites"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Favorite ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Product deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Product 'Mens Casual Slim Fit' removed from favorites."),
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     ) 
     * )
     */
    public function removeFromFavorites(int $favoriteId): JsonResponse
    {
        try {
            $customer = auth()->user();
            
            $result = $this->favoriteService->removeFromFavorites($favoriteId);

            return response()->json($result->toArray());
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Favorite not found.',
                'error' => $e->getMessage()
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error removing product from favorites.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /** 
     *  @OA\Delete(
     *     path="/api/favorites/product/{id}",
     *     summary="Delete favorite by product ID",
     *     tags={"Favorites"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Product ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Product deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Product 'Fjallraven - Foldsack No. 1 Backpack, Fits 15 Laptops' removed from favorites."),
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     ) 
     * )
     */
    public function removeFromFavoritesByProduct(int $productExternalId): JsonResponse
    {
        try {
            $customer = auth()->user();
            
            $result = $this->favoriteService->removeFromFavoritesByProductId(
                $customer->id, 
                $productExternalId
            );

            return response()->json($result->toArray());
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Favorite not found.',
                'error' => $e->getMessage()
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error removing product from favorites.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/favorites/",
     *     summary="List favorites",
     *     tags={"Favorites"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Data of favorites",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Favorites successfully obtained."),
     *             @OA\Property(property="favorites", type="object"),
     *             @OA\Property(property="total", type="integer"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     ) 
     * )
     */
    public function getCustomerFavorites(): JsonResponse
    {
        try {
            $customer = auth()->user();
            
            $favorites = $this->favoriteService->getCustomerFavorites($customer->id);

            return response()->json([
                'favorites' => $favorites,
                'total' => $favorites->count(),
                'message' => 'Favorites successfully obtained.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error getting favorites.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/favorites/check/ {id}",
     *     summary="Check if favorite exists",
     *     tags={"Favorites"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Favorite ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Data if favorite exist",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Favorites successfully obtained."),
     *             @OA\Property(property="is_in_favorites", type="boolean"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     ) 
     * )
     */
    public function checkProductInFavorites(int $productExternalId): JsonResponse
    {
        try {
            $customer = auth()->user();
            
            $isInFavorites = $this->favoriteService->isProductInFavorites(
                $customer->id, 
                $productExternalId
            );

            return response()->json([
                'is_in_favorites' => $isInFavorites,
                'message' => $isInFavorites ? 
                    'Product is in favorites.' : 
                    'Product is not in favorites.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error checking favorite.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getAllProducts(): JsonResponse
    {
        try {
            $products = $this->productService->getAllProducts();

            return response()->json([
                'products' => $products,
                'total' => $products->count(),
                'message' => 'Products successfully obtained.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error getting products.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getProductsByCategory(string $category): JsonResponse
    {
        try {
            $products = $this->productService->getProductsByCategory($category);

            return response()->json([
                'products' => $products,
                'category' => $category,
                'total' => $products->count(),
                'message' => 'Category products successfully obtained.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error getting products from category.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function searchProducts(string $query): JsonResponse
    {
        try {
            $products = $this->productService->searchProducts($query);

            return response()->json([
                'products' => $products,
                'search_query' => $query,
                'total' => $products->count(),
                'message' => 'Product search completed successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error searching for products.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getProduct(int $productId): JsonResponse
    {
        try {
            $product = $this->productService->getProductById($productId);

            if (!$product) {
                return response()->json([
                    'message' => 'Product not found.'
                ], 404);
            }

            return response()->json([
                'product' => $product->toArray(),
                'message' => 'Product obtained successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error getting product.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}