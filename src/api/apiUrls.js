export const BASE_URL = import.meta.env.VITE_API_URL || 'http://localhost:8000/api';

export const STORAGE_URL = import.meta.env.VITE_STORAGE_URL || 'http://localhost:8000/storage';

export const API_URLS = {
  // Products
  PRODUCTS: {
    LIST: '/products',
    DETAIL: (id) => `/products/${id}`,
    FEATURED: '/products/featured',
    BY_CATEGORY: (category) => `/products?category=${category}`,
  },

  // Categories
  CATEGORIES: {
    LIST: '/categories',
    DETAIL: (id) => `/categories/${id}`,
    JEWELLERY: '/jewellery-categories',
    JEWELLERY_BY_SLUG: (slug) => `/jewellery-categories/${slug}`,
    JEWELLERY_BY_CATEGORY: (category) => `/jewellery-categories/${encodeURIComponent(category)}`,
    BY_METAL: '/jewellery/categories-by-metal',
    ALL_JEWELLERY: '/jewellery/browse',
    JEWELLERY_SEARCH: '/jewellery/search',
    BY_PRICE: '/jewellery/by-price',
    BY_GENDER: '/jewellery/by-gender',
    OFFERS: '/jewellery/offers',
  },

  // Auth
  AUTH: {
    LOGIN: '/user/login',
    REGISTER: '/user/register',
    LOGOUT: '/user/logout',
    PROFILE: '/user/profile',
    REQUEST_OTP: '/user/request-otp',
    VERIFY_OTP: '/user/verify-otp',
  },

  // Cart
  CART: {
    GET: '/cart',
    ADD: '/cart/add',
    UPDATE: (id) => `/cart/${id}`,
    REMOVE: (id) => `/cart/${id}`,
  },

  // Wishlist
  WISHLIST: {
    GET: '/wishlist',
    ADD: '/wishlist/add',
    REMOVE: (id) => `/wishlist/${id}`,
  },

  // Orders
  ORDERS: {
    LIST: '/orders',
    DETAIL: (id) => `/orders/${id}`,
    CREATE: '/orders',
  },
};
