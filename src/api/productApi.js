import { apiService } from '../services/apiService';
import { API_URLS } from './apiUrls';

export const fetchProducts = (category = null, search = null, perPage = 12) => {
  const params = { per_page: perPage };
  if (category) params.category = category;
  if (search) params.search = search;
  return apiService.get(API_URLS.PRODUCTS.LIST, params);
};

export const fetchProductDetail = (productId) =>
  apiService.get(API_URLS.PRODUCTS.DETAIL(productId));

export const fetchFeaturedProducts = () =>
  apiService.get(API_URLS.PRODUCTS.FEATURED);
