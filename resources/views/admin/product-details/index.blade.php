@extends('layouts.admin')

@section('title', 'Product Details')
@section('header', 'Product Details')

@section('content')
{{-- Filters --}}
<form method="GET" action="{{ route('admin.product-details.index') }}" class="mb-6 bg-white rounded-lg shadow p-4">
    <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
        <input type="text" name="search" placeholder="Search by name, SKU, brand..."
               value="{{ request('search') }}"
               class="px-4 py-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500">

        <select name="category_id" class="px-4 py-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500" onchange="this.form.submit()">
            <option value="">All Categories</option>
            @foreach ($categories as $cat)
                <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>
                    {{ $cat->category_name }}
                </option>
            @endforeach
        </select>

        <select name="subcategory_id" class="px-4 py-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500">
            <option value="">All Subcategories</option>
            @foreach ($subcategories as $sub)
                <option value="{{ $sub->id }}" {{ request('subcategory_id') == $sub->id ? 'selected' : '' }}>
                    {{ $sub->name }}
                </option>
            @endforeach
        </select>

        <select name="in_stock" class="px-4 py-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500">
            <option value="">All Stock</option>
            <option value="1" {{ request('in_stock') === '1' ? 'selected' : '' }}>In Stock</option>
            <option value="0" {{ request('in_stock') === '0' ? 'selected' : '' }}>Out of Stock</option>
        </select>
    </div>
    <div class="flex gap-2 mt-3">
        <button type="submit" class="px-5 py-2 bg-gray-800 text-white rounded hover:bg-gray-700">
            <i class="fas fa-search mr-2"></i>Search
        </button>
        @if(request()->hasAny(['search','category_id','subcategory_id','in_stock']))
            <a href="{{ route('admin.product-details.index') }}" class="px-5 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300">
                Clear
            </a>
        @endif
        <span class="ml-auto self-center text-sm text-gray-500">
            {{ $productDetails->total() }} product(s) found
        </span>
    </div>
</form>

{{-- Table --}}
<div class="bg-white rounded-lg shadow overflow-hidden">
    @if ($productDetails->count() > 0)
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-200 bg-gray-50 text-left">
                        <th class="px-4 py-3 font-medium text-gray-700">#</th>
                        <th class="px-4 py-3 font-medium text-gray-700">SKU</th>
                        <th class="px-4 py-3 font-medium text-gray-700">Name</th>
                        <th class="px-4 py-3 font-medium text-gray-700">Category</th>
                        <th class="px-4 py-3 font-medium text-gray-700">Subcategory</th>
                        <th class="px-4 py-3 font-medium text-gray-700">Price</th>
                        <th class="px-4 py-3 font-medium text-gray-700">Stock</th>
                        <th class="px-4 py-3 font-medium text-gray-700 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($productDetails as $pd)
                        <tr class="border-b border-gray-100 hover:bg-gray-50">
                            <td class="px-4 py-3 text-gray-500">
                                {{ $loop->iteration + ($productDetails->currentPage() - 1) * $productDetails->perPage() }}
                            </td>
                            <td class="px-4 py-3 font-mono text-xs text-gray-600">{{ $pd->sku ?? '—' }}</td>
                            <td class="px-4 py-3 font-medium text-gray-900 max-w-xs truncate" title="{{ $pd->name }}">
                                {{ $pd->name ?? '—' }}
                            </td>
                            <td class="px-4 py-3">
                                @if ($pd->category)
                                    <span class="px-2 py-0.5 bg-blue-100 text-blue-800 rounded text-xs font-medium">
                                        {{ $pd->category->category_name }}
                                    </span>
                                @else
                                    <span class="text-gray-400 text-xs">Unassigned</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                @if ($pd->subcategory)
                                    <span class="px-2 py-0.5 bg-purple-100 text-purple-800 rounded text-xs font-medium">
                                        {{ $pd->subcategory->name }}
                                    </span>
                                @else
                                    <span class="text-gray-400 text-xs">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-gray-900 font-semibold">
                                @if ($pd->regular_price)
                                    AED {{ number_format($pd->regular_price, 2) }}
                                @else
                                    —
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-0.5 rounded text-xs font-medium {{ $pd->in_stock ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $pd->in_stock ? 'In Stock' : 'Out' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <a href="{{ route('admin.product-details.edit', $pd->record_id) }}"
                                   class="inline-flex items-center px-3 py-1 bg-yellow-500 text-white rounded hover:bg-yellow-600 text-xs">
                                    <i class="fas fa-edit mr-1"></i>Edit
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="px-6 py-4 border-t border-gray-200">
            {{ $productDetails->appends(request()->query())->links() }}
        </div>
    @else
        <div class="py-16 text-center">
            <i class="fas fa-box-open text-5xl text-gray-300 mb-4"></i>
            <p class="text-gray-500">No product details found.</p>
        </div>
    @endif
</div>
@endsection
