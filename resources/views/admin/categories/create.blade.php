@extends('layouts.admin')

@section('title', 'Create Category')
@section('header', 'Create Category')

@section('content')
<div class="bg-white rounded-lg shadow p-6 max-w-lg">
    <form method="POST" action="{{ route('admin.categories.store') }}">
        @csrf

        <div class="mb-6">
            <label for="category_name" class="block text-sm font-medium text-gray-700 mb-1">Category Name *</label>
            <input type="text" id="category_name" name="category_name" value="{{ old('category_name') }}"
                   placeholder="e.g. Gold, Diamond, Collections..."
                   class="w-full px-4 py-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500 {{ $errors->has('category_name') ? 'border-red-500' : '' }}">
            @error('category_name')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex gap-4">
            <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                <i class="fas fa-save mr-2"></i>Save Category
            </button>
            <a href="{{ route('admin.categories.index') }}" class="px-6 py-2 bg-gray-600 text-white rounded hover:bg-gray-700">
                <i class="fas fa-times mr-2"></i>Cancel
            </a>
        </div>
    </form>
</div>
@endsection
