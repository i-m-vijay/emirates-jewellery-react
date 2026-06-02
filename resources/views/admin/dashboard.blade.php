@extends('layouts.admin')

@section('title', 'Dashboard')
@section('header', 'Dashboard')

@section('content')
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <!-- Total Products -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-600 text-sm font-medium">Total Products</p>
                <p class="text-3xl font-bold text-gray-900 mt-2">{{ $totalProducts }}</p>
            </div>
            <div class="bg-blue-100 rounded-full p-3">
                <i class="fas fa-box text-blue-600 text-2xl"></i>
            </div>
        </div>
    </div>

    <!-- Active Products -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-600 text-sm font-medium">Active Products</p>
                <p class="text-3xl font-bold text-gray-900 mt-2">{{ $activeProducts }}</p>
            </div>
            <div class="bg-green-100 rounded-full p-3">
                <i class="fas fa-check-circle text-green-600 text-2xl"></i>
            </div>
        </div>
    </div>

    <!-- Out of Stock -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-600 text-sm font-medium">Out of Stock</p>
                <p class="text-3xl font-bold text-gray-900 mt-2">{{ $outOfStock }}</p>
            </div>
            <div class="bg-red-100 rounded-full p-3">
                <i class="fas fa-exclamation-circle text-red-600 text-2xl"></i>
            </div>
        </div>
    </div>
</div>

<!-- Recent Products -->
<div class="bg-white rounded-lg shadow">
    <div class="px-6 py-4 border-b border-gray-200">
        <h3 class="text-lg font-semibold text-gray-900">Recent Products</h3>
    </div>

    @if ($recentProducts->count() > 0)
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-gray-200 bg-gray-50">
                        <th class="px-6 py-3 text-left text-sm font-medium text-gray-700">Name</th>
                        <th class="px-6 py-3 text-left text-sm font-medium text-gray-700">Category</th>
                        <th class="px-6 py-3 text-left text-sm font-medium text-gray-700">Price</th>
                        <th class="px-6 py-3 text-left text-sm font-medium text-gray-700">Stock</th>
                        <th class="px-6 py-3 text-left text-sm font-medium text-gray-700">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($recentProducts as $product)
                        <tr class="border-b border-gray-200 hover:bg-gray-50">
                            <td class="px-6 py-4 text-sm text-gray-900">{{ $product->name }}</td>
                            <td class="px-6 py-4 text-sm text-gray-600">{{ $product->category ?? '-' }}</td>
                            <td class="px-6 py-4 text-sm font-semibold text-gray-900">${{ number_format($product->price, 2) }}</td>
                            <td class="px-6 py-4 text-sm text-gray-600">{{ $product->stock }}</td>
                            <td class="px-6 py-4 text-sm">
                                <span class="px-3 py-1 rounded-full text-xs font-medium {{ $product->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ ucfirst($product->status) }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="px-6 py-8 text-center text-gray-500">
            <i class="fas fa-inbox text-3xl mb-2"></i>
            <p>No products yet. <a href="{{ route('admin.products.create') }}" class="text-blue-600 hover:underline">Create one now</a></p>
        </div>
    @endif
</div>
@endsection
