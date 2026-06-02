<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Subcategory;
use Illuminate\Http\JsonResponse;

class CategoryApiController extends Controller
{
    public function index(): JsonResponse
    {
        $categories = Category::withCount('subcategories')
            ->orderBy('category_name')
            ->get(['id', 'category_name', 'created_at']);

        return response()->json(['success' => true, 'data' => $categories]);
    }

    public function subcategories(Category $category): JsonResponse
    {
        $subcategories = $category->subcategories()
            ->orderBy('name')
            ->get(['id', 'category_id', 'name', 'image']);

        return response()->json([
            'success'  => true,
            'category' => ['id' => $category->id, 'name' => $category->category_name],
            'data'     => $subcategories,
        ]);
    }

    public function subcategoriesByCategory(int $categoryId): JsonResponse
    {
        $subcategories = Subcategory::where('category_id', $categoryId)
            ->orderBy('name')
            ->get(['id', 'category_id', 'name', 'image']);

        return response()->json(['success' => true, 'data' => $subcategories]);
    }
}
