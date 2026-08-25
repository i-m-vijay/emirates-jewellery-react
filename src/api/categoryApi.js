import { apiService } from '../services/apiService';
import { API_URLS } from './apiUrls';

export const fetchJewelleryCategories = (perCategory = 5) =>
  apiService.get(API_URLS.CATEGORIES.JEWELLERY, {
    include_products: true,
    per_category: perCategory,
  });

export const fetchCategoryProducts = (slug) =>
  apiService.get(API_URLS.CATEGORIES.JEWELLERY_BY_SLUG(slug));

export const fetchCategoriesByMetal = (metalType) =>
  apiService.get(API_URLS.CATEGORIES.BY_METAL, { metal_type: metalType });

export const fetchCategoryProductsByMetal = (category, metalType) => {
  const params = metalType ? { metal_type: metalType } : {};
  return apiService.get(API_URLS.CATEGORIES.JEWELLERY_BY_CATEGORY(category), params);
};

export const fetchCategoriesByMetalType = (metalType) => {
  const params = metalType ? { metal_type: metalType } : {};
  return apiService.get(API_URLS.CATEGORIES.JEWELLERY, params);
};

export const fetchSearchResults = (query, page = 1, perPage = 20) =>
  apiService.get(API_URLS.CATEGORIES.JEWELLERY_SEARCH, { q: query, page, per_page: perPage });

export const fetchJewelleryByPrice = (metalType, minPrice, maxPrice) => {
  const params = {};
  if (metalType) params.metal_type = metalType;
  if (minPrice != null && minPrice !== '') params.min_price = minPrice;
  if (maxPrice != null && maxPrice !== '') params.max_price = maxPrice;
  return apiService.get(API_URLS.CATEGORIES.BY_PRICE, params);
};

export const fetchJewelleryByGender = (metalType, gender) => {
  const params = {};
  if (metalType) params.metal_type = metalType;
  if (gender) params.gender = gender;
  return apiService.get(API_URLS.CATEGORIES.BY_GENDER, params);
};

export const fetchJewelleryOffers = () =>
  apiService.get(API_URLS.CATEGORIES.OFFERS);

export const fetchAllJewellery = (page = 1, perPage = 50, jewelleryType = '') => {
  const params = { page, per_page: perPage };
  if (jewelleryType) params.jewellery_type = jewelleryType;
  return apiService.get(API_URLS.CATEGORIES.ALL_JEWELLERY, params);
};
