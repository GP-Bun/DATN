import { useEffect, useState } from "react";
import { getCart, updateCartItem, removeCartItem, clearCart } from "../api/cart.api";

export const useCart = () => {
  const [cart, setCart] = useState<any[]>([]);
  const [loading, setLoading] = useState(true);

  const loadCart = async () => {
    try {
      setLoading(true);
      const data = await getCart();
      setCart(data);
    } finally {
      setLoading(false);
    }
  };

  const updateItem = async (id: number, quantity: number) => {
    await updateCartItem(id, quantity);
    loadCart();
  };

  const removeItem = async (id: number) => {
    await removeCartItem(id);
    loadCart();
  };

  const clearAll = async () => {
    await clearCart();
    loadCart();
  };

  useEffect(() => {
    loadCart();
  }, []);

  return {
    cart,
    loading,
    updateItem,
    removeItem,
    clearAll,
    reload: loadCart,
  };
};
