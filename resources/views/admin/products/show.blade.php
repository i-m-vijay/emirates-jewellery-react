@extends('layouts.admin')

@section('title', 'Product Details')
@section('header', 'Product Details')

@section('content')
<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    <!-- Product Image -->
    <div class="md:col-span-1">
        <div class="bg-white rounded-lg shadow p-6">
            @if ($product->image)
                <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" 
                     class="w-full h-64 object-cover rounded mb-4">
            @else
                <div class="w-full h-64 bg-gray-200 rounded mb-4 flex items-center justify-center">
                    <i class="fas fa-image text-gray-400 text-4xl"></i>
                </div>
            @endif
            <div class="space-y-2">
                <a href="{{ route('admin.products.edit', $product->slug) }}" 
                   class="block w-full px-4 py-2 bg-yellow-600 text-white rounded hover:bg-yellow-700 text-center">
                    <i class="fas fa-edit mr-2"></i>Edit
                </a>
                <form method="POST" action="{{ route('admin.products.destroy', $product->slug) }}" onsubmit="return confirm('Delete this product?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="w-full px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">
                        <i class="fas fa-trash mr-2"></i>Delete
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Product Details -->
    <div class="md:col-span-2">
        <div class="bg-white rounded-lg shadow p-6">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ $product->name }}</h1>
            
            <div class="flex items-center mb-6">
                <span class="px-3 py-1 rounded-full text-sm font-medium {{ $product->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                    {{ ucfirst($product->status) }}
                </span>
            </div>

            <div class="grid grid-cols-2 gap-6 mb-6 pb-6 border-b border-gray-200">
                <div>
                    <p class="text-gray-600 text-sm">Price</p>
                    <p class="text-3xl font-bold text-gray-900">${{ number_format($product->price, 2) }}</p>
                </div>
                <div>
                    <p class="text-gray-600 text-sm">Stock Quantity</p>
                    <p class="text-3xl font-bold {{ $product->stock > 0 ? 'text-green-600' : 'text-red-600' }}">
                        {{ $product->stock }}
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-6 mb-6 pb-6 border-b border-gray-200">
                <div>
                    <p class="text-gray-600 text-sm font-medium mb-1">Category</p>
                    <p class="text-gray-900">{{ $product->category ?? 'N/A' }}</p>
                </div>
                <div>
                    <p class="text-gray-600 text-sm font-medium mb-1">Product Slug</p>
                    <p class="text-gray-900 font-mono">{{ $product->slug }}</p>
                </div>
            </div>

            @if ($product->description)
                <div class="mb-6">
                    <p class="text-gray-600 text-sm font-medium mb-2">Description</p>
                    <p class="text-gray-900">{{ $product->description }}</p>
                </div>
            @endif

            <div class="grid grid-cols-2 gap-6 text-sm text-gray-600">
                <div>
                    <p class="font-medium">Created</p>
                    <p>{{ $product->created_at->format('M d, Y - H:i') }}</p>
                </div>
                <div>
                    <p class="font-medium">Last Updated</p>
                    <p>{{ $product->updated_at->format('M d, Y - H:i') }}</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
