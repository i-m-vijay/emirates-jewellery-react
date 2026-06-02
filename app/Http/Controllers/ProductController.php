<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\ImportProductsRequest;
use App\Models\Product;
use App\Services\CsvImportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    /**
     * Display a listing of products.
     */
    public function index(Request $request)
    {
        $query = Product::query();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('category', 'like', "%{$search}%");
        }

        $products = $query->latest()->paginate(10);

        return view('admin.products.index', compact('products'));
    }

    /**
     * Show the form for creating a new product.
     */
    public function create()
    {
        return view('admin.products.create');
    }

    /**
     * Store a newly created product in storage.
     */
    public function store(StoreProductRequest $request)
    {
        $validated = $request->validated();

        // Handle image upload
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->storeAs('products', $filename, 'public');
            $validated['image'] = 'products/' . $filename;
        }

        Product::create($validated);

        return redirect()->route('admin.products.index')
                       ->with('success', 'Product created successfully!');
    }

    /**
     * Display the specified product.
     */
    public function show(Product $product)
    {
        return view('admin.products.show', compact('product'));
    }

    /**
     * Show the form for editing the specified product.
     */
    public function edit(Product $product)
    {
        return view('admin.products.edit', compact('product'));
    }

    /**
     * Update the specified product in storage.
     */
    public function update(StoreProductRequest $request, Product $product)
    {
        $validated = $request->validated();

        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image if it exists
            if ($product->image && Storage::disk('public')->exists($product->image)) {
                Storage::disk('public')->delete($product->image);
            }

            $file = $request->file('image');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->storeAs('products', $filename, 'public');
            $validated['image'] = 'products/' . $filename;
        }

        $product->update($validated);

        return redirect()->route('admin.products.index')
                       ->with('success', 'Product updated successfully!');
    }

    /**
     * Remove the specified product from storage.
     */
    public function destroy(Product $product)
    {
        // Delete image if it exists
        if ($product->image && Storage::disk('public')->exists($product->image)) {
            Storage::disk('public')->delete($product->image);
        }

        $product->delete();

        return redirect()->route('admin.products.index')
                       ->with('success', 'Product deleted successfully!');
    }

    /**
     * Show the CSV import form.
     */
    public function showImportForm()
    {
        return view('admin.products.import');
    }

    /**
     * Handle CSV file upload and import.
     */
    public function import(ImportProductsRequest $request)
    {
        try {
            // Store the uploaded file temporarily
            $file = $request->file('csv_file');
            $filePath = $file->getRealPath();

            // Import products from CSV
            $results = CsvImportService::importProducts($filePath);

            // Prepare success/error messages
            $message = "Import completed! ";
            $message .= "{$results['success']} products imported successfully.";

            if ($results['failed'] > 0) {
                $message .= " {$results['failed']} products failed to import.";
            }

            return redirect()->route('admin.products.index')
                           ->with('success', $message)
                           ->with('import_errors', $results['errors']);
        } catch (\Exception $e) {
            return redirect()->back()
                           ->with('error', 'Import failed: ' . $e->getMessage());
        }
    }

    /**
     * Download CSV template.
     */
    public function downloadTemplate()
    {
        $fileName = 'products_import_template_' . date('Y-m-d') . '.csv';
        $content = CsvImportService::generateTemplate();

        return response($content)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', "attachment; filename=\"{$fileName}\"");
    }
}
