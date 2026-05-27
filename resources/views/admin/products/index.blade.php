@extends('layouts.admin')

@section('title', 'Products')
@section('header', 'Products')

@section('content')
<div class="mb-6 flex justify-between items-center">
    <div class="flex-1 mr-4">
        <form method="GET" action="{{ route('admin.products.index') }}" class="flex gap-2">
            <input type="text" name="search" placeholder="Search by name or category..." 
                   value="{{ request('search') }}" class="flex-1 px-4 py-2 border border-gray-300 rounded">
            <button type="submit" class="px-4 py-2 bg-gray-800 text-white rounded hover:bg-gray-700">
                <i class="fas fa-search mr-2"></i>Search
            </button>
        </form>
    </div>
    <div class="flex gap-2">
        <a href="{{ route('admin.products.import.form') }}" class="px-6 py-2 bg-green-600 text-white rounded hover:bg-green-700">
            <i class="fas fa-file-import mr-2"></i>Import CSV
        </a>
        <a href="{{ route('admin.products.create') }}" class="px-6 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
            <i class="fas fa-plus mr-2"></i>Add Product
        </a>
    </div>
</div>

<div class="bg-white rounded-lg shadow overflow-hidden">
    @if ($products->count() > 0)
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-gray-200 bg-gray-50">
                        <th class="px-6 py-3 text-left text-sm font-medium text-gray-700">#</th>
                        <th class="px-6 py-3 text-left text-sm font-medium text-gray-700">Image</th>
                        <th class="px-6 py-3 text-left text-sm font-medium text-gray-700">Name</th>
                        <th class="px-6 py-3 text-left text-sm font-medium text-gray-700">Category</th>
                        <th class="px-6 py-3 text-left text-sm font-medium text-gray-700">Price</th>
                        <th class="px-6 py-3 text-left text-sm font-medium text-gray-700">Stock</th>
                        <th class="px-6 py-3 text-left text-sm font-medium text-gray-700">Status</th>
                        <th class="px-6 py-3 text-center text-sm font-medium text-gray-700">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($products as $product)
                        <tr class="border-b border-gray-200 hover:bg-gray-50">
                            <td class="px-6 py-4 text-sm text-gray-600">{{ $loop->iteration }}</td>
                            <td class="px-6 py-4">
                                @if ($product->image)
                                    <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" 
                                         class="w-10 h-10 rounded object-cover">
                                @else
                                    <div class="w-10 h-10 bg-gray-200 rounded flex items-center justify-center">
                                        <i class="fas fa-image text-gray-400"></i>
                                    </div>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900 font-medium">{{ $product->name }}</td>
                            <td class="px-6 py-4 text-sm text-gray-600">{{ $product->category ?? '-' }}</td>
                            <td class="px-6 py-4 text-sm font-semibold text-gray-900">${{ number_format($product->price, 2) }}</td>
                            <td class="px-6 py-4 text-sm text-gray-600">
                                <span class="px-2 py-1 rounded {{ $product->stock > 0 ? 'bg-blue-100 text-blue-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $product->stock }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm">
                                <span class="px-3 py-1 rounded-full text-xs font-medium {{ $product->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ ucfirst($product->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex gap-2 justify-center">
                                    <a href="{{ route('admin.products.show', $product->slug) }}" 
                                       class="text-blue-600 hover:text-blue-800" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.products.edit', $product->slug) }}" 
                                       class="text-yellow-600 hover:text-yellow-800" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form method="POST" action="{{ route('admin.products.destroy', $product->slug) }}" 
                                          style="display:inline;" onsubmit="return confirm('Delete this product?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-800" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $products->links() }}
        </div>
    @else
        <div class="px-6 py-12 text-center">
            <i class="fas fa-inbox text-4xl text-gray-400 mb-3"></i>
            <p class="text-gray-500 mb-4">No products found.</p>
            <a href="{{ route('admin.products.create') }}" class="text-blue-600 hover:underline">Create your first product</a>
        </div>
    @endif
</div>
@endsection
