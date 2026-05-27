<?php

namespace App\Http\Controllers;

use App\Services\ProductDetailImportService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProductDetailImportController extends Controller
{
    /**
     * Import product details from uploaded CSV file
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function import(Request $request): JsonResponse
    {
        try {
            // Validate request
            $request->validate([
                'csv_file' => 'required|file|mimes:csv,txt|max:10240', // 10MB max
                'clear_existing' => 'sometimes|boolean',
            ]);

            // Store uploaded file temporarily
            $file = $request->file('csv_file');
            $filename = 'csv_import_' . time() . '_' . uniqid() . '.csv';
            $storagePath = Storage::disk('local')->putFileAs('temp', $file, $filename);
            $filePath = Storage::disk('local')->path($storagePath);

            // Clear existing data if requested
            if ($request->boolean('clear_existing')) {
                ProductDetailImportService::clearImportedData();
            }

            // Perform import
            $results = ProductDetailImportService::importProductDetails($filePath);

            // Clean up temporary file
            Storage::disk('local')->delete($storagePath);

            // Get statistics
            $stats = ProductDetailImportService::getImportStats();

            return response()->json([
                'success' => true,
                'message' => 'Import completed successfully',
                'results' => $results,
                'statistics' => $stats,
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $e->errors(),
            ], 422);

        } catch (\Exception $e) {
            Log::error('Product Detail Import Error', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Import failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get import statistics
     *
     * @return JsonResponse
     */
    public function statistics(): JsonResponse
    {
        try {
            $stats = ProductDetailImportService::getImportStats();

            return response()->json([
                'success' => true,
                'data' => $stats,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve statistics: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get paginated product details
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function list(Request $request): JsonResponse
    {
        try {
            $perPage = $request->get('per_page', 50);
            $search = $request->get('search', '');
            $category = $request->get('category', '');

            $query = \App\Models\ProductDetail::query();

            // Apply search filter
            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('sku', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            }

            // Apply category filter
            if ($category) {
                $query->where('categories', 'like', "%{$category}%");
            }

            $products = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $products,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve products: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get single product detail
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        try {
            $product = \App\Models\ProductDetail::findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $product,
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found',
            ], 404);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve product: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Search products by SKU
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function searchBySku(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'sku' => 'required|string|min:1',
            ]);

            $product = \App\Models\ProductDetail::bySku($request->get('sku'))->first();

            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product with SKU not found',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $product,
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $e->errors(),
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Search failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Clear all imported data
     *
     * @return JsonResponse
     */
    public function clear(): JsonResponse
    {
        try {
            ProductDetailImportService::clearImportedData();

            return response()->json([
                'success' => true,
                'message' => 'All product details have been cleared',
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to clear data: ' . $e->getMessage(),
            ], 500);
        }
    }
}
