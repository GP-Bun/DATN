import React, { createContext, useContext, useMemo, useState } from 'react'

type User = { id: string; name: string; email: string }
type AuthContextValue = {
  user: User | null
  login: (email: string, password: string) => Promise<void>
  register: (name: string, email: string, password: string) => Promise<void>
  logout: () => void
}

const AuthContext = createContext<AuthContextValue | null>(null)

export function useAuth() {
  const ctx = useContext(AuthContext)
  if (!ctx) throw new Error('useAuth must be used within AuthProvider')
  return ctx
}

export function AuthProvider({ children }: { children: React.ReactNode }) {
  const [user, setUser] = useState<User | null>(null)

  const login = async (email: string, _password: string) => {
    await new Promise((r) => setTimeout(r, 400))
    setUser({ id: '1', name: email.split('@')[0] || 'User', email })
  }

  const register = async (name: string, email: string, _password: string) => {
    await new Promise((r) => setTimeout(r, 400))
    setUser({ id: '1', name, email })
  }

  const logout = () => setUser(null)

  const value = useMemo(() => ({ user, login, register, logout }), [user])

  return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>
}


