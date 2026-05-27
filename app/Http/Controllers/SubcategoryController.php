<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSubcategoryRequest;
use App\Models\Category;
use App\Models\Subcategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SubcategoryController extends Controller
{
    public function index(Request $request)
    {
        $query = Subcategory::with('category');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('name', 'like', "%{$search}%")
                  ->orWhereHas('category', fn($q) => $q->where('category_name', 'like', "%{$search}%"));
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->input('category_id'));
        }

        $subcategories = $query->latest()->paginate(10);
        $categories    = Category::orderBy('category_name')->get();

        return view('admin.subcategories.index', compact('subcategories', 'categories'));
    }

    public function create()
    {
        $categories = Category::orderBy('category_name')->get();
        return view('admin.subcategories.create', compact('categories'));
    }

    public function store(StoreSubcategoryRequest $request)
    {
        $validated = $request->validated();

        if ($request->hasFile('image')) {
            $file              = $request->file('image');
            $filename          = time() . '_' . $file->getClientOriginalName();
            $file->storeAs('subcategories', $filename, 'public');
            $validated['image'] = 'subcategories/' . $filename;
        }

        Subcategory::create($validated);

        return redirect()->route('admin.subcategories.index')
                         ->with('success', 'Subcategory created successfully!');
    }

    public function edit(Subcategory $subcategory)
    {
        $categories = Category::orderBy('category_name')->get();
        return view('admin.subcategories.edit', compact('subcategory', 'categories'));
    }

    public function update(StoreSubcategoryRequest $request, Subcategory $subcategory)
    {
        $validated = $request->validated();

        if ($request->hasFile('image')) {
            if ($subcategory->image && Storage::disk('public')->exists($subcategory->image)) {
                Storage::disk('public')->delete($subcategory->image);
            }

            $file              = $request->file('image');
            $filename          = time() . '_' . $file->getClientOriginalName();
            $file->storeAs('subcategories', $filename, 'public');
            $validated['image'] = 'subcategories/' . $filename;
        }

        $subcategory->update($validated);

        return redirect()->route('admin.subcategories.index')
                         ->with('success', 'Subcategory updated successfully!');
    }

    public function destroy(Subcategory $subcategory)
    {
        if ($subcategory->image && Storage::disk('public')->exists($subcategory->image)) {
            Storage::disk('public')->delete($subcategory->image);
        }

        $subcategory->delete();

        return redirect()->route('admin.subcategories.index')
                         ->with('success', 'Subcategory deleted successfully!');
    }
}
