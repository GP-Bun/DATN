// src/main.tsx
import React, { useMemo } from 'react';
import { createRoot } from 'react-dom/client';
import { createBrowserRouter, RouterProvider } from 'react-router-dom';

import { Toaster } from 'react-hot-toast';
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
  OrderSuccessPage,
  LoginPage,
  RegisterPage,
  ProfilePage,
  OrdersPage,
  AboutPage,
  VnPayReturnPage,
  PaymentResultPage
} from './pages';
import TestApiPage from './pages/TestApi';

// Pages Admin
import Dashboard from './admin/pages/Dashboard';
import AdminProducts from './admin/pages/Products';
import AdminOrders from './admin/pages/Orders';
import AdminUsers from './admin/pages/Users';
import AdminReviews from './admin/pages/Reviews';
import AdminLogin from './admin/pages/Login';
import AdminRegister from './admin/pages/Register';
import ChatSupport from './admin/pages/ChatSupport';

// Contexts
import { CartProvider } from './store/CartContext';
import { AuthProvider } from './store/AuthContext';
import AdminRoute from './admin/AdminRoute';

// Tạo router configuration
const routerConfig = [
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
      { path: 'dat-hang-thanh-cong', element: <OrderSuccessPage /> },
      { path: 'dang-nhap', element: <LoginPage /> },
      { path: 'dang-ky', element: <RegisterPage /> },
      { path: 'tai-khoan', element: <ProfilePage /> },
      { path: 'don-hang', element: <OrdersPage /> },
      { path: 'gioi-thieu', element: <AboutPage /> },
      { path: 'vnpay-return', element: <VnPayReturnPage /> },
      { path: 'payment/result', element: <PaymentResultPage /> },
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
      { path: 'reviews', element: <AdminReviews /> },
      { path: 'chat', element: <ChatSupport /> },
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
];

// Component để tạo router sau khi providers đã mount
function RouterWrapper() {
  const router = useMemo(() => createBrowserRouter(routerConfig), []);
  return <RouterProvider router={router} />;
}

// Wrapper component để đảm bảo providers được mount đúng cách
function App() {
  return (
    <AuthProvider>
      <CartProvider>
        <RouterWrapper />
        <Toaster
          position="top-right"
          reverseOrder={false}
          toastOptions={{
            style: {
              borderRadius: '12px',
              background: '#333',
              color: '#fff',
              fontSize: '14px',
              padding: '12px 24px',
              boxShadow: '0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05)',
            },
            success: {
              iconTheme: {
                primary: '#10b981',
                secondary: '#fff',
              },
            },
            error: {
              iconTheme: {
                primary: '#ef4444',
                secondary: '#fff',
              },
            },
          }}
        />
      </CartProvider>
    </AuthProvider>
  );
}

const rootElement = document.getElementById('root')!;
createRoot(rootElement).render(
  <React.StrictMode>
    <App />
  </React.StrictMode>
);
