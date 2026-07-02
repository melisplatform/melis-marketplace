import { useEffect, useRef, useState, type CSSProperties } from 'react'
import { useLocation, useNavigate, useParams } from 'react-router-dom'
import {
  fetchPackages, fetchPackageGroups, fetchMarketPlaceStats,
  type PackageItem, type PackageGroup, type MarketPlaceStats,
} from './marketplace-api'
import { ViewToggle, type ViewMode } from './ViewToggle'

/* ──────────────────────────────────────────────────────────────────────────
 * Brique « Market Place » (MelisMarketPlace). La LISTE (parcours du catalogue de
 * packages) est full React, montée à /melis-marketplace ; le DÉTAIL d'un package
 * (/melis-marketplace/:id) — vue, install/update/remove, formulaire de setup post-
 * installation — reste l'outil LEGACY en iframe : ces actions mutent composer.json,
 * réexécutent dbdeploy et touchent le filesystem, on ne les réimplémente pas.
 * Styles inline + variables CSS du thème, i18n FR/EN via <html lang> (la brique ne
 * partage pas les modules de l'hôte).
 * ────────────────────────────────────────────────────────────────────────── */

const MELIS_KEY = 'melis_market_place_tool_display'
const PACKAGE_MELIS_KEY = 'melis_market_place_tool_package_display'

// ── i18n minimal ──
type Lang = 'fr' | 'en'
function currentLang(): Lang { return (document.documentElement.lang || 'en').toLowerCase().startsWith('fr') ? 'fr' : 'en' }
const DICT: Record<Lang, Record<string, string>> = {
  fr: {
    title: 'Market Place', subtitle: 'Parcourir et installer des modules Melis Platform',
    search: 'Rechercher un package…', all_groups: 'Tous les groupes',
    kpi_total: 'Packages', kpi_installed: 'Installés', kpi_need_update: 'Mises à jour',
    sort_downloads: 'Téléchargements', sort_date: 'Date d’ajout', sort_name: 'Nom',
    refresh: 'Rafraîchir', back: 'retour', loading: 'Chargement…', empty: 'Aucun package trouvé',
    installed_badge: 'Installé', need_update_badge: 'Mise à jour disponible',
    page_of: 'Page {page} / {count}', prev: 'Précédent', next: 'Suivant',
    not_accessible: 'Le serveur Melis Packagist est inaccessible pour le moment.',
    downloads: '{n} téléchargements', version: 'v{v}',
  },
  en: {
    title: 'Market Place', subtitle: 'Browse and install Melis Platform modules',
    search: 'Search a package…', all_groups: 'All groups',
    kpi_total: 'Packages', kpi_installed: 'Installed', kpi_need_update: 'Updates available',
    sort_downloads: 'Downloads', sort_date: 'Date added', sort_name: 'Name',
    refresh: 'Refresh', back: 'back', loading: 'Loading…', empty: 'No package found',
    installed_badge: 'Installed', need_update_badge: 'Update available',
    page_of: 'Page {page} / {count}', prev: 'Previous', next: 'Next',
    not_accessible: 'The Melis Packagist server is currently unreachable.',
    downloads: '{n} downloads', version: 'v{v}',
  },
}
function useT() {
  const lang = currentLang()
  return (key: string, vars?: Record<string, string | number>) => {
    let s = DICT[lang][key] ?? key
    if (vars) for (const [k, v] of Object.entries(vars)) s = s.replaceAll(`{${k}}`, String(v))
    return s
  }
}

// ── Styles (variables CSS du thème) ──
const card: CSSProperties = { border: '1px solid var(--color-border)', background: 'var(--color-card)', borderRadius: 12, boxShadow: '0 1px 2px rgba(0,0,0,.04)' }
const inputCss: CSSProperties = { height: 36, boxSizing: 'border-box', borderRadius: 8, border: '1px solid var(--color-input,var(--color-border))', background: 'var(--color-card)', color: 'var(--color-foreground)', padding: '0 12px', fontSize: 14, outline: 'none' }
const btnGhost: CSSProperties = { display: 'inline-flex', alignItems: 'center', gap: 6, height: 36, padding: '0 12px', borderRadius: 8, border: '1px solid var(--color-border)', background: 'var(--color-card)', color: 'var(--color-foreground)', fontSize: 14, cursor: 'pointer' }

