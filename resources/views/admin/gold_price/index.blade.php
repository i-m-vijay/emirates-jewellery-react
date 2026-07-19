@extends('layouts.admin')

@section('title', 'Metal Price')
@section('header', 'Update Metal Price')

@section('content')
<div class="max-w-2xl">

    {{-- Form Card --}}
    <div class="bg-white rounded-lg shadow overflow-hidden mb-6">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex items-center gap-3">
            <div class="w-8 h-8 bg-yellow-100 rounded-full flex items-center justify-center">
                <i class="fas fa-coins text-yellow-600 text-sm"></i>
            </div>
            <h2 class="text-base font-semibold text-gray-800">Update Today's Metal Price</h2>
        </div>

        <div class="p-6">

            @if (session('success'))
                <div class="mb-5 flex items-center gap-2 px-4 py-3 bg-green-50 border border-green-200 text-green-700 rounded-lg text-sm">
                    <i class="fas fa-check-circle"></i>
                    {{ session('success') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-5 px-4 py-3 bg-red-50 border border-red-200 text-red-700 rounded-lg text-sm">
                    <div class="flex items-center gap-2 mb-1 font-medium">
                        <i class="fas fa-exclamation-circle"></i> Please fix the following errors:
                    </div>
                    <ul class="list-disc list-inside space-y-0.5 mt-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('admin.gold-price.update') }}" method="POST">
                @csrf

                <div class="mb-5">
                    <label for="metal_type" class="block text-sm font-medium text-gray-700 mb-1">
                        Metal Type <span class="text-red-500">*</span>
                    </label>
                    <select name="metal_type" id="metal_type" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500 text-sm {{ $errors->has('metal_type') ? 'border-red-400' : '' }}">
                        <!-- <option value="">-- Select Metal --</option>
                        <option value="Gold"    {{ old('metal_type') == 'Gold'    ? 'selected' : '' }}>Gold</option>
                        <option value="Diamond" {{ old('metal_type') == 'Diamond' ? 'selected' : '' }}>Diamond</option> -->
                        <option value="Gold"    selected >Gold</option>
                    </select>
                    @error('metal_type')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-6">
                    <label for="per_gram_price" class="block text-sm font-medium text-gray-700 mb-1">
                        Price Per Gram ($) <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 text-sm font-medium">$</span>
                        <input type="number" step="0.01" min="0"
                               name="per_gram_price" id="per_gram_price"
                               value="{{ old('per_gram_price') }}"
                               placeholder="e.g. 7250.00" required
                               class="w-full pl-8 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500 text-sm {{ $errors->has('per_gram_price') ? 'border-red-400' : '' }}">
                    </div>
                    @error('per_gram_price')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                    <p class="text-gray-500 text-xs mt-1.5">
                        <i class="fas fa-info-circle mr-1"></i>
                        This price will be multiplied by each product's weight to update its selling price.
                    </p>
                </div>

                <button type="submit"
                        class="inline-flex items-center gap-2 px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition">
                    <i class="fas fa-sync-alt"></i>
                    Update Prices
                </button>
            </form>
        </div>
    </div>

    {{-- History Table --}}
    @if ($latestPrices->isNotEmpty())
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex items-center gap-3">
                <div class="w-8 h-8 bg-blue-50 rounded-full flex items-center justify-center">
                    <i class="fas fa-history text-blue-500 text-sm"></i>
                </div>
                <h2 class="text-base font-semibold text-gray-800">Recent Price Updates</h2>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 bg-gray-50 text-left">
                            <th class="px-5 py-3 font-medium text-gray-600">Metal</th>
                            <th class="px-5 py-3 font-medium text-gray-600">Per Gram ($)</th>
                            <th class="px-5 py-3 font-medium text-gray-600">Products Updated</th>
                            <th class="px-5 py-3 font-medium text-gray-600">Updated At</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($latestPrices as $price)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-5 py-3">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium
                                        {{ $price->metal_type === 'Gold' ? 'bg-yellow-100 text-yellow-800' : 'bg-blue-100 text-blue-800' }}">
                                        <i class="fas {{ $price->metal_type === 'Gold' ? 'fa-coins' : 'fa-gem' }} text-xs"></i>
                                        {{ $price->metal_type }}
                                    </span>
                                </td>
                                <td class="px-5 py-3 font-medium text-gray-800">
                                    ${{ number_format($price->per_gram_price, 2) }}
                                </td>
                                <td class="px-5 py-3 text-gray-700">
                                    <span class="inline-flex items-center gap-1">
                                        <i class="fas fa-box text-gray-400 text-xs"></i>
                                        {{ number_format($price->products_updated) }}
                                    </span>
                                </td>
                                <td class="px-5 py-3 text-gray-500">
                                    <span title="{{ $price->updated_at }}">
                                        {{ $price->updated_at->format('d M Y, h:i A') }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

</div>
@endsection
