import React, { createContext, useContext, useMemo, useState } from 'react'

type FavoriteItem = { id: number; name: string; image: string; price: number }

type FavoritesContextValue = {
  items: FavoriteItem[]
  isFavorite: (id: number) => boolean
  add: (item: FavoriteItem) => void
  remove: (id: number) => void
  toggle: (item: FavoriteItem) => void
}

const FavoritesContext = createContext<FavoritesContextValue | null>(null)

export function useFavorites() {
  const ctx = useContext(FavoritesContext)
  if (!ctx) throw new Error('useFavorites must be used within FavoritesProvider')
  return ctx
}

export function FavoritesProvider({ children }: { children: React.ReactNode }) {
  const [items, setItems] = useState<FavoriteItem[]>([])

  const isFavorite = (id: number) => items.some(i => i.id === id)
  const add = (item: FavoriteItem) => setItems(prev => isFavorite(item.id) ? prev : [...prev, item])
  const remove = (id: number) => setItems(prev => prev.filter(i => i.id !== id))
  const toggle = (item: FavoriteItem) => isFavorite(item.id) ? remove(item.id) : add(item)

  const value = useMemo(() => ({ items, isFavorite, add, remove, toggle }), [items])

  return <FavoritesContext.Provider value={value}>{children}</FavoritesContext.Provider>
}


