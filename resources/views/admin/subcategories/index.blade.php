@extends('layouts.admin')

@section('title', 'Subcategories')
@section('header', 'Subcategories')

@section('content')
<div class="mb-6 flex justify-between items-center gap-4">
    <form method="GET" action="{{ route('admin.subcategories.index') }}" class="flex flex-1 gap-2">
        <input type="text" name="search" placeholder="Search by name or category..."
               value="{{ request('search') }}" class="flex-1 px-4 py-2 border border-gray-300 rounded">

        <select name="category_id" class="px-4 py-2 border border-gray-300 rounded">
            <option value="">All Categories</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                    {{ $category->category_name }}
                </option>
            @endforeach
        </select>

        <button type="submit" class="px-4 py-2 bg-gray-800 text-white rounded hover:bg-gray-700">
            <i class="fas fa-search mr-2"></i>Search
        </button>
        @if(request('search') || request('category_id'))
            <a href="{{ route('admin.subcategories.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300">
                Clear
            </a>
        @endif
    </form>

    <a href="{{ route('admin.subcategories.create') }}" class="px-6 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 whitespace-nowrap">
        <i class="fas fa-plus mr-2"></i>Add Subcategory
    </a>
</div>

<div class="bg-white rounded-lg shadow overflow-hidden">
    @if ($subcategories->count() > 0)
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-gray-200 bg-gray-50">
                        <th class="px-6 py-3 text-left text-sm font-medium text-gray-700">#</th>
                        <th class="px-6 py-3 text-left text-sm font-medium text-gray-700">Image</th>
                        <th class="px-6 py-3 text-left text-sm font-medium text-gray-700">Subcategory Name</th>
                        <th class="px-6 py-3 text-left text-sm font-medium text-gray-700">Category</th>
                        <th class="px-6 py-3 text-left text-sm font-medium text-gray-700">Created At</th>
                        <th class="px-6 py-3 text-center text-sm font-medium text-gray-700">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($subcategories as $subcategory)
                        <tr class="border-b border-gray-200 hover:bg-gray-50">
                            <td class="px-6 py-4 text-sm text-gray-600">
                                {{ $loop->iteration + ($subcategories->currentPage() - 1) * $subcategories->perPage() }}
                            </td>
                            <td class="px-6 py-4">
                                @if ($subcategory->image)
                                    <img src="{{ asset('storage/' . $subcategory->image) }}"
                                         alt="{{ $subcategory->name }}"
                                         class="w-12 h-12 rounded object-cover">
                                @else
                                    <div class="w-12 h-12 bg-gray-100 rounded flex items-center justify-center">
                                        <i class="fas fa-image text-gray-400"></i>
                                    </div>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900 font-medium">{{ $subcategory->name }}</td>
                            <td class="px-6 py-4 text-sm">
                                <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded text-xs font-medium">
                                    {{ $subcategory->category->category_name }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ $subcategory->created_at->format('M d, Y') }}</td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex gap-3 justify-center">
                                    <a href="{{ route('admin.subcategories.edit', $subcategory) }}"
                                       class="text-yellow-600 hover:text-yellow-800" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form method="POST" action="{{ route('admin.subcategories.destroy', $subcategory) }}"
                                          style="display:inline;" onsubmit="return confirm('Delete this subcategory?');">
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

        <div class="px-6 py-4 border-t border-gray-200">
            {{ $subcategories->appends(request()->query())->links() }}
        </div>
    @else
        <div class="px-6 py-12 text-center">
            <i class="fas fa-layer-group text-4xl text-gray-400 mb-3"></i>
            <p class="text-gray-500 mb-4">No subcategories found.</p>
            <a href="{{ route('admin.subcategories.create') }}" class="text-blue-600 hover:underline">Create your first subcategory</a>
        </div>
    @endif
</div>
@endsection
