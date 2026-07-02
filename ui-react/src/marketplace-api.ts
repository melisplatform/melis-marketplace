/**
 * Client for the Market Place JSON API — owned by MelisMarketPlace itself (not melis-react-api),
 * backend: MelisMarketPlaceReactApiController (vendor/melisplatform/melis-marketplace).
 * Read-only browse — install/update/remove/setup stay on the legacy tool (rendered in an iframe
 * on the record route).
 */

const XHR_HEADER = { 'X-Requested-With': 'XMLHttpRequest' } as const
const BASE = '/melis/MelisMarketPlace/react-api'

export interface PackageItem {
  id: number
  title: string
  name: string
  subtitle: string
  moduleName: string
  description: string
  image: string | null
  url: string | null
  repository: string | null
  totalDownloads: number
  version: string
  releaseDate: string | null
  maintainers: unknown
  type: string | null
  dateAdded: string | null
  lastUpdate: string | null
  groupId: number | null
  groupName: string
  isActive: boolean
  installed: boolean
  versionStatus: 'need_update' | 'up_to_date' | 'in_advance' | null
}

export interface PackageGroup {
  id: number | null
  name: string
}

export interface PackageListParams {
  page?: number
  limit?: number
  search?: string
  group?: string
  orderBy?: string
  order?: 'asc' | 'desc'
}

export interface PackageListResult {
  items: PackageItem[]
  page: number
  pageCount: number
  limit: number
  marketAccessible: boolean
}

export interface MarketPlaceStats {
  total: number
  installed: number
  needUpdate: number
  marketAccessible: boolean
}

async function apiFetch<T>(url: string): Promise<T> {
  const res = await fetch(url, { headers: XHR_HEADER, credentials: 'include' })
  if (!res.ok) {
    let msg = `HTTP ${res.status}`
    try {
      const d = (await res.json()) as { error?: string }
      if (d.error) msg = d.error
    } catch { /* ignore */ }
    throw new Error(msg)
  }
  const data = (await res.json()) as { success: boolean; data?: T; error?: string }
  if (!data.success) throw new Error(data.error ?? 'API error')
  return data.data as T
}

export async function fetchPackages(params: PackageListParams = {}): Promise<PackageListResult> {
  const qs = new URLSearchParams()
  if (params.page) qs.set('page', String(params.page))
  if (params.limit) qs.set('limit', String(params.limit))
  if (params.search) qs.set('search', params.search)
  if (params.group) qs.set('group', params.group)
  if (params.orderBy) qs.set('orderBy', params.orderBy)
  if (params.order) qs.set('order', params.order)
  return apiFetch<PackageListResult>(`${BASE}/packages?${qs}`)
}

export async function fetchPackageGroups(): Promise<{ groups: PackageGroup[]; marketAccessible: boolean }> {
  return apiFetch(`${BASE}/groups`)
}

export async function fetchMarketPlaceStats(): Promise<MarketPlaceStats> {
  return apiFetch<MarketPlaceStats>(`${BASE}/stats`)
}
