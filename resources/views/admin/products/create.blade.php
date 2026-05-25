@extends('layouts.admin')

@section('title', 'Create Product')
@section('header', 'Create Product')

@section('content')
<div class="bg-white rounded-lg shadow p-6 max-w-2xl">
    <form method="POST" action="{{ route('admin.products.store') }}" enctype="multipart/form-data">
        @csrf

        <!-- Name -->
        <div class="mb-4">
            <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Product Name *</label>
            <input type="text" id="name" name="name" value="{{ old('name') }}" 
                   class="w-full px-4 py-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500 {{ $errors->has('name') ? 'border-red-500' : '' }}">
            @error('name')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Price -->
        <div class="grid grid-cols-2 gap-4 mb-4">
            <div>
                <label for="price" class="block text-sm font-medium text-gray-700 mb-1">Price (USD) *</label>
                <input type="number" id="price" name="price" step="0.01" value="{{ old('price') }}" 
                       class="w-full px-4 py-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500 {{ $errors->has('price') ? 'border-red-500' : '' }}">
                @error('price')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="stock" class="block text-sm font-medium text-gray-700 mb-1">Stock Quantity *</label>
                <input type="number" id="stock" name="stock" value="{{ old('stock', 0) }}" 
                       class="w-full px-4 py-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500 {{ $errors->has('stock') ? 'border-red-500' : '' }}">
                @error('stock')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <!-- Category -->
        <div class="mb-4">
            <label for="category" class="block text-sm font-medium text-gray-700 mb-1">Category</label>
            <input type="text" id="category" name="category" value="{{ old('category') }}" 
                   class="w-full px-4 py-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500">
            @error('category')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Description -->
        <div class="mb-4">
            <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
            <textarea id="description" name="description" rows="4" 
                      class="w-full px-4 py-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500">{{ old('description') }}</textarea>
            @error('description')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Image -->
        <div class="mb-4">
            <label for="image" class="block text-sm font-medium text-gray-700 mb-1">Product Image</label>
            <input type="file" id="image" name="image" accept="image/*" 
                   class="w-full px-4 py-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500"
                   onchange="previewImage(event)">
            @error('image')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
            <div id="imagePreview" class="mt-2"></div>
        </div>

        <!-- Status -->
        <div class="mb-6">
            <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status *</label>
            <select id="status" name="status" 
                    class="w-full px-4 py-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500">
                <option value="active" {{ old('status') === 'active' ? 'selected' : '' }}>Active</option>
                <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
            </select>
            @error('status')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Buttons -->
        <div class="flex gap-4">
            <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                <i class="fas fa-save mr-2"></i>Save Product
            </button>
            <a href="{{ route('admin.products.index') }}" class="px-6 py-2 bg-gray-600 text-white rounded hover:bg-gray-700">
                <i class="fas fa-times mr-2"></i>Cancel
            </a>
        </div>
    </form>
</div>

<script>
function previewImage(event) {
    const reader = new FileReader();
    reader.onload = function() {
        const preview = document.getElementById('imagePreview');
        preview.innerHTML = '<img src="' + reader.result + '" alt="Preview" class="w-32 h-32 object-cover rounded">';
    };
    reader.readAsDataURL(event.target.files[0]);
}
</script>
@endsection