function Kpi({ label, value }: { label: string; value: number | null }) {
  return (
    <div style={{ ...card, display: 'flex', flexDirection: 'column', gap: 2, padding: 16, flex: 1, minWidth: 130 }}>
      <span style={{ fontSize: 12, color: 'var(--color-muted-foreground)' }}>{label}</span>
      <span style={{ fontSize: 22, fontWeight: 700 }}>{value == null ? '…' : value}</span>
    </div>
  )
}

function Badge({ kind, children }: { kind: 'installed' | 'update'; children: string }) {
  const style: CSSProperties = kind === 'installed'
    ? { background: 'color-mix(in srgb, #22c55e 15%, transparent)', color: '#16a34a' }
    : { background: 'color-mix(in srgb, #f59e0b 15%, transparent)', color: '#b45309' }
  return <span style={{ ...style, display: 'inline-block', padding: '2px 8px', borderRadius: 6, fontSize: 11, fontWeight: 600 }}>{children}</span>
}

// ════════════════════════════════════════════════════════════════════════════
export default function MarketPlacePage() {
  const { id } = useParams()
  const location = useLocation()
  const base = id ? location.pathname.slice(0, location.pathname.length - id.length - 1) : location.pathname
  if (id) return <PackageDetail id={id} base={base} />
  return <PackageList base={base} />
}

