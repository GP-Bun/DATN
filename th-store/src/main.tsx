import React from 'react'
import { createRoot } from 'react-dom/client'
import { createBrowserRouter, RouterProvider } from 'react-router-dom'
import './style.css'

import AppLayout from './ui/AppLayout'
import AdminLayout from './admin/AdminLayout'
import { 
  HomePage, 
  ProductsPage, 
  ProductDetailPage, 
  CartPage, 
  CheckoutPage, 
  LoginPage, 
  RegisterPage,
  OrderTrackingPage
} from './pages'
import Dashboard from './admin/pages/Dashboard'
import AdminProducts from './admin/pages/Products'
import AdminOrders from './admin/pages/Orders'
import AdminUsers from './admin/pages/Users'
import AdminLogin from './admin/pages/Login'
import { CartProvider } from './store/CartContext'
import { FavoritesProvider } from './store/FavoritesContext'
import { AuthProvider } from './store/AuthContext'

const router = createBrowserRouter([
  {
    path: '/',
    element: <AppLayout />,
    children: [
      { index: true, element: <HomePage /> },
      { path: 'san-pham', element: <ProductsPage /> },
      { path: 'san-pham/:id', element: <ProductDetailPage /> },
      { path: 'gio-hang', element: <CartPage /> },
      { path: 'thanh-toan', element: <CheckoutPage /> },
      { path: 'theo-doi-don', element: <OrderTrackingPage /> },
      { path: 'dang-nhap', element: <LoginPage /> },
      { path: 'dang-ky', element: <RegisterPage /> },
    ],
  },
  {
    path: '/admin',
    element: <AdminLayout />,
    children: [
      { index: true, element: <Dashboard /> },
      { path: 'products', element: <AdminProducts /> },
      { path: 'orders', element: <AdminOrders /> },
      { path: 'users', element: <AdminUsers /> },
    ],
  },
  {
    path: '/admin/login',
    element: <AdminLogin />,
  },
])

const rootElement = document.getElementById('root')!
createRoot(rootElement).render(
  <React.StrictMode>
    <AuthProvider>
      <CartProvider>
        <FavoritesProvider>
          <RouterProvider router={router} />
        </FavoritesProvider>
      </CartProvider>
    </AuthProvider>
  </React.StrictMode>
)
