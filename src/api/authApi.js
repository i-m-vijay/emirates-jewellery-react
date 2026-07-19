import { apiService } from '../services/apiService';
import { API_URLS } from './apiUrls';

export const loginUser = (credentials) =>
  apiService.post(API_URLS.AUTH.LOGIN, credentials);

export const registerUser = (data) =>
  apiService.post(API_URLS.AUTH.REGISTER, data);

export const requestOtp = (contact) =>
  apiService.post(API_URLS.AUTH.REQUEST_OTP, contact);

export const verifyOtp = (data) =>
  apiService.post(API_URLS.AUTH.VERIFY_OTP, data);

export const logoutUser = () =>
  apiService.post(API_URLS.AUTH.LOGOUT, {});
