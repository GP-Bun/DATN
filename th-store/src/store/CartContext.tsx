import React, { createContext, useContext, useMemo, useState, useEffect } from 'react'
import { 
  getCart, 
  addToCart as apiAdd, 
  updateCartItem as apiUpdate, 
  removeCartItem as apiRemove, 
  clearCart as apiClear 
} from '../api/cart.api'

export type CartItem = { 
  id: string
  product_id: number
  name: string
  price: number
  image: string
  quantity: number
  color?: string
  size?: string
}

type CartContextValue = {
  items: CartItem[]
  addToCart: (productId: number, quantity?: number, color?: string | null, size?: string | null, variantId?: number | null) => Promise<void>
  updateCartItem: (itemId: number, quantity: number) => Promise<void>
  removeCartItem: (itemId: number) => Promise<void>
  clearCart: () => Promise<void>
  getTotalPrice: () => number
  getTotalItems: () => number
  reloadCart: () => Promise<void>
}

const CartContext = createContext<CartContextValue | null>(null)

export function useCart() {
  const ctx = useContext(CartContext)
  if (!ctx) throw new Error('useCart must be used within CartProvider')
  return ctx
}

export function CartProvider({ children }: { children: React.ReactNode }) {
  const [items, setItems] = useState<CartItem[]>([])

  // 🔥 Load giỏ hàng từ API backend
  const reloadCart = async () => {
    try {
      const res = await getCart()

      // Backend trả về array items hoặc object có items
      const list = Array.isArray(res) ? res : (res.items || res.data || [])

      setItems(
        list.map((item: any) => ({
          id: item.id.toString(),
          product_id: item.product?.id || item.product_id,
          name: item.product?.name || item.name,
          price: item.price, // Sử dụng price từ cart_item (có thể khác product.price nếu có variant)
          // Ưu tiên: image -> thumbnail_url -> thumbnail -> images[0]
          image: item.product?.image || 
                 item.product?.thumbnail_url || 
                 (item.product?.thumbnail ? `http://127.0.0.1:8000/storage/${item.product.thumbnail}` : null) ||
                 (item.product?.images && item.product.images.length > 0 
                   ? (item.product.images[0].startsWith('http') 
                       ? item.product.images[0] 
                       : `http://127.0.0.1:8000/storage/${item.product.images[0]}`)
                   : null) ||
                 item.image ||
                 null,
          quantity: item.quantity,
          color: item.variant?.color?.name || item.color || null,
          size: item.variant?.size?.value || item.size || null
        }))
      )
    } catch (e) {
      console.error('Lỗi load cart:', e)
      setItems([])
    }
  }

  useEffect(() => {
    reloadCart()
  }, [])

  // 🔥 Thêm vào giỏ hàng (backend)
  const addToCart = async (productId: number, quantity = 1, color?: string | null, size?: string | null, variantId?: number | null) => {
    await apiAdd(productId, quantity, variantId || undefined)
    await reloadCart()
  }

  // 🔥 Cập nhật số lượng
  const updateCartItem = async (itemId: number, quantity: number) => {
    if (quantity <= 0) {
      await removeCartItem(itemId)
      return
    }
    await apiUpdate(itemId, quantity)
    await reloadCart()
  }

  // 🔥 Xóa một item
  const removeCartItem = async (itemId: number) => {
    await apiRemove(itemId)
    await reloadCart()
  }

  // 🔥 Xóa toàn bộ giỏ hàng
  const clearCart = async () => {
    await apiClear()
    setItems([])
  }

  const getTotalPrice = () => items.reduce((sum, i) => sum + i.price * i.quantity, 0)
  const getTotalItems = () => items.reduce((sum, i) => sum + i.quantity, 0)

  const value = useMemo(
    () => ({
      items,
      addToCart,
      updateCartItem,
      removeCartItem,
      clearCart,
      getTotalPrice,
      getTotalItems,
      reloadCart
    }),
    [items]
  )

  return <CartContext.Provider value={value}>{children}</CartContext.Provider>
}