// ── Liste (native) ──────────────────────────────────────────────────────────
function PackageList({ base }: { base: string }) {
  const t = useT()
  const navigate = useNavigate()
  const [items, setItems] = useState<PackageItem[]>([])
  const [stats, setStats] = useState<MarketPlaceStats | null>(null)
  const [groups, setGroups] = useState<PackageGroup[]>([])
  const [loading, setLoading] = useState(false)
  const [marketAccessible, setMarketAccessible] = useState(true)
  const [searchInput, setSearchInput] = useState('')
  const [search, setSearch] = useState('')
  const [group, setGroup] = useState('')
  const [orderBy, setOrderBy] = useState('mp_total_downloads')
  const [order, setOrder] = useState<'asc' | 'desc'>('desc')
  const [page, setPage] = useState(1)
  const [pageCount, setPageCount] = useState(1)
  const [tick, setTick] = useState(0)
  const [mode, setMode] = useState<ViewMode>('react')
  const [frameLoaded, setFrameLoaded] = useState(false)

  useEffect(() => { fetchMarketPlaceStats().then(setStats).catch(() => null) }, [tick])
  useEffect(() => { fetchPackageGroups().then((r) => setGroups(r.groups)).catch(() => null) }, [])
  useEffect(() => {
    setLoading(true)
    fetchPackages({ page, search, group, orderBy, order })
      .then((r) => { setItems(r.items); setPageCount(Math.max(1, r.pageCount)); setMarketAccessible(r.marketAccessible) })
      .catch(() => null)
      .finally(() => setLoading(false))
  }, [page, search, group, orderBy, order, tick])

  useEffect(() => { setPage(1) }, [search, group, orderBy, order])

  return (
    <div style={{ display: 'flex', flexDirection: 'column', gap: 20, padding: 24, height: '100%', boxSizing: 'border-box', overflow: 'auto' }}>
      <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', gap: 16 }}>
        <div>
          <h1 style={{ fontSize: 20, fontWeight: 700, margin: 0 }}>{t('title')}</h1>
          <p style={{ fontSize: 14, color: 'var(--color-muted-foreground)', margin: '2px 0 0' }}>{t('subtitle')}</p>
        </div>
        <div style={{ display: 'flex', alignItems: 'center', gap: 8 }}>
          <ViewToggle mode={mode} onChange={(m) => { setMode(m); if (m === 'iframe') setFrameLoaded(true) }} />
          <button style={btnGhost} onClick={() => setTick((x) => x + 1)} title={t('refresh')}>↻</button>
        </div>
      </div>

      {/* Vue « Old » : outil Market Place legacy en iframe */}
      {frameLoaded && (
        <div style={{ ...card, display: mode === 'iframe' ? 'flex' : 'none', flex: 1, minHeight: 480, overflow: 'hidden' }}>
          <iframe src={`/melis/react-tool-page?key=${encodeURIComponent(MELIS_KEY)}`}
            style={{ flex: 1, width: '100%', border: 0 }} title="Market Place — Vue Melis"
            sandbox="allow-scripts allow-same-origin allow-forms allow-popups allow-modals" />
        </div>
      )}

      {/* Vue « New » : liste React native */}
      <div style={{ display: mode === 'react' ? 'flex' : 'none', flexDirection: 'column', gap: 20 }}>
        {!marketAccessible ? (
          <div style={{ ...card, padding: '40px 16px', textAlign: 'center', fontSize: 14, color: 'var(--color-muted-foreground)' }}>{t('not_accessible')}</div>
        ) : (<>
          <div style={{ display: 'flex', gap: 12, flexWrap: 'wrap' }}>
            <Kpi label={t('kpi_total')} value={stats?.total ?? null} />
            <Kpi label={t('kpi_installed')} value={stats?.installed ?? null} />
            <Kpi label={t('kpi_need_update')} value={stats?.needUpdate ?? null} />
          </div>

          <div style={{ display: 'flex', gap: 8, flexWrap: 'wrap' }}>
            <input style={{ ...inputCss, flex: 1, minWidth: 220 }} value={searchInput} onChange={(e) => setSearchInput(e.target.value)}
              onKeyDown={(e) => e.key === 'Enter' && setSearch(searchInput.trim())} placeholder={t('search')} />
            <select style={{ ...inputCss, width: 'auto' }} value={group} onChange={(e) => setGroup(e.target.value)}>
              <option value="">{t('all_groups')}</option>
              {groups.map((g) => <option key={g.id ?? g.name} value={g.name}>{g.name}</option>)}
            </select>
            <select style={{ ...inputCss, width: 'auto' }} value={orderBy} onChange={(e) => setOrderBy(e.target.value)}>
              <option value="mp_total_downloads">{t('sort_downloads')}</option>
              <option value="mp_date_added">{t('sort_date')}</option>
              <option value="mp_title">{t('sort_name')}</option>
            </select>
            <button style={btnGhost} onClick={() => setOrder((o) => (o === 'asc' ? 'desc' : 'asc'))}>{order === 'asc' ? '↑' : '↓'}</button>
          </div>

          <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fill, minmax(260px, 1fr))', gap: 16 }}>
            {items.length === 0 && !loading ? (
              <div style={{ ...card, gridColumn: '1 / -1', padding: '40px 16px', textAlign: 'center', fontSize: 14, color: 'var(--color-muted-foreground)' }}>{t('empty')}</div>
            ) : items.map((pkg) => (
              <div key={pkg.id} style={{ ...card, padding: 16, display: 'flex', flexDirection: 'column', gap: 8, cursor: 'pointer' }}
                onClick={() => navigate(`${base}/${pkg.id}`)}>
                <div style={{ display: 'flex', alignItems: 'center', gap: 10 }}>
                  {pkg.image ? (
                    <img src={pkg.image} alt="" style={{ width: 36, height: 36, borderRadius: 8, objectFit: 'cover', flexShrink: 0 }} />
                  ) : (
                    <div style={{ width: 36, height: 36, borderRadius: 8, background: 'var(--color-muted,rgba(0,0,0,.06))', flexShrink: 0 }} />
                  )}
                  <div style={{ minWidth: 0, flex: 1 }}>
                    <div style={{ fontSize: 14, fontWeight: 600, overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap' }}>{pkg.title || pkg.moduleName}</div>
                    <div style={{ fontSize: 12, color: 'var(--color-muted-foreground)' }}>{t('version', { v: pkg.version })}</div>
                  </div>
                </div>
                <p style={{ fontSize: 13, color: 'var(--color-muted-foreground)', margin: 0, display: '-webkit-box', WebkitLineClamp: 2, WebkitBoxOrient: 'vertical', overflow: 'hidden' }}>
                  {pkg.subtitle || pkg.description}
                </p>
                <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', marginTop: 4 }}>
                  <span style={{ fontSize: 12, color: 'var(--color-muted-foreground)' }}>{t('downloads', { n: pkg.totalDownloads })}</span>
                  <div style={{ display: 'flex', gap: 6 }}>
                    {pkg.installed && <Badge kind="installed">{t('installed_badge')}</Badge>}
                    {pkg.versionStatus === 'need_update' && <Badge kind="update">{t('need_update_badge')}</Badge>}
                  </div>
                </div>
              </div>
            ))}
          </div>

          <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'center', gap: 12 }}>
            <button style={btnGhost} disabled={page <= 1} onClick={() => setPage((p) => Math.max(1, p - 1))}>{t('prev')}</button>
            <span style={{ fontSize: 13, color: 'var(--color-muted-foreground)' }}>{loading ? t('loading') : t('page_of', { page, count: pageCount })}</span>
            <button style={btnGhost} disabled={page >= pageCount} onClick={() => setPage((p) => Math.min(pageCount, p + 1))}>{t('next')}</button>
          </div>
        </>)}
      </div>
    </div>
  )
}

