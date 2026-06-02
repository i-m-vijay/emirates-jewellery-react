@extends('layouts.admin')

@section('title', 'Create Subcategory')
@section('header', 'Create Subcategory')

@section('content')
<div class="bg-white rounded-lg shadow p-6 max-w-lg">
    <form method="POST" action="{{ route('admin.subcategories.store') }}" enctype="multipart/form-data">
        @csrf

        {{-- Category --}}
        <div class="mb-4">
            <label for="category_id" class="block text-sm font-medium text-gray-700 mb-1">Category *</label>
            <select id="category_id" name="category_id"
                    class="w-full px-4 py-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500 {{ $errors->has('category_id') ? 'border-red-500' : '' }}">
                <option value="">— Select Category —</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                        {{ $category->category_name }}
                    </option>
                @endforeach
            </select>
            @error('category_id')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        {{-- Name --}}
        <div class="mb-4">
            <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Subcategory Name *</label>
            <input type="text" id="name" name="name" value="{{ old('name') }}"
                   placeholder="e.g. Gold Rings, Diamond Necklaces..."
                   class="w-full px-4 py-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500 {{ $errors->has('name') ? 'border-red-500' : '' }}">
            @error('name')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        {{-- Image --}}
        <div class="mb-6">
            <label for="image" class="block text-sm font-medium text-gray-700 mb-1">Image</label>
            <input type="file" id="image" name="image" accept="image/*"
                   class="w-full px-4 py-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500 {{ $errors->has('image') ? 'border-red-500' : '' }}"
                   onchange="previewImage(event)">
            @error('image')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
            <div id="imagePreview" class="mt-3"></div>
        </div>

        <div class="flex gap-4">
            <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                <i class="fas fa-save mr-2"></i>Save Subcategory
            </button>
            <a href="{{ route('admin.subcategories.index') }}" class="px-6 py-2 bg-gray-600 text-white rounded hover:bg-gray-700">
                <i class="fas fa-times mr-2"></i>Cancel
            </a>
        </div>
    </form>
</div>

<script>
function previewImage(event) {
    if (event.target.files.length > 0) {
        const reader = new FileReader();
        reader.onload = function () {
            document.getElementById('imagePreview').innerHTML =
                '<img src="' + reader.result + '" alt="Preview" class="w-32 h-32 object-cover rounded border">';
        };
        reader.readAsDataURL(event.target.files[0]);
    }
}
</script>
@endsection
