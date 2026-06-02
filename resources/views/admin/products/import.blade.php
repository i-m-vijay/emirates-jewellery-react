@extends('layouts.admin')

@section('title', 'Import Products')
@section('header', 'Import Products from CSV')

@section('content')
<div class="max-w-2xl mx-auto">
    <!-- Navigation -->
    <div class="mb-6">
        <a href="{{ route('admin.products.index') }}" class="text-blue-600 hover:text-blue-800">
            <i class="fas fa-arrow-left mr-2"></i>Back to Products
        </a>
    </div>

    <!-- Main Content -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Import Form -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold mb-4">Upload CSV File</h2>

                <form action="{{ route('admin.products.import') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                    @csrf

                    <!-- File Input -->
                    <div>
                        <label for="csv_file" class="block text-sm font-medium text-gray-700 mb-2">
                            Select CSV File <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input type="file" id="csv_file" name="csv_file" accept=".csv,.txt" 
                                   class="block w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent cursor-pointer
                                   @error('csv_file') border-red-500 @enderror">
                            <small class="text-gray-500 block mt-2">
                                <i class="fas fa-info-circle"></i> Accepted formats: CSV, TXT (max 10MB)
                            </small>
                        </div>
                        @error('csv_file')
                            <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Instructions -->
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <h3 class="font-semibold text-blue-900 mb-2">
                            <i class="fas fa-lightbulb mr-2"></i>CSV Format Requirements
                        </h3>
                        <ul class="text-sm text-blue-800 space-y-1 ml-4">
                            <li><i class="fas fa-check mr-2"></i>First row must contain headers</li>
                            <li><i class="fas fa-check mr-2"></i>Required columns: name, price, stock</li>
                            <li><i class="fas fa-check mr-2"></i>Optional columns: description, category, status</li>
                            <li><i class="fas fa-check mr-2"></i>Status should be 'active' or 'inactive' (default: active)</li>
                            <li><i class="fas fa-check mr-2"></i>Duplicate product names will be skipped</li>
                        </ul>
                    </div>

                    <!-- Column Mapping Info -->
                    <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                        <h3 class="font-semibold text-gray-900 mb-3">Expected CSV Columns</h3>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="border-b border-gray-300">
                                        <th class="text-left px-3 py-2 font-medium">Column Name</th>
                                        <th class="text-left px-3 py-2 font-medium">Type</th>
                                        <th class="text-left px-3 py-2 font-medium">Example</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="border-b border-gray-200">
                                        <td class="px-3 py-2 text-red-600 font-semibold">name</td>
                                        <td class="px-3 py-2 text-gray-700">String (required)</td>
                                        <td class="px-3 py-2 text-gray-600">Gold Ring</td>
                                    </tr>
                                    <tr class="border-b border-gray-200">
                                        <td class="px-3 py-2">price</td>
                                        <td class="px-3 py-2 text-gray-700">Number (required)</td>
                                        <td class="px-3 py-2 text-gray-600">5000.00</td>
                                    </tr>
                                    <tr class="border-b border-gray-200">
                                        <td class="px-3 py-2">stock</td>
                                        <td class="px-3 py-2 text-gray-700">Integer (required)</td>
                                        <td class="px-3 py-2 text-gray-600">10</td>
                                    </tr>
                                    <tr class="border-b border-gray-200">
                                        <td class="px-3 py-2">description</td>
                                        <td class="px-3 py-2 text-gray-700">Text (optional)</td>
                                        <td class="px-3 py-2 text-gray-600">Beautiful 18K gold ring</td>
                                    </tr>
                                    <tr class="border-b border-gray-200">
                                        <td class="px-3 py-2">category</td>
                                        <td class="px-3 py-2 text-gray-700">String (optional)</td>
                                        <td class="px-3 py-2 text-gray-600">Rings</td>
                                    </tr>
                                    <tr>
                                        <td class="px-3 py-2">status</td>
                                        <td class="px-3 py-2 text-gray-700">Enum (optional)</td>
                                        <td class="px-3 py-2 text-gray-600">active / inactive</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex gap-3">
                        <button type="submit" class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-medium">
                            <i class="fas fa-upload mr-2"></i>Import Products
                        </button>
                        <a href="{{ route('admin.products.index') }}" class="px-6 py-3 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition font-medium">
                            <i class="fas fa-times mr-2"></i>Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Download Template -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold mb-4">
                    <i class="fas fa-download mr-2 text-blue-600"></i>Get Started
                </h3>
                
                <p class="text-sm text-gray-600 mb-4">
                    Download our template to see the exact format required.
                </p>

                <a href="{{ route('admin.products.download-template') }}" 
                   class="block w-full text-center px-4 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition font-medium">
                    <i class="fas fa-file-download mr-2"></i>Download Template
                </a>
            </div>

            <!-- Sample CSV -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold mb-4">
                    <i class="fas fa-database mr-2 text-purple-600"></i>Sample Data
                </h3>
                
                <p class="text-sm text-gray-600 mb-3">Your CSV should look like this:</p>

                <div class="bg-gray-50 border border-gray-200 rounded p-3 text-xs overflow-x-auto">
                    <pre class="text-gray-700">name,description,price,stock,category,status
"Gold Ring","18K gold",5000,10,"Rings","active"
"Diamond Earrings","Brilliant cut",8000,5,"Earrings","active"
"Silver Necklace","Pure silver",3000,15,"Necklace","active"</pre>
                </div>
            </div>

            <!-- Tips -->
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6">
                <h3 class="text-lg font-semibold text-yellow-900 mb-3">
                    <i class="fas fa-exclamation-triangle mr-2"></i>Tips
                </h3>
                
                <ul class="text-sm text-yellow-800 space-y-2">
                    <li><i class="fas fa-star mr-2"></i>Use UTF-8 encoding</li>
                    <li><i class="fas fa-star mr-2"></i>Wrap text in quotes</li>
                    <li><i class="fas fa-star mr-2"></i>Use commas as separators</li>
                    <li><i class="fas fa-star mr-2"></i>No special characters in names</li>
                    <li><i class="fas fa-star mr-2"></i>Double-check prices and stock</li>
                </ul>
            </div>
        </div>
    </div>
</div>

@if ($errors->any())
    <div class="fixed bottom-4 right-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
        <strong>Error:</strong> Check your CSV file format.
    </div>
@endif
@endsection