// ── Détail d'un package (legacy, iframe) ────────────────────────────────────
function getDetailFrame(): HTMLIFrameElement {
  const FRAME_ID = 'melis-brick-frame-marketplace-detail'
  let f = document.getElementById(FRAME_ID) as HTMLIFrameElement | null
  if (!f) {
    f = document.createElement('iframe')
    f.id = FRAME_ID
    f.title = 'Market Place — Package'
    f.style.cssText = 'position:fixed;border:0;display:none;z-index:1;'
    document.body.appendChild(f)
  }
  return f
}

function PackageDetail({ id, base }: { id: string; base: string }) {
  const t = useT()
  const navigate = useNavigate()
  const anchorRef = useRef<HTMLDivElement>(null)

  useEffect(() => {
    const f = getDetailFrame()
    f.src = `/melis/react-tool-page?key=${encodeURIComponent(PACKAGE_MELIS_KEY)}&packageId=${encodeURIComponent(id)}`
    const anchor = anchorRef.current!
    const sync = () => {
      const r = anchor.getBoundingClientRect()
      f.style.left = `${r.left}px`; f.style.top = `${r.top}px`
      f.style.width = `${r.width}px`; f.style.height = `${r.height}px`
      f.style.display = 'block'
    }
    sync()
    const ro = new ResizeObserver(sync)
    ro.observe(anchor)
    window.addEventListener('resize', sync)
    window.addEventListener('scroll', sync, true)
    return () => {
      f.style.display = 'none'
      ro.disconnect()
      window.removeEventListener('resize', sync)
      window.removeEventListener('scroll', sync, true)
    }
  }, [id])

  return (
    <div style={{ display: 'flex', flexDirection: 'column', height: '100%', minHeight: 0 }}>
      <div style={{ display: 'flex', alignItems: 'center', gap: 12, padding: '12px 24px', borderBottom: '1px solid var(--color-border)', flexShrink: 0 }}>
        <button style={{ ...btnGhost, height: 32, padding: '0 10px' }} onClick={() => navigate(base)}>← {t('back')}</button>
      </div>
      <div ref={anchorRef} style={{ flex: 1, minHeight: 0 }} />
    </div>
  )
}
