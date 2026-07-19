<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Panel') - Emirates</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('emirates-logo.svg') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50">
    <div class="flex h-screen">
        <!-- Sidebar -->
        <div class="w-64 bg-gray-900 text-white">
            <div class="p-5 border-b border-gray-800 flex items-center gap-3">
                <img src="{{ asset('emirates-logo.svg') }}" alt="Emirates Logo" class="w-10 h-10 rounded-lg">
                <div>
                    <p class="text-xs text-gray-400 leading-none">Admin Panel</p>
                    <h1 class="text-base font-bold leading-tight">Emirates</h1>
                </div>
            </div>

            <nav class="mt-6">
                <a href="{{ route('admin.dashboard') }}" 
                   class="px-6 py-3 flex items-center text-gray-300 hover:bg-gray-800 transition {{ request()->routeIs('admin.dashboard') ? 'bg-gray-800 text-white' : '' }}">
                    <i class="fas fa-chart-line mr-3"></i>
                    Dashboard
                </a>
                <a href="{{ route('admin.products.index') }}"
                   class="px-6 py-3 flex items-center text-gray-300 hover:bg-gray-800 transition {{ request()->routeIs('admin.products.*') ? 'bg-gray-800 text-white' : '' }}">
                    <i class="fas fa-box mr-3"></i>
                    Products
                </a>
                <a href="{{ route('admin.product-details.index') }}"
                   class="px-6 py-3 flex items-center text-gray-300 hover:bg-gray-800 transition {{ request()->routeIs('admin.product-details.*') ? 'bg-gray-800 text-white' : '' }}">
                    <i class="fas fa-gem mr-3"></i>
                    Product Details
                </a>
                <a href="{{ route('admin.gold-price.index') }}"
                   class="px-6 py-3 flex items-center text-gray-300 hover:bg-gray-800 transition {{ request()->routeIs('admin.gold-price.*') ? 'bg-gray-800 text-white' : '' }}">
                    <i class="fas fa-coins mr-3"></i>
                    Gold Price
                </a>
                <a href="{{ route('admin.categories.index') }}"
                   class="px-6 py-3 flex items-center text-gray-300 hover:bg-gray-800 transition {{ request()->routeIs('admin.categories.*') ? 'bg-gray-800 text-white' : '' }}">
                    <i class="fas fa-tags mr-3"></i>
                    Categories
                </a>
                <a href="{{ route('admin.subcategories.index') }}"
                   class="px-6 py-3 pl-10 flex items-center text-gray-300 hover:bg-gray-800 transition {{ request()->routeIs('admin.subcategories.*') ? 'bg-gray-800 text-white' : '' }}">
                    <i class="fas fa-layer-group mr-3"></i>
                    Subcategories
                </a>
            </nav>

            <div class="absolute bottom-0 left-0 right-0 border-t border-gray-800 w-64">
                <form method="POST" action="{{ route('logout') }}" class="p-6">
                    @csrf
                    <button type="submit" class="w-full px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded transition">
                        <i class="fas fa-sign-out-alt mr-2"></i> Logout
                    </button>
                </form>
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col">
            <!-- Navbar -->
            <nav class="bg-white border-b border-gray-200 px-6 py-4 flex justify-between items-center">
                <h2 class="text-xl font-semibold text-gray-800">@yield('header', 'Dashboard')</h2>
                <div class="flex items-center">
                    <span class="text-gray-700 mr-4">{{ Auth::user()->name }}</span>
                    <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}&background=random" 
                         alt="Avatar" class="w-10 h-10 rounded-full">
                </div>
            </nav>

            <!-- Page Content -->
            <div class="flex-1 overflow-auto p-6">
                <!-- Flash Messages -->
                @if ($message = Session::get('success'))
                    <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
                        <i class="fas fa-check-circle mr-2"></i>{{ $message }}
                    </div>
                @endif

                @if ($message = Session::get('error'))
                    <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                        <i class="fas fa-exclamation-circle mr-2"></i>{{ $message }}
                    </div>
                @endif

                @yield('content')
            </div>
        </div>
    </div>
</body>
</html>
