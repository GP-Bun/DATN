export type HttpMethod = 'GET' | 'POST' | 'PUT' | 'PATCH' | 'DELETE'

export interface ApiClientOptions {
  baseURL?: string
  getToken?: () => string | null
}

const defaultBaseURL = '/api'

export class ApiClient {
  private baseURL: string
  private getToken?: () => string | null

  constructor(options: ApiClientOptions = {}) {
    this.baseURL = options.baseURL || defaultBaseURL
    this.getToken = options.getToken
  }

  async request<T>(method: HttpMethod, url: string, body?: unknown): Promise<T> {
    const headers: Record<string, string> = {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
    }
    const token = this.getToken?.()
    if (token) headers['Authorization'] = `Bearer ${token}`

    const res = await fetch(`${this.baseURL}${url}`, {
      method,
      headers,
      body: body ? JSON.stringify(body) : undefined,
      credentials: 'include',
    })

    if (!res.ok) {
      let message = `Request failed (${res.status})`
      try {
        const data = await res.json()
        message = data.message || message
      } catch {}
      throw new Error(message)
    }

    if (res.status === 204) return undefined as unknown as T
    return (await res.json()) as T
  }

  get<T>(url: string) { return this.request<T>('GET', url) }
  post<T>(url: string, body?: unknown) { return this.request<T>('POST', url, body) }
  put<T>(url: string, body?: unknown) { return this.request<T>('PUT', url, body) }
  patch<T>(url: string, body?: unknown) { return this.request<T>('PATCH', url, body) }
  delete<T>(url: string) { return this.request<T>('DELETE', url) }
}

export const api = new ApiClient()
