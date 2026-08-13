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
  /** URL de l'image React (branche melis-react + sous-dossier react/). */
  image: string | null
  /** URL de l'image d'ORIGINE (legacy) — repli onError si l'image React ne charge pas. */
  imageLegacy: string | null
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
  /** true when the module is private (must be bought, not downloadable directly). */
  isPrivate: boolean
  installed: boolean
  versionStatus: 'need_update' | 'up_to_date' | 'in_advance' | null
}

export interface PackageDetail extends PackageItem {
  images: string[]
  /** URLs d'ORIGINE (legacy) alignées index par index avec `images` — repli onError. */
  imagesLegacy: string[]
  currentVersion: string | null
  /** true when the module is protected (never removable/updatable via the marketplace). */
  isExempted: boolean
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
  bundle?: boolean
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
  if (params.bundle) qs.set('bundle', '1')
  return apiFetch<PackageListResult>(`${BASE}/packages?${qs}`)
}

export async function fetchPackageById(id: number): Promise<PackageDetail> {
  return apiFetch<PackageDetail>(`${BASE}/packages/${id}`)
}

export async function fetchPackageGroups(): Promise<{ groups: PackageGroup[]; marketAccessible: boolean }> {
  return apiFetch(`${BASE}/groups`)
}

export async function fetchMarketPlaceStats(): Promise<MarketPlaceStats> {
  return apiFetch<MarketPlaceStats>(`${BASE}/stats`)
}
