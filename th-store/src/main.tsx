// src/main.tsx
import React from 'react';
import { createRoot } from 'react-dom/client';
import { createBrowserRouter, RouterProvider } from 'react-router-dom';

import './style.css';

// Layouts
import AppLayout from './ui/AppLayout';
import AdminLayout from './admin/AdminLayout';

// Pages User
import { 
  HomePage, 
  ProductsPage, 
  ProductDetailPage, 
  CartPage, 
  CheckoutPage, 
  LoginPage, 
  RegisterPage 
} from './pages';
import TestApiPage from './pages/TestApi';

// Pages Admin
import Dashboard from './admin/pages/Dashboard';
import AdminProducts from './admin/pages/Products';
import AdminOrders from './admin/pages/Orders';
import AdminUsers from './admin/pages/Users';
import AdminLogin from './admin/pages/Login';
import AdminRegister from './admin/pages/Register';

// Contexts
import { CartProvider } from './store/CartContext';
import { AuthProvider } from './store/AuthContext';
import AdminRoute from './admin/AdminRoute';

const router = createBrowserRouter([
  // User routes
  {
    path: '/',
    element: <AppLayout />,
    children: [
      { index: true, element: <HomePage /> },
      { path: 'san-pham', element: <ProductsPage /> },
      { path: 'san-pham/:id', element: <ProductDetailPage /> },
      { path: 'gio-hang', element: <CartPage /> },
      { path: 'thanh-toan', element: <CheckoutPage /> },
      { path: 'dang-nhap', element: <LoginPage /> },
      { path: 'dang-ky', element: <RegisterPage /> },
      { path: 'test-api', element: <TestApiPage /> },
    ],
  },

  // Admin routes
  {
    path: '/admin',
    element: (
      <AdminRoute>
        <AdminLayout />
      </AdminRoute>
    ),
    children: [
      { path: 'dashboard', element: <Dashboard /> }, // /admin/dashboard
      { path: 'products', element: <AdminProducts /> },
      { path: 'orders', element: <AdminOrders /> },
      { path: 'users', element: <AdminUsers /> },
    ],
  },

  // Admin auth routes (login/register)
  {
    path: '/admin/login',
    element: <AdminLogin />,
  },
  {
    path: '/admin/register',
    element: <AdminRegister />,
  },
]);

const rootElement = document.getElementById('root')!;
createRoot(rootElement).render(
  <React.StrictMode>
    <AuthProvider>
      <CartProvider>
        <RouterProvider router={router} />
      </CartProvider>
    </AuthProvider>
  </React.StrictMode>
);
