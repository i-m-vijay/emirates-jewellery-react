@extends('layouts.admin')

@section('title', 'Categories')
@section('header', 'Categories')

@section('content')
<div class="mb-6 flex justify-between items-center">
    <div class="flex-1 mr-4">
        <form method="GET" action="{{ route('admin.categories.index') }}" class="flex gap-2">
            <input type="text" name="search" placeholder="Search categories..."
                   value="{{ request('search') }}" class="flex-1 px-4 py-2 border border-gray-300 rounded">
            <button type="submit" class="px-4 py-2 bg-gray-800 text-white rounded hover:bg-gray-700">
                <i class="fas fa-search mr-2"></i>Search
            </button>
            @if(request('search'))
                <a href="{{ route('admin.categories.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300">
                    Clear
                </a>
            @endif
        </form>
    </div>
    <a href="{{ route('admin.categories.create') }}" class="px-6 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
        <i class="fas fa-plus mr-2"></i>Add Category
    </a>
</div>

<div class="bg-white rounded-lg shadow overflow-hidden">
    @if ($categories->count() > 0)
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-gray-200 bg-gray-50">
                        <th class="px-6 py-3 text-left text-sm font-medium text-gray-700">#</th>
                        <th class="px-6 py-3 text-left text-sm font-medium text-gray-700">Category Name</th>
                        <th class="px-6 py-3 text-left text-sm font-medium text-gray-700">Created At</th>
                        <th class="px-6 py-3 text-center text-sm font-medium text-gray-700">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($categories as $category)
                        <tr class="border-b border-gray-200 hover:bg-gray-50">
                            <td class="px-6 py-4 text-sm text-gray-600">{{ $loop->iteration + ($categories->currentPage() - 1) * $categories->perPage() }}</td>
                            <td class="px-6 py-4 text-sm text-gray-900 font-medium">
                                <div class="flex items-center">
                                    <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center mr-3">
                                        <i class="fas fa-tag text-blue-600 text-xs"></i>
                                    </div>
                                    {{ $category->category_name }}
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ $category->created_at->format('M d, Y') }}</td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex gap-3 justify-center">
                                    <a href="{{ route('admin.categories.edit', $category) }}"
                                       class="text-yellow-600 hover:text-yellow-800" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form method="POST" action="{{ route('admin.categories.destroy', $category) }}"
                                          style="display:inline;" onsubmit="return confirm('Delete this category?');">
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
            {{ $categories->links() }}
        </div>
    @else
        <div class="px-6 py-12 text-center">
            <i class="fas fa-tags text-4xl text-gray-400 mb-3"></i>
            <p class="text-gray-500 mb-4">No categories found.</p>
            <a href="{{ route('admin.categories.create') }}" class="text-blue-600 hover:underline">Create your first category</a>
        </div>
    @endif
</div>
@endsection
