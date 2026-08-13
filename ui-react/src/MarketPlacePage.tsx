import { useCallback, useEffect, useRef, useState, type CSSProperties, type ReactNode } from 'react'
import {
  fetchPackages, fetchPackageGroups, fetchMarketPlaceStats, fetchPackageById,
  type PackageItem, type PackageDetail as PackageDetailData,type PackageGroup, type MarketPlaceStats,
} from './marketplace-api'
import { ViewToggle, type ViewMode } from './ViewToggle'
import { useCaps } from './shared/useCaps'
import { useDebounce } from './shared/useDebounce'
import { useIsNarrow } from './shared/useIsNarrow'

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

// ── i18n minimal ──
type Lang = 'fr' | 'en'
// Source autoritaire = la locale persistée par l'hôte (melis-core I18nProvider) dans le
// localStorage, fiable dès le boot. Le `<html lang>` du shell est figé à "fr" dans index.html
// et n'est corrigé par l'hôte qu'APRÈS son fetch /langs → lire uniquement `<html lang>` donne
// un flash FR même en session EN. On retombe sur `<html lang>` en dernier recours.
function currentLang(): Lang {
  try {
    const l = localStorage.getItem('melis-ui-lang')
    if (l) return l.toLowerCase().startsWith('fr') ? 'fr' : 'en'
    const loc = localStorage.getItem('melis-ui-locale')
    if (loc) return loc.toLowerCase().startsWith('fr') ? 'fr' : 'en'
  } catch { /* localStorage indisponible */ }
  return (document.documentElement.lang || 'en').toLowerCase().startsWith('fr') ? 'fr' : 'en'
}
const DICT: Record<Lang, Record<string, string>> = {
  fr: {
    title: 'Market Place', subtitle: 'Parcourir et installer des modules Melis Platform',
    search: 'Rechercher un package…', all_groups: 'Tous les groupes',
    kpi_total: 'Packages', kpi_installed: 'Installés', kpi_need_update: 'Mises à jour',
    sort_downloads: 'Téléchargements', sort_date: 'Date d’ajout', sort_name: 'Nom',
    reset_filters: 'Réinitialiser les filtres',
    refresh: 'Rafraîchir', back: 'retour', loading: 'Chargement…', empty: 'Aucun package trouvé',
    installed_badge: 'Installé', need_update_badge: 'Mise à jour disponible',
    end_of_list: 'Fin de la liste',
    not_accessible: 'Le serveur Melis Packagist est inaccessible pour le moment.',
    no_list_access: "Vous n'avez pas le droit de consulter le catalogue Market Place.",
    downloads: '{n} téléchargements',
    listed_title: 'Vous voulez votre module listé sur cette page ?',
    listed_body: 'Ajoutez cette ligne dans votre composer.json',
    listed_after: 'et il sera automatiquement inclu dans les résultats de Packagist ! Les images et les textes doivent être définis dans vos sources sous la forme d’un XML placé dans le répertoire etc/ de votre module. Merci de regarder',
    listed_link: 'ici', listed_end: 'pour avoir un exemple de structure.',
    most_downloaded: 'Packages les plus téléchargés',
    bundles: 'Bundles',
    latest_version: 'Dernière version', current_version: 'Version installée',
    github: 'Github', packagist: 'Packagist', package_name: 'Nom du package',
    manage_title: 'Installer, mettre à jour ou supprimer',
    manage_open: 'Ouvrir l’outil classique', manage_close: 'Fermer',
    exempted_note: 'Ce module est protégé et ne peut pas être supprimé ou mis à jour depuis la Market Place.',
    additional_info: 'Informations complémentaires', downloads_label: 'Téléchargements',
    btn_private: 'Privé', btn_download: 'Télécharger', btn_update: 'Mettre à jour', btn_manage: 'Gérer',
    private_title: 'Module privé',
    private_body: 'Ce module est privé. Vous ne pouvez pas le télécharger directement, il doit être acheté. Contactez-nous :',
    private_form: 'notre formulaire de contact',
    btn_remove: 'Supprimer',
    mng_confirm_require: 'Télécharger et installer « {module} » ? Composer va récupérer le paquet.',
    mng_confirm_update: 'Mettre à jour « {module} » vers la dernière version ?',
    mng_confirm_remove: 'Supprimer « {module} » ? Cette action désinstalle le module.',
    mng_confirm_btn: 'Confirmer', mng_cancel: 'Annuler', mng_close: 'Fermer',
    mng_checking: 'Vérification des dépendances…',
    mng_blocked_title: 'Suppression impossible',
    mng_blocked_body: 'Les modules suivants dépendent de « {module} » et doivent être supprimés d’abord :',
    mng_start_require: 'Téléchargement de « {module} »…',
    mng_start_update: 'Mise à jour de « {module} »…',
    mng_start_remove: 'Suppression de « {module} »…',
    mng_exec_done: 'Tâche Composer terminée.',
    mng_dbdeploy: 'Exécution des migrations de base de données…',
    mng_dbdeploy_ok: 'Migrations terminées.',
    mng_composer_scripts: 'Exécution des scripts Composer…',
    mng_setup_form_note: 'Ce module a un formulaire de configuration post-installation — configurez-le via l’outil classique si nécessaire.',
    mng_download_done: 'Téléchargement terminé. Activez le module pour l’utiliser.',
    mng_not_found: 'Module introuvable après le téléchargement.',
    mng_perm_changed: 'Permissions du répertoire ajustées.',
    mng_remove_ok: '« {module} » supprimé avec succès.',
    mng_remove_ko: 'Échec de la suppression de « {module} ».',
    mng_table_dump: 'Export des tables du module…',
    mng_table_dump_ok: 'Tables exportées (SQL téléchargé).',
    mng_activate: 'Activer le module', mng_activating: 'Activation…', mng_reload: 'Recharger',
    mng_error: 'Une erreur est survenue. Réessayez.',
  },
  en: {
    title: 'Market Place', subtitle: 'Browse and install Melis Platform modules',
    search: 'Search a package…', all_groups: 'All groups',
    kpi_total: 'Packages', kpi_installed: 'Installed', kpi_need_update: 'Updates available',
    sort_downloads: 'Downloads', sort_date: 'Date added', sort_name: 'Name',
    reset_filters: 'Reset filters',
    refresh: 'Refresh', back: 'back', loading: 'Loading…', empty: 'No package found',
    installed_badge: 'Installed', need_update_badge: 'Update available',
    end_of_list: 'End of list',
    not_accessible: 'The Melis Packagist server is currently unreachable.',
    no_list_access: 'You are not allowed to browse the Market Place catalog.',
    downloads: '{n} downloads',
    listed_title: 'Want your module listed on this page?',
    listed_body: 'Add this line to your composer.json',
    listed_after: 'and it will automatically be included in the results grabbed from Packagist! Images and texts must be defined in your sources under the form of an XML located in a etc/ folder of your module. Please have a look',
    listed_link: 'here', listed_end: 'to have an example of the structure.',
    most_downloaded: 'Most downloaded packages',
    bundles: 'Bundles',
    latest_version: 'Latest version', current_version: 'Current version',
    github: 'Github', packagist: 'Packagist', package_name: 'Package name',
    manage_title: 'Install, update or remove',
    manage_open: 'Open the classic tool', manage_close: 'Close',
    exempted_note: 'This module is protected and cannot be removed or updated from the Market Place.',
    additional_info: 'Additional information', downloads_label: 'Downloads',
    btn_private: 'Private', btn_download: 'Download', btn_update: 'Update', btn_manage: 'Manage',
    private_title: 'Private module',
    private_body: "This module is private. You can't download it directly, it must be bought. Please contact us at:",
    private_form: 'our contact form',
    btn_remove: 'Remove',
    mng_confirm_require: 'Download and install "{module}"? Composer will fetch the package.',
    mng_confirm_update: 'Update "{module}" to the latest version?',
    mng_confirm_remove: 'Remove "{module}"? This uninstalls the module.',
    mng_confirm_btn: 'Confirm', mng_cancel: 'Cancel', mng_close: 'Close',
    mng_checking: 'Checking dependencies…',
    mng_blocked_title: 'Cannot remove',
    mng_blocked_body: 'The following modules depend on "{module}" and must be removed first:',
    mng_start_require: 'Downloading "{module}"…',
    mng_start_update: 'Updating "{module}"…',
    mng_start_remove: 'Removing "{module}"…',
    mng_exec_done: 'Composer task done.',
    mng_dbdeploy: 'Running database migrations…',
    mng_dbdeploy_ok: 'Database migrations done.',
    mng_composer_scripts: 'Running composer scripts…',
    mng_setup_form_note: 'This module has a post-install setup form — configure it via the classic tool if needed.',
    mng_download_done: 'Download complete. Activate the module to enable it.',
    mng_not_found: 'Module not found after download.',
    mng_perm_changed: 'Adjusted package directory permissions.',
    mng_remove_ok: '"{module}" removed successfully.',
    mng_remove_ko: 'Failed to remove "{module}".',
    mng_table_dump: 'Exporting module tables…',
    mng_table_dump_ok: 'Tables exported (SQL downloaded).',
    mng_activate: 'Activate module', mng_activating: 'Activating…', mng_reload: 'Reload',
    mng_error: 'An error occurred. Please try again.',
  },
}
function useT() {
  // Réactif : l'hôte corrige `<html lang>` après son fetch /langs (et sur changement de langue,
  // via un reload). On resynchronise sur la mutation de l'attribut `lang` + les events storage,
  // pour repeindre dans la bonne langue sans rester bloqué sur la valeur du 1er rendu.
  const [lang, setLang] = useState<Lang>(currentLang)
  useEffect(() => {
    const sync = () => setLang(currentLang())
    sync()
    const obs = new MutationObserver(sync)
    obs.observe(document.documentElement, { attributes: true, attributeFilter: ['lang'] })
    window.addEventListener('storage', sync)
    return () => { obs.disconnect(); window.removeEventListener('storage', sync) }
  }, [])
  return (key: string, vars?: Record<string, string | number>) => {
    let s = DICT[lang][key] ?? key
    if (vars) for (const [k, v] of Object.entries(vars)) s = s.replaceAll(`{${k}}`, String(v))
    return s
  }
}

/** Normalise "v5.3.5" / "5.3.5" / "vv5.3.5" → "v5.3.5" (single leading v). */
function fmtVersion(v: string): string {
  return 'v' + v.replace(/^v+/i, '')
}

/**
 * Package descriptions come from Packagist as raw HTML (`<p>…<br />…</p>`). We render them
 * as plain text (styled by the brick), so strip the tags: <br>/<p> → line breaks, drop the rest,
 * decode the common entities. Avoids the literal "<p>" showing up in the UI.
 */
function stripHtml(html: string): string {
  if (!html) return ''
  return html
    .replace(/<\s*br\s*\/?>/gi, '\n')
    .replace(/<\/\s*(p|div|li)\s*>/gi, '\n')
    .replace(/<[^>]+>/g, '')
    .replace(/&nbsp;/gi, ' ')
    .replace(/&amp;/gi, '&')
    .replace(/&lt;/gi, '<')
    .replace(/&gt;/gi, '>')
    .replace(/&quot;/gi, '"')
    .replace(/&#0*39;|&apos;/gi, "'")
    .replace(/[ \t]+\n/g, '\n')
    .replace(/\n{3,}/g, '\n\n')
    .trim()
}

// ── Styles (variables CSS du thème) ──
const card: CSSProperties = { border: '1px solid var(--color-border)', background: 'var(--color-card)', borderRadius: 12, boxShadow: '0 1px 2px rgba(0,0,0,.04)' }
const inputCss: CSSProperties = { height: 36, boxSizing: 'border-box', borderRadius: 8, border: '1px solid var(--color-input,var(--color-border))', background: 'var(--color-card)', color: 'var(--color-foreground)', padding: '0 12px', fontSize: 14, outline: 'none' }
const btnGhost: CSSProperties = { display: 'inline-flex', alignItems: 'center', gap: 6, height: 36, padding: '0 12px', borderRadius: 8, border: '1px solid var(--color-border)', background: 'var(--color-card)', color: 'var(--color-foreground)', fontSize: 14, cursor: 'pointer' }
// Bouton d'action du détail sur viewport étroit : sa propre ligne, pleine largeur, centré.
const actionBtnNarrow: CSSProperties = { flex: '1 1 100%', justifyContent: 'center' }

// Flèche circulaire anti-horaire — bouton « Réinitialiser les filtres » (même style que l'icône de recherche).
const ResetIcon = () => (
  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"
    strokeLinecap="round" strokeLinejoin="round" style={{ flexShrink: 0 }}>
    <path d="M3 2v6h6" /><path d="M3 13a9 9 0 1 0 3-7.7L3 8" />
  </svg>
)

// ── Group filter buttons (legacy colored "M" tiles: Core/Cms/Marketing/Commerce/Sites) ──
const GROUP_COLORS: Record<string, string> = {
  core: '#ef6623', cms: '#67b345', marketing: '#70479c', commerce: '#3085c6', sites: '#c72127',
}
function groupColor(name: string): string {
  return GROUP_COLORS[name.toLowerCase()] ?? '#c72127'
}
// `mark` = glyph only (no rounded square) — for use on a solid colored chip where the
// square would blend into the background; `glyph` is then the color of the "M" strokes.
function GroupLogo({ color, size = 16, mark = false, glyph = '#fff' }: { color: string; size?: number; mark?: boolean; glyph?: string }) {
  return (
    <svg width={size} height={size} viewBox="0 0 80 80" style={{ flexShrink: 0 }}>
      {!mark && <rect fill={color} x=".07" y=".13" width="79.86" height="79.86" rx="15.36" ry="15.36" />}
      <path fill={glyph} d="M57.78,15.87c-3.47,0-6.29,2.81-6.29,6.29v35.85c0,3.47,2.81,6.29,6.29,6.29s6.29-2.81,6.29-6.29V22.16c0-3.47-2.81-6.29-6.29-6.29Z" />
      <path fill={glyph} d="M27.79,19.16c-1.62-3.07-5.43-4.24-8.5-2.62-3.07,1.62-4.24,5.43-2.62,8.5l19.01,35.93c1.62,3.07,5.43,4.24,8.5,2.62,3.07-1.62,4.24-5.43,2.62-8.5L27.79,19.16Z" />
      <circle fill={glyph} cx="22.36" cy="57.88" r="6.43" />
    </svg>
  )
}
// Petits icônes inline (les briques n'importent pas lucide — React est externalisé côté hôte).
function Ic({ children, size = 16 }: { children: ReactNode; size?: number }) {
  return (
    <svg width={size} height={size} viewBox="0 0 24 24" fill="none" stroke="currentColor"
      strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" style={{ flexShrink: 0 }}>{children}</svg>
  )
}
const ICON_BOX = (<><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z" /><path d="m3.3 7 8.7 5 8.7-5" /><path d="M12 22V12" /></>)
const ICON_CHECK = (<><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" /><path d="m9 11 3 3L22 4" /></>)
const ICON_UP = (<><circle cx="12" cy="12" r="10" /><path d="m16 12-4-4-4 4" /><path d="M12 16V8" /></>)
const ICON_TAG = (<><path d="M12.586 2.586A2 2 0 0 0 11.172 2H4a2 2 0 0 0-2 2v7.172a2 2 0 0 0 .586 1.414l8.704 8.704a2.426 2.426 0 0 0 3.42 0l6.58-6.58a2.426 2.426 0 0 0 0-3.42z" /><circle cx="7.5" cy="7.5" r="1.5" /></>)
const ICON_GITHUB = (<><path d="M15 22v-4a4.8 4.8 0 0 0-1-3.5c3 0 6-2 6-5.5.08-1.25-.27-2.48-1-3.5.28-1.15.28-2.35 0-3.5 0 0-1 0-3 1.5-2.64-.5-5.36-.5-8 0C6 2 5 2 5 2c-.3 1.15-.3 2.35 0 3.5A5.4 5.4 0 0 0 4 9c0 3.5 3 5.5 6 5.5-.39.49-.68 1.05-.85 1.65-.17.6-.22 1.23-.15 1.85v4" /><path d="M9 18c-4.51 2-5-2-7-2" /></>)
const ICON_LINK = (<><path d="M15 3h6v6" /><path d="M10 14 21 3" /><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6" /></>)
const ICON_DOWNLOAD = (<><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" /><polyline points="7 10 12 15 17 10" /><line x1="12" x2="12" y1="15" y2="3" /></>)

// Teinte de groupe translucide — fonds/accents dérivés de la couleur du groupe.
function groupTint(name: string, pct: number): string {
  return `color-mix(in srgb, ${groupColor(name)} ${pct}%, transparent)`
}

// Carte fantôme (skeleton) pendant le chargement initial / un changement de filtre.
function SkeletonCard() {
  return (
    <div style={{ ...card, overflow: 'hidden' }}>
      <div style={{ height: 3, background: 'var(--color-border)' }} />
      <div className="melis-mp-sk" style={{ height: 160 }} />
      <div style={{ padding: 16, display: 'flex', flexDirection: 'column', gap: 10 }}>
        <div className="melis-mp-sk" style={{ height: 14, width: '65%', borderRadius: 4 }} />
        <div className="melis-mp-sk" style={{ height: 12, width: '100%', borderRadius: 4 }} />
        <div className="melis-mp-sk" style={{ height: 12, width: '85%', borderRadius: 4 }} />
        <div style={{ display: 'flex', justifyContent: 'space-between', marginTop: 4 }}>
          <div className="melis-mp-sk" style={{ height: 18, width: 60, borderRadius: 5 }} />
          <div className="melis-mp-sk" style={{ height: 18, width: 72, borderRadius: 6 }} />
        </div>
      </div>
    </div>
  )
}

// Puce de filtre actif (retirable d'un clic).
function FilterChip({ children, onRemove }: { children: ReactNode; onRemove: () => void }) {
  return (
    <span style={{
      display: 'inline-flex', alignItems: 'center', gap: 6, padding: '3px 5px 3px 10px', borderRadius: 999,
      fontSize: 12, fontWeight: 500, background: 'var(--color-muted,rgba(0,0,0,.05))', border: '1px solid var(--color-border)',
    }}>
      {children}
      <button onClick={onRemove} aria-label="remove"
        style={{ display: 'inline-flex', alignItems: 'center', justifyContent: 'center', width: 16, height: 16, borderRadius: '50%', border: 'none', cursor: 'pointer', background: 'color-mix(in srgb, var(--color-foreground) 12%, transparent)', color: 'var(--color-foreground)', fontSize: 10, lineHeight: 1, padding: 0 }}>✕</button>
    </span>
  )
}

/** Small spinner — self-contained (injects its own @keyframes; bricks can't rely on host CSS). */
function Spinner({ size = 28 }: { size?: number }) {
  return (
    <>
      <style>{'@keyframes melis-mp-spin { to { transform: rotate(360deg) } }'}</style>
      <div style={{
        width: size, height: size, borderRadius: '50%',
        border: '3px solid color-mix(in srgb, var(--color-primary) 20%, transparent)',
        borderTopColor: 'var(--color-primary)',
        animation: 'melis-mp-spin 700ms linear infinite',
      }} />
    </>
  )
}
function GroupButton({ label, color, active, onClick, fullWidth = false }: { label: string; color: string; active: boolean; onClick: () => void; fullWidth?: boolean }) {
  const [hover, setHover] = useState(false)
  const [pressed, setPressed] = useState(false)
  return (
    <button
      onClick={onClick}
      onMouseEnter={() => setHover(true)}
      onMouseLeave={() => { setHover(false); setPressed(false) }}
      onMouseDown={() => setPressed(true)}
      onMouseUp={() => setPressed(false)}
      style={{
        display: 'inline-flex', alignItems: 'center', justifyContent: 'center', gap: 8, height: 36, padding: '0 14px', borderRadius: 8,
        ...(fullWidth ? { width: '100%' } : null),
        border: `1.5px solid ${active ? color : 'var(--color-border)'}`,
        cursor: 'pointer', fontSize: 13, fontWeight: 500,
        background: active ? `color-mix(in srgb, ${color} 14%, transparent)` : 'var(--color-card)',
        color: active ? color : 'var(--color-foreground)',
        boxShadow: pressed
          ? 'inset 0 1px 3px rgba(0,0,0,.12)'
          : hover
            ? '0 4px 10px rgba(0,0,0,.10)'
            : '0 1px 2px rgba(0,0,0,.05)',
        transform: pressed ? 'translateY(0)' : hover ? 'translateY(-1px)' : 'translateY(0)',
        transition: 'transform 120ms ease, box-shadow 120ms ease, background 120ms ease, border-color 120ms ease',
      }}
    >
      <GroupLogo color={color} />
      {label}
    </button>
  )
}
function GroupButtons({ groups, value, onChange, bundle, onToggleBundle, t, narrow }: {
  groups: PackageGroup[]; value: string; onChange: (v: string) => void
  bundle: boolean; onToggleBundle: () => void
  t: (key: string) => string
  narrow: boolean
}) {
  return (
    <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', gap: 8, flexWrap: 'wrap' }}>
      <div style={{ display: 'flex', gap: 8, flexWrap: 'wrap' }}>
        <GroupButton label={t('all_groups')} color="#6b7280" active={value === ''} onClick={() => onChange('')} />
        {groups.map((g) => (
          <GroupButton key={g.id ?? g.name} label={g.name} color={groupColor(g.name)}
            active={value === String(g.id ?? '')} onClick={() => onChange(String(g.id ?? ''))} />
        ))}
      </div>
      {/* Narrow : « Bundles » prend sa propre ligne pleine largeur (le pousser sur la même ligne
          que les groupes le coince à quelques pixels du bord). */}
      <div style={narrow ? { flex: '1 1 100%', display: 'flex' } : undefined}>
        <GroupButton label={t('bundles')} color="#c72127" active={bundle} onClick={onToggleBundle} fullWidth={narrow} />
      </div>
    </div>
  )
}

function Kpi({ label, value, icon, accent }: { label: string; value: number | null; icon?: ReactNode; accent?: string }) {
  return (
    <div style={{ ...card, display: 'flex', alignItems: 'center', gap: 12, padding: 16, flex: 1, minWidth: 150 }}>
      {icon && (
        <div style={{
          width: 40, height: 40, borderRadius: 11, display: 'flex', alignItems: 'center', justifyContent: 'center', flexShrink: 0,
          background: accent ? `color-mix(in srgb, ${accent} 15%, transparent)` : 'var(--color-muted,rgba(0,0,0,.06))',
          color: accent ?? 'var(--color-foreground)',
        }}>{icon}</div>
      )}
      <div style={{ display: 'flex', flexDirection: 'column', gap: 3, minWidth: 0 }}>
        <span style={{ fontSize: 12, color: 'var(--color-muted-foreground)' }}>{label}</span>
        <span style={{ fontSize: 22, fontWeight: 700, lineHeight: 1 }}>{value == null ? '…' : value}</span>
      </div>
    </div>
  )
}

// Badge — teinte + bordure de même hue (la bordure assure la lisibilité en thème sombre,
// où un simple fond translucide se fondrait dans le canvas).
function Badge({ kind, children }: { kind: 'installed' | 'update'; children: string }) {
  const hue = kind === 'installed' ? '#22c55e' : '#f59e0b'
  const fg = kind === 'installed' ? '#16a34a' : '#b45309'
  return (
    <span style={{
      display: 'inline-block', padding: '2px 8px', borderRadius: 6, fontSize: 11, fontWeight: 600,
      background: `color-mix(in srgb, ${hue} 18%, transparent)`,
      border: `1px solid color-mix(in srgb, ${hue} 40%, transparent)`,
      color: fg,
    }}>{children}</span>
  )
}

/** Package card — lifts + shadows on hover as a clickable affordance (opens the detail view). */
function PackageCard({ pkg, t, onClick }: { pkg: PackageItem; t: (key: string, vars?: Record<string, string | number>) => string; onClick: () => void }) {
  const [hover, setHover] = useState(false)
  return (
    <div
      style={{
        ...card,
        overflow: 'hidden',
        display: 'flex',
        flexDirection: 'column',
        cursor: 'pointer',
        transition: 'transform 150ms ease, box-shadow 150ms ease, border-color 150ms ease',
        transform: hover ? 'translateY(-4px)' : 'translateY(0)',
        boxShadow: hover ? '0 12px 24px rgba(0,0,0,.12)' : '0 1px 2px rgba(0,0,0,.04)',
        borderColor: hover ? 'var(--color-primary)' : 'var(--color-border)',
      }}
      onMouseEnter={() => setHover(true)}
      onMouseLeave={() => setHover(false)}
      onClick={onClick}
    >
      {/* Liseré haut coloré par groupe — rappelle l'identité du groupe sans surcharger. */}
      <div style={{ height: 3, background: pkg.groupName ? groupColor(pkg.groupName) : 'var(--color-primary)' }} />
      <div style={{ position: 'relative', overflow: 'hidden' }}>
        {pkg.image ? (<>
          <img src={pkg.image} data-legacy={pkg.imageLegacy ?? ''} alt="" onError={mpImgFallback} style={{ width: '100%', height: 160, objectFit: 'cover', display: 'block', transition: 'transform 200ms ease', transform: hover ? 'scale(1.05)' : 'scale(1)' }} />
          {/* Léger dégradé en pied d'image → uniformise des visuels très hétérogènes. */}
          <div style={{ position: 'absolute', inset: 0, background: 'linear-gradient(to bottom, transparent 62%, rgba(0,0,0,.12))', pointerEvents: 'none' }} />
        </>) : (
          /* Pas de visuel → filigrane du logo de groupe sur fond teinté (plus de rectangle gris mort). */
          <div style={{ width: '100%', height: 160, display: 'flex', alignItems: 'center', justifyContent: 'center',
            background: pkg.groupName ? `linear-gradient(135deg, ${groupTint(pkg.groupName, 16)}, var(--color-muted,rgba(0,0,0,.05)))` : 'var(--color-muted,rgba(0,0,0,.06))' }}>
            {pkg.groupName && <div style={{ opacity: 0.2 }}><GroupLogo color={groupColor(pkg.groupName)} size={64} /></div>}
          </div>
        )}
        {/* Logo de groupe — identique au back-office legacy (.melis-svg) : carré « M »
            coloré par groupe, 30×30, coin haut-droit. Léger drop-shadow (le legacy est
            posé sur le fond blanc de la fiche ; ici il est au-dessus de l'image de couverture). */}
        {pkg.groupName && (
          <div style={{ position: 'absolute', top: 10, right: 10, width: 30, height: 30, filter: 'drop-shadow(0 1px 3px rgba(0,0,0,.35))' }} title={pkg.groupName}>
            <GroupLogo color={groupColor(pkg.groupName)} size={30} />
          </div>
        )}
      </div>
      <div style={{ padding: 16, display: 'flex', flexDirection: 'column', gap: 8, flex: 1 }}>
        <div style={{ fontSize: 15, fontWeight: 600, overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap', color: hover ? 'var(--color-primary)' : 'var(--color-foreground)' }}>
          {pkg.title || pkg.moduleName}
        </div>
        <p style={{ fontSize: 13, color: 'var(--color-muted-foreground)', margin: 0, display: '-webkit-box', WebkitLineClamp: 2, WebkitBoxOrient: 'vertical', overflow: 'hidden' }}>
          {stripHtml(pkg.subtitle || pkg.description)}
        </p>
        <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', gap: 8, marginTop: 4 }}>
          <div style={{ display: 'flex', alignItems: 'center', gap: 8, minWidth: 0 }}>
            <span style={{ fontSize: 11, fontWeight: 600, fontFamily: 'monospace', padding: '2px 6px', borderRadius: 5, whiteSpace: 'nowrap', background: 'var(--color-muted,rgba(0,0,0,.06))', color: 'var(--color-muted-foreground)' }}>{fmtVersion(pkg.version)}</span>
            <span style={{ display: 'inline-flex', alignItems: 'center', gap: 4, fontSize: 12, color: 'var(--color-muted-foreground)', whiteSpace: 'nowrap' }}><Ic size={13}>{ICON_DOWNLOAD}</Ic>{pkg.totalDownloads.toLocaleString()}</span>
          </div>
          <div style={{ display: 'flex', gap: 6, flexShrink: 0 }}>
            {pkg.installed && <Badge kind="installed">{t('installed_badge')}</Badge>}
            {pkg.versionStatus === 'need_update' && <Badge kind="update">{t('need_update_badge')}</Badge>}
            {!pkg.installed && (pkg.isPrivate ? (
              <span style={{ display: 'inline-flex', alignItems: 'center', gap: 4, padding: '2px 8px', borderRadius: 6, fontSize: 11, fontWeight: 600, background: 'var(--color-muted,rgba(0,0,0,.06))', color: 'var(--color-muted-foreground)' }}>🔒 {t('btn_private')}</span>
            ) : (
              <span style={{ display: 'inline-flex', alignItems: 'center', gap: 4, padding: '3px 10px', borderRadius: 6, fontSize: 11, fontWeight: 600, background: 'var(--color-primary)', color: 'var(--color-primary-foreground,#fff)', boxShadow: hover ? '0 3px 10px color-mix(in srgb, var(--color-primary) 45%, transparent)' : 'none', transition: 'box-shadow 150ms ease' }}>↓ {t('btn_download')}</span>
            ))}
          </div>
        </div>
      </div>
    </div>
  )
}

// ════════════════════════════════════════════════════════════════════════════
export default function MarketPlacePage() {
  // List vs. detail is PURE INTERNAL STATE — no react-router navigation, no per-package top
  // tab. Each brick maps to exactly ONE host tab (Shell keys its cleanup-on-tab-close by
  // brick.id alone); spawning extra top tabs under this brick's route confused that cleanup
  // into unmounting the whole brick (incl. the list) whenever ANY of those tabs was closed —
  // that's what caused other tabs to go blank with several Marketplace-related tabs open.
  const [openId, setOpenId] = useState<number | null>(null)

  // La liste reste MONTÉE (cachée) quand un détail est ouvert : revenir en arrière ne
  // remonte donc plus PackageList → recherche / filtres / tri / scroll / pagination
  // infinie sont préservés (sinon chaque retour rechargeait toute la liste depuis la
  // page 1). Le détail est rendu par-dessus, keyé par id pour repartir propre d'un
  // package à l'autre. Rafraîchir l'état « installé » après une action se fait via le
  // bouton ↻ (les actions install/update/remove ont lieu dans l'iframe legacy du détail).
  return (
    <>
      <div style={{ height: '100%', display: openId != null ? 'none' : 'block' }}>
        <PackageList onOpen={setOpenId} />
      </div>
      {openId != null && <PackageDetail key={openId} id={openId} onBack={() => setOpenId(null)} onOpen={setOpenId} />}
    </>
  )
}

// ── Liste (native) ──────────────────────────────────────────────────────────
function PackageList({ onOpen }: { onOpen: (id: number) => void }) {
  const t = useT()
  const narrow = useIsNarrow()
  // Capacité `list` (droit avancé, cf. config/react.capabilities.php) : parcourir le catalogue.
  // Default-allow (admin / cap non déclarée → permis). Refusée → on masque la vue React native.
  const { can } = useCaps(MELIS_KEY)
  const [items, setItems] = useState<PackageItem[]>([])
  const [stats, setStats] = useState<MarketPlaceStats | null>(null)
  const [groups, setGroups] = useState<PackageGroup[]>([])
  const [loading, setLoading] = useState(false)
  const [marketAccessible, setMarketAccessible] = useState(true)
  const [searchInput, setSearchInput] = useState('')
  const [search, setSearch] = useState('')
  const [group, setGroup] = useState('')
  const [bundle, setBundle] = useState(false)
  const [orderBy, setOrderBy] = useState('mp_total_downloads')
  const [order, setOrder] = useState<'asc' | 'desc'>('desc')
  const [page, setPage] = useState(1)
  const [pageCount, setPageCount] = useState(1)
  const [tick, setTick] = useState(0)
  const [mode, setMode] = useState<ViewMode>('react')
  const [frameLoaded, setFrameLoaded] = useState(false)

  const hasMore = page < pageCount
  const sentinelRef = useRef<HTMLDivElement>(null)

  useEffect(() => { fetchMarketPlaceStats().then(setStats).catch(() => null) }, [tick])
  useEffect(() => { fetchPackageGroups().then((r) => setGroups(r.groups)).catch(() => null) }, [])
  useEffect(() => {
    setLoading(true)
    fetchPackages({ page, search, group, orderBy, order, bundle })
      .then((r) => {
        setItems((prev) => (page === 1 ? r.items : [...prev, ...r.items]))
        setPageCount(Math.max(1, r.pageCount))
        setMarketAccessible(r.marketAccessible)
      })
      .catch(() => null)
      .finally(() => setLoading(false))
  }, [page, search, group, orderBy, order, bundle, tick])

  // Chaque changement de filtre/tri/refresh repart de la page 1 — reset fait au point
  // d'appel (pas via un effet séparé) pour éviter un fetch de la page N avec les
  // nouveaux filtres avant que le reset ne prenne effet.
  function resetList() { setItems([]); setPage(1) }
  function changeSearch(v: string) { setSearch(v); resetList() }

  const debouncedSearchInput = useDebounce(searchInput, 300)
  useEffect(() => {
    const trimmed = debouncedSearchInput.trim()
    if (trimmed === search) return
    changeSearch(trimmed)
  }, [debouncedSearchInput])
  function changeGroup(v: string) { setGroup(v); resetList() }
  function toggleBundle() { setBundle((b) => !b); resetList() }
  // Tri = champ + direction en un seul contrôle (valeur « champ:dir ») — plus clair que
  // le select + bouton ↑/↓ séparés.
  function changeSort(field: string, dir: 'asc' | 'desc') { setOrderBy(field); setOrder(dir); resetList() }
  function refresh() { setTick((x) => x + 1); resetList() }

  const hasActiveFilters = Boolean(search || group || bundle)
  const activeGroupName = groups.find((g) => String(g.id ?? '') === group)?.name ?? ''

  // Réinitialiser : recherche + groupe + bundle + tri par défaut (téléchargements desc),
  // puis refetch (`tick`). `resetList()` vide la grille et repart de la page 1 — sinon les
  // anciennes cartes restent affichées et le clic paraît sans effet.
  function resetFilters() {
    setSearchInput(''); setSearch(''); setGroup(''); setBundle(false)
    setOrderBy('mp_total_downloads'); setOrder('desc')
    resetList()
    setTick((x) => x + 1)
  }

  useEffect(() => {
    if (!sentinelRef.current || !hasMore) return
    const obs = new IntersectionObserver(
      ([entry]) => { if (entry.isIntersecting && !loading) setPage((p) => p + 1) },
      { rootMargin: '200px' },
    )
    obs.observe(sentinelRef.current)
    return () => obs.disconnect()
  }, [hasMore, loading])

  return (
    <div style={{ display: 'flex', flexDirection: 'column', gap: 20, padding: narrow ? '0 12px 16px' : '0 24px 24px', height: '100%', boxSizing: 'border-box', overflow: 'auto' }}>
      {/* Titre à gauche / contrôles à droite — TOUJOURS sur une seule ligne (empiler donnerait
          deux barres pleine largeur, moins lisible) : le bloc titre rétrécit (minWidth 0 +
          ellipsis) et le toggle passe en mode compact (icônes seules). */}
      <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', gap: narrow ? 8 : 16, paddingTop: narrow ? 16 : 24 }}>
        <div style={narrow ? { minWidth: 0 } : undefined}>
          <h1 style={{ fontSize: narrow ? 17 : 20, fontWeight: 700, margin: 0 }}>{t('title')}</h1>
          <p style={narrow
            ? { fontSize: 12, color: 'var(--color-muted-foreground)', margin: '2px 0 0', overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap' }
            : { fontSize: 14, color: 'var(--color-muted-foreground)', margin: '2px 0 0' }}>{t('subtitle')}</p>
        </div>
        <div style={{ display: 'flex', alignItems: 'center', gap: 8, flexShrink: 0 }}>
          <ViewToggle mode={mode} compact={narrow} onChange={(m) => { setMode(m); if (m === 'iframe') setFrameLoaded(true) }} />
          <button style={narrow ? { ...btnGhost, padding: '0 10px' } : btnGhost} onClick={refresh} title={t('refresh')}>↻</button>
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
        {!can('list') ? (
          <div style={{ ...card, padding: '40px 16px', textAlign: 'center', fontSize: 14, color: 'var(--color-muted-foreground)' }}>{t('no_list_access')}</div>
        ) : !marketAccessible ? (
          <div style={{ ...card, padding: '40px 16px', textAlign: 'center', fontSize: 14, color: 'var(--color-muted-foreground)' }}>{t('not_accessible')}</div>
        ) : (
          <div style={{ display: 'flex', flexDirection: narrow ? 'column' : 'row', gap: narrow ? 20 : 24, alignItems: 'flex-start' }}>
            <div style={{ flex: 1, minWidth: 0, width: narrow ? '100%' : undefined, display: 'flex', flexDirection: 'column', gap: 20 }}>
          <div style={{ display: 'flex', gap: 12, flexWrap: 'wrap' }}>
            <Kpi label={t('kpi_total')} value={stats?.total ?? null} icon={<Ic>{ICON_BOX}</Ic>} accent="#6366f1" />
            <Kpi label={t('kpi_installed')} value={stats?.installed ?? null} icon={<Ic>{ICON_CHECK}</Ic>} accent="#22c55e" />
            <Kpi label={t('kpi_need_update')} value={stats?.needUpdate ?? null} icon={<Ic>{ICON_UP}</Ic>} accent="#f59e0b" />
          </div>

          {/* Barre d'outils + filtres — collante en haut pendant le scroll de la grille
              (défilement infini) : on affine sans remonter. Fond = fond de page pour masquer
              les cartes qui défilent dessous. */}
          <div style={{ position: 'sticky', top: 0, zIndex: 5, background: 'var(--color-background,#fff)', paddingTop: 10, paddingBottom: 12, display: 'flex', flexDirection: 'column', gap: 12, borderBottom: '1px solid var(--color-border)', boxShadow: '0 -20px 0 0 var(--color-background,#fff)' }}>
            <div style={{ display: 'flex', gap: 8, flexWrap: 'wrap' }}>
              <div style={narrow ? { position: 'relative', flex: '1 1 100%' } : { position: 'relative', flex: 1, minWidth: 220 }}>
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"
                  strokeLinecap="round" strokeLinejoin="round"
                  style={{ position: 'absolute', left: 12, top: '50%', transform: 'translateY(-50%)', color: 'var(--color-muted-foreground)', pointerEvents: 'none' }}>
                  <circle cx="11" cy="11" r="8" /><path d="m21 21-4.3-4.3" />
                </svg>
                <input style={{ ...inputCss, width: '100%', paddingLeft: 32 }} value={searchInput} onChange={(e) => setSearchInput(e.target.value)}
                  onKeyDown={(e) => e.key === 'Enter' && changeSearch(searchInput.trim())} placeholder={t('search')} />
              </div>
              {/* Tri champ+direction en un seul select (valeur « champ:dir »). */}
              <select style={narrow ? { ...inputCss, flex: '1 1 100%', width: '100%' } : { ...inputCss, width: 'auto' }} value={`${orderBy}:${order}`}
                onChange={(e) => { const [f, d] = e.target.value.split(':'); changeSort(f, d as 'asc' | 'desc') }}>
                <option value="mp_total_downloads:desc">{t('sort_downloads')}</option>
                <option value="mp_date_added:desc">{t('sort_date')}</option>
                <option value="mp_title:asc">{t('sort_name')}</option>
              </select>
              {/* « Réinitialiser les filtres » : ligne pleine largeur sur mobile — le libellé FR
                  (~25 car.) passerait sur 2 lignes dans un demi-slot. */}
              <button style={narrow ? { ...btnGhost, flex: '1 1 100%', justifyContent: 'center' } : btnGhost} onClick={resetFilters}><ResetIcon />{t('reset_filters')}</button>
            </div>

            <GroupButtons groups={groups} value={group} onChange={changeGroup} bundle={bundle} onToggleBundle={toggleBundle} t={t} narrow={narrow} />

            {/* Compteur (chargés, « + » s'il reste des pages) + puces de filtres actifs. */}
            <div style={{ display: 'flex', alignItems: 'center', gap: 8, flexWrap: 'wrap', minHeight: 24 }}>
              <span style={{ display: 'inline-flex', alignItems: 'center', gap: 5, fontSize: 12, fontWeight: 600, color: 'var(--color-muted-foreground)' }}>
                <Ic size={13}>{ICON_BOX}</Ic>{items.length}{hasMore ? '+' : ''}
              </span>
              {hasActiveFilters && <span style={{ width: 1, height: 14, background: 'var(--color-border)' }} />}
              {search && <FilterChip onRemove={() => { setSearchInput(''); changeSearch('') }}>“{search}”</FilterChip>}
              {group && activeGroupName && (
                <FilterChip onRemove={() => changeGroup('')}>
                  <GroupLogo color={groupColor(activeGroupName)} size={14} />{activeGroupName}
                </FilterChip>
              )}
              {bundle && <FilterChip onRemove={toggleBundle}>{t('bundles')}</FilterChip>}
            </div>
          </div>

          <div>
            <style>{`
              .melis-mp-grid { display: grid; gap: 16px; grid-template-columns: repeat(3, minmax(0, 1fr)); }
              @media (max-width: 1180px) { .melis-mp-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
              @media (max-width: 760px) { .melis-mp-grid { grid-template-columns: minmax(0, 1fr); } }
              .melis-mp-sk { position: relative; overflow: hidden; background: var(--color-muted, rgba(0,0,0,.06)); }
              .melis-mp-sk::after { content: ''; position: absolute; inset: 0; transform: translateX(-100%); background: linear-gradient(90deg, transparent, rgba(255,255,255,.18), transparent); animation: melis-mp-sweep 1.4s infinite; }
              @keyframes melis-mp-sweep { 100% { transform: translateX(100%); } }
            `}</style>
            <div className="melis-mp-grid">
              {loading && page === 1 ? (
                Array.from({ length: 6 }).map((_, i) => <SkeletonCard key={`sk${i}`} />)
              ) : items.length === 0 ? (
                <div style={{ ...card, gridColumn: '1 / -1', padding: '48px 16px', textAlign: 'center', display: 'flex', flexDirection: 'column', alignItems: 'center', gap: 12 }}>
                  <span style={{ color: 'var(--color-muted-foreground)', opacity: 0.5 }}><Ic size={40}>{ICON_BOX}</Ic></span>
                  <div style={{ fontSize: 14, color: 'var(--color-muted-foreground)' }}>{t('empty')}</div>
                  {hasActiveFilters && <button style={btnGhost} onClick={resetFilters}><ResetIcon />{t('reset_filters')}</button>}
                </div>
              ) : items.map((pkg) => (
                <PackageCard key={pkg.id} pkg={pkg} t={t} onClick={() => onOpen(pkg.id)} />
              ))}
            </div>
          </div>

          {/* Défilement infini : sentinelle observée pour charger la page suivante */}
          {items.length > 0 && (
            <div ref={sentinelRef} style={{ display: 'flex', alignItems: 'center', justifyContent: 'center', padding: '8px 0', minHeight: 32 }}>
              {loading && page > 1 ? <Spinner size={20} /> : !hasMore && (
                <span style={{ fontSize: 12, color: 'var(--color-muted-foreground)' }}>{t('end_of_list')}</span>
              )}
            </div>
          )}
            </div>

            <Sidebar t={t} onOpen={onOpen} />
          </div>
        )}
      </div>
    </div>
  )
}

// ── Sidebar : info « module listing » + packages les plus téléchargés ──────
function Sidebar({ t, onOpen }: { t: (key: string, vars?: Record<string, string | number>) => string; onOpen: (id: number) => void }) {
  const narrow = useIsNarrow()
  const [top, setTop] = useState<PackageItem[]>([])

  useEffect(() => {
    fetchPackages({ limit: 5, orderBy: 'mp_total_downloads', order: 'desc' }).then((r) => setTop(r.items)).catch(() => null)
  }, [])

  // Narrow : la colonne latérale passe sous le contenu, pleine largeur et non collante
  // (sticky sur une colonne empilée figerait un bloc au milieu du scroll).
  return (
    <aside style={narrow
      ? { width: '100%', display: 'flex', flexDirection: 'column', gap: 20 }
      : { width: 280, flexShrink: 0, display: 'flex', flexDirection: 'column', gap: 20, position: 'sticky', top: 16, alignSelf: 'flex-start' }}>
      <div style={{ ...card, padding: 20 }}>
        <h5 style={{ fontSize: 14, fontWeight: 700, margin: '0 0 10px' }}>{t('listed_title')}</h5>
        <p style={{ fontSize: 13, color: 'var(--color-muted-foreground)', margin: '0 0 8px' }}>{t('listed_body')}</p>
        <pre style={{ background: 'var(--color-muted,rgba(0,0,0,.05))', borderRadius: 6, padding: '8px 10px', fontSize: 12, overflow: 'auto', margin: '0 0 10px' }}>
          <code>"type": "melisplatform-module"</code>
        </pre>
        <p style={{ fontSize: 13, color: 'var(--color-muted-foreground)', margin: 0 }}>
          {t('listed_after')}{' '}
          <a href="https://github.com/melisplatform/melis-cms/tree/master/etc/MarketPlace" target="_blank" rel="noreferrer" style={{ color: 'var(--color-primary)' }}>
            {t('listed_link')}
          </a>{' '}
          {t('listed_end')}
        </p>
      </div>

      <div style={{ ...card, padding: 20 }}>
        <h5 style={{ fontSize: 14, fontWeight: 700, margin: '0 0 12px' }}>{t('most_downloaded')}</h5>
        <div style={{ display: 'flex', flexDirection: 'column', gap: 12 }}>
          {top.map((pkg) => (
            <div key={pkg.id} onClick={() => onOpen(pkg.id)}
              style={{ display: 'flex', alignItems: 'center', gap: 10, cursor: 'pointer' }}>
              {pkg.image ? (
                <img src={pkg.image} data-legacy={pkg.imageLegacy ?? ''} alt="" onError={mpImgFallback} style={{ width: 44, height: 44, borderRadius: 8, objectFit: 'cover', flexShrink: 0 }} />
              ) : (
                <div style={{ width: 44, height: 44, borderRadius: 8, background: 'var(--color-muted,rgba(0,0,0,.06))', flexShrink: 0 }} />
              )}
              <div style={{ minWidth: 0, flex: 1 }}>
                <div style={{ fontSize: 13, fontWeight: 600, overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap' }}>{pkg.title || pkg.moduleName}</div>
                <span style={{ fontSize: 12, color: 'var(--color-muted-foreground)' }}>{t('downloads', { n: pkg.totalDownloads })}</span>
              </div>
            </div>
          ))}
        </div>
      </div>
    </aside>
  )
}

// ── Gestion native (install / update / remove) — reproduit le workflow legacy ──────
// Le JS jQuery de l'outil (melis-market-place.js) orchestre : confirmation → console de
// progression streamée (melisMarketPlaceProductDo) → reDumpAutoload → dbdeploy (récursif)
// → plugModule → scripts composer → activation ; pour « remove » : dépendances → permissions
// → stream remove → export des tables. On rejoue CETTE orchestration en React en appelant les
// MÊMES endpoints PHP (composer/dbdeploy restent côté serveur, non réimplémentés).
type ManageAction = 'require' | 'update' | 'remove'

const MP = '/melis/MelisMarketPlace/MelisMarketPlace/'
const XHR = { 'X-Requested-With': 'XMLHttpRequest' } as const

function esc(s: string): string {
  return s.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
}
async function mpPost(action: string, data: Record<string, string>): Promise<any> {
  const res = await fetch(MP + action, { method: 'POST', headers: { ...XHR }, credentials: 'include', body: new URLSearchParams(data) })
  return res.json()
}
async function corePost(path: string, data: Record<string, string>): Promise<any> {
  const res = await fetch('/melis/' + path, { method: 'POST', headers: { ...XHR }, credentials: 'include', body: new URLSearchParams(data) })
  return res.json()
}
async function mpGetText(action: string): Promise<string> {
  const res = await fetch(MP + action, { headers: { ...XHR }, credentials: 'include' })
  return res.text()
}

function ManageModal({ pkg, action, onClose }: { pkg: PackageDetailData; action: ManageAction; onClose: () => void }) {
  const t = useT()
  const narrow = useIsNarrow()
  const [phase, setPhase] = useState<'checking' | 'confirm' | 'blocked' | 'running' | 'done'>(action === 'remove' ? 'checking' : 'confirm')
  const [html, setHtml] = useState('')
  const [blocked, setBlocked] = useState<string[]>([])
  const [showActivate, setShowActivate] = useState(false)
  const [showReload, setShowReload] = useState(false)
  const [activating, setActivating] = useState(false)
  const meta = useRef<{ tables: string[]; files: string[] }>({ tables: [], files: [] })
  const consoleRef = useRef<HTMLDivElement>(null)

  const module = pkg.moduleName
  const packageName = pkg.name
  const busy = phase === 'running' && !showReload && !showActivate

  const push = (h: string) => setHtml((prev) => prev + h)
  const line = (msg: string, color?: string) => push(`\n<span${color ? ` style="color:${color}"` : ''}>${msg}</span>\n`)
  const okLine = (m: string) => line('✓ ' + m, '#22c55e')
  const warnLine = (m: string) => line('▲ ' + m, '#eab308')
  const errLine = (m: string) => line('✕ ' + m, '#ef4444')

  useEffect(() => { const el = consoleRef.current; if (el) el.scrollTop = el.scrollHeight }, [html])

  // Remove: pré-vérifier les dépendances (modules qui dépendent de celui-ci) avant de confirmer.
  useEffect(() => {
    if (action !== 'remove') return
    let cancelled = false
    ;(async () => {
      try {
        const tbl = await mpPost('getModuleTables', { module })
        meta.current = { tables: tbl.tables || [], files: tbl.files || [] }
        const dep = await corePost('MelisCore/Modules/getDependents', { module })
        if (cancelled) return
        if (dep.success && Array.isArray(dep.modules) && dep.modules.length) { setBlocked(dep.modules); setPhase('blocked') }
        else setPhase('confirm')
      } catch { if (!cancelled) setPhase('confirm') }
    })()
    return () => { cancelled = true }
  }, [action, module])

  async function streamProductDo(act: string) {
    const body = new FormData()
    body.append('action', act); body.append('package', packageName); body.append('module', module)
    const res = await fetch(MP + 'melisMarketPlaceProductDo', { method: 'POST', headers: { ...XHR }, credentials: 'include', body })
    const reader = res.body?.getReader()
    if (reader) {
      const dec = new TextDecoder()
      for (;;) {
        const { done, value } = await reader.read()
        if (done) break
        const chunk = dec.decode(value, { stream: true })
        // The backend streams already-formatted HTML (colored <span>s) — render as-is (like the
        // legacy console does), NOT escaped, else the raw tags show up as literal text.
        if (chunk) push(chunk)
      }
    }
    // Composer re-dump autoload (comme le .done() legacy), puis on continue.
    await fetch(MP + 'reDumpAutoload', { headers: { ...XHR }, credentials: 'include' }).catch(() => null)
    okLine(t('mng_exec_done'))
  }

  async function runDbDeploy(depth = 0): Promise<void> {
    const r = await mpPost('execDbDeploy', { module })
    if (r && r.success === -1 && depth < 30) return runDbDeploy(depth + 1)
  }

  async function exportTables() {
    const { tables, files } = meta.current
    const body = new FormData()
    body.append('module', module)
    tables.forEach((tb) => body.append('tables[]', tb))
    files.forEach((f) => body.append('files[]', f))
    const res = await fetch(MP + 'exportTables', { method: 'POST', headers: { ...XHR }, credentials: 'include', body })
    if (res.headers.get('error') === '0') {
      const fileName = res.headers.get('fileName') || (module.toLowerCase() + '_export_data.sql')
      const blob = await res.blob()
      const url = URL.createObjectURL(blob)
      const a = document.createElement('a'); a.href = url; a.download = fileName; a.click(); URL.revokeObjectURL(url)
      okLine(t('mng_table_dump_ok'))
    }
  }

  async function run() {
    setPhase('running')
    try {
      if (action === 'remove') {
        okLine(t('mng_start_remove', { module }))
        const rem = await mpPost('isPackageDirectoryRemovable', { module })
        if (!(rem && (rem.success === 1 || rem.success === '1'))) {
          await mpPost('changePackageDirectoryPermission', { module }).catch(() => null)
          warnLine(t('mng_perm_changed'))
        }
        await streamProductDo('remove')
        const exists = await mpPost('isModuleExists', { module })
        if (exists && !exists.isExist) {
          okLine(t('mng_remove_ok', { module }))
          if (meta.current.tables.length) { warnLine(t('mng_table_dump')); await exportTables() }
          setShowReload(true)
        } else {
          errLine(t('mng_remove_ko', { module }))
        }
      } else {
        okLine(t(action === 'update' ? 'mng_start_update' : 'mng_start_require', { module }))
        await streamProductDo(action)
        const exists = await mpPost('isModuleExists', { module })
        if (!exists || !exists.isExist) { errLine(t('mng_not_found')); setPhase('done'); return }
        line(t('mng_dbdeploy'))
        await runDbDeploy()
        okLine(t('mng_dbdeploy_ok'))
        // Charger temporairement le module (pour scripts + détection du formulaire de setup).
        await mpPost('plugModule', { module }).catch(() => null)
        line(t('mng_composer_scripts'))
        const scripts = await mpGetText('executeComposerScripts').catch(() => '')
        if (scripts && scripts.trim()) push(scripts)
        const setup = await mpPost('getSetupModuleForm', { action, module }).catch(() => null)
        await mpPost('unplugModule', { module }).catch(() => null)
        if (setup && setup.form) warnLine(t('mng_setup_form_note'))
        okLine(t('mng_download_done'))
        setShowActivate(true); setShowReload(true)
      }
    } catch (e) {
      errLine(String(e) || t('mng_error'))
    } finally {
      setPhase('done')
    }
  }

  async function activate() {
    setActivating(true)
    try {
      await mpPost('activateModule', { module })
      await fetch('/melis', { credentials: 'include' }).catch(() => null)
      window.location.reload()
    } catch {
      setActivating(false)
      errLine(t('mng_error'))
    }
  }

  const title = pkg.title || module
  const confirmMsg = t(action === 'remove' ? 'mng_confirm_remove' : action === 'update' ? 'mng_confirm_update' : 'mng_confirm_require', { module })

  return (
    <div style={{ position: 'fixed', inset: 0, zIndex: 50, display: 'flex', alignItems: 'center', justifyContent: 'center', background: 'rgba(0,0,0,.5)' }}
      onClick={() => { if (!busy) onClose() }}>
      <div style={{ ...card, width: '92vw', maxWidth: 780, maxHeight: '88vh', display: 'flex', flexDirection: 'column', overflow: 'hidden' }}
        onClick={(e) => e.stopPropagation()}>
        <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', padding: '12px 16px', borderBottom: '1px solid var(--color-border)', flexShrink: 0 }}>
          <h3 style={narrow
            ? { fontSize: 13, fontWeight: 700, margin: 0, minWidth: 0, overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap' }
            : { fontSize: 14, fontWeight: 700, margin: 0 }}>{t('manage_title')} — {title}</h3>
          {!busy && <button style={{ ...btnGhost, height: 30, width: 30, padding: 0, justifyContent: 'center' }} onClick={onClose}>✕</button>}
        </div>

        <div style={{ padding: narrow ? 14 : 20, overflow: 'auto', display: 'flex', flexDirection: 'column', gap: 16 }}>
          {phase === 'checking' && (
            <div style={{ display: 'flex', alignItems: 'center', gap: 10, fontSize: 14, color: 'var(--color-muted-foreground)' }}>
              <Spinner size={20} /> {t('mng_checking')}
            </div>
          )}

          {phase === 'blocked' && (
            <div style={{ display: 'flex', flexDirection: 'column', gap: 12 }}>
              <div style={{ fontSize: 15, fontWeight: 600, color: 'var(--color-destructive,#ef4444)' }}>{t('mng_blocked_title')}</div>
              <p style={{ fontSize: 14, margin: 0, color: 'var(--color-foreground)' }}>{t('mng_blocked_body', { module })}</p>
              <ul style={{ margin: 0, paddingLeft: 20, fontSize: 14 }}>{blocked.map((m) => <li key={m}>{m}</li>)}</ul>
              <div style={{ display: 'flex', justifyContent: 'flex-end' }}><button style={btnGhost} onClick={onClose}>{t('mng_close')}</button></div>
            </div>
          )}

          {phase === 'confirm' && (
            <div style={{ display: 'flex', flexDirection: 'column', gap: 16 }}>
              <p style={{ fontSize: 14, margin: 0, lineHeight: 1.6 }}>{confirmMsg}</p>
              <div style={{ display: 'flex', justifyContent: 'flex-end', gap: 8 }}>
                <button style={btnGhost} onClick={onClose}>{t('mng_cancel')}</button>
                <button style={{ ...btnGhost, background: action === 'remove' ? 'var(--color-destructive,#ef4444)' : 'var(--color-primary)', color: '#fff', borderColor: 'transparent' }}
                  onClick={run}>{t('mng_confirm_btn')}</button>
              </div>
            </div>
          )}

          {(phase === 'running' || phase === 'done') && (
            <>
              <div ref={consoleRef} style={{ background: '#0b1020', color: '#d7dbe8', borderRadius: 8, padding: 14, fontFamily: 'ui-monospace, SFMono-Regular, Menlo, monospace', fontSize: 12.5, lineHeight: 1.55, whiteSpace: 'pre-wrap', wordBreak: 'break-word', minHeight: 160, maxHeight: '48vh', overflow: 'auto' }}
                dangerouslySetInnerHTML={{ __html: html || '' }} />
              {busy && <div style={{ display: 'flex', alignItems: 'center', gap: 10, fontSize: 13, color: 'var(--color-muted-foreground)' }}><Spinner size={18} /> …</div>}
              {(showActivate || showReload) && (
                <div style={{ display: 'flex', justifyContent: 'flex-end', gap: 8 }}>
                  {showActivate && (
                    <button style={{ ...btnGhost, background: 'var(--color-primary)', color: 'var(--color-primary-foreground,#fff)', borderColor: 'transparent' }}
                      onClick={activate} disabled={activating}>{activating ? t('mng_activating') : t('mng_activate')}</button>
                  )}
                  {showReload && <button style={btnGhost} onClick={() => window.location.reload()}>{t('mng_reload')}</button>}
                </div>
              )}
            </>
          )}
        </div>
      </div>
    </div>
  )
}

/**
 * Repli d'image marketplace : l'API React sert les NOUVELLES captures (branche melis-react,
 * sous-dossier react/) dans `src`, et l'URL d'ORIGINE (legacy) dans `data-legacy`. Si l'image
 * React ne charge pas (module sans images react, ou sans branche melis-react), on bascule sur la
 * legacy fournie. Le flag data-fallback évite toute boucle si la legacy manque aussi.
 */
function mpImgFallback(e: { currentTarget: HTMLImageElement }): void {
  const img = e.currentTarget
  if (img.dataset.fallback === 'done') return
  img.dataset.fallback = 'done'
  // L'URL d'origine (legacy) est fournie explicitement par l'API via data-legacy : la React pointe
  // sur la branche melis-react, on ne peut donc plus la dériver par simple remplacement de chemin.
  const legacy = img.dataset.legacy
  if (legacy && legacy !== img.src) img.src = legacy
}

// ── Galerie d'images (slider + lightbox) pour le détail d'un package ──────────
function ImageGallery({ images, legacy = [] }: { images: string[]; legacy?: string[] }) {
  const [active, setActive] = useState(0)
  const [zoom, setZoom] = useState(false)   // lightbox plein écran ouvert ?

  useEffect(() => { setActive(0); setZoom(false) }, [images])

  const prev = useCallback(() => setActive((i) => (i - 1 + images.length) % images.length), [images.length])
  const next = useCallback(() => setActive((i) => (i + 1) % images.length), [images.length])

  // Navigation clavier quand le lightbox est ouvert (← → Échap), et blocage du scroll de fond.
  useEffect(() => {
    if (!zoom) return
    const onKey = (e: KeyboardEvent) => {
      if (e.key === 'Escape') setZoom(false)
      else if (e.key === 'ArrowLeft') prev()
      else if (e.key === 'ArrowRight') next()
    }
    window.addEventListener('keydown', onKey)
    const prevOverflow = document.body.style.overflow
    document.body.style.overflow = 'hidden'
    return () => { window.removeEventListener('keydown', onKey); document.body.style.overflow = prevOverflow }
  }, [zoom, prev, next])

  if (images.length === 0) return null

  return (
    <div style={{ display: 'flex', flexDirection: 'column', gap: 8 }}>
      {/* Image principale — clic = ouvrir le lightbox */}
      <div style={{ position: 'relative' }}>
        <img
          src={images[active]}
          data-legacy={legacy[active] ?? ''}
          alt=""
          onError={mpImgFallback}
          onClick={() => setZoom(true)}
          // `contain` (et non `cover`) : on montre la capture ENTIÈRE à son format d'origine, sans la
          // rogner en 16:9 (on perdait beaucoup de l'écran du module). Fond neutre pour d'éventuelles
          // bandes de letterbox quand le ratio de l'image diffère du conteneur.
          style={{ width: '100%', maxHeight: 460, objectFit: 'contain', background: 'var(--color-muted,rgba(0,0,0,.04))', borderRadius: 12, border: '1px solid var(--color-border)', cursor: 'zoom-in', display: 'block' }}
        />
        {/* Indice « agrandir » en haut à droite */}
        <div style={{ position: 'absolute', top: 8, right: 8, width: 30, height: 30, borderRadius: 8, background: 'rgba(0,0,0,0.5)', color: '#fff', display: 'flex', alignItems: 'center', justifyContent: 'center', fontSize: 15, pointerEvents: 'none' }}>⤢</div>
        {images.length > 1 && (<>
          <button aria-label="Précédent" onClick={prev} style={sliderArrow('left', 32)}>‹</button>
          <button aria-label="Suivant" onClick={next} style={sliderArrow('right', 32)}>›</button>
          <div style={{ position: 'absolute', bottom: 8, left: '50%', transform: 'translateX(-50%)', padding: '2px 10px', borderRadius: 999, background: 'rgba(0,0,0,0.55)', color: '#fff', fontSize: 11, fontWeight: 600, pointerEvents: 'none' }}>{active + 1} / {images.length}</div>
        </>)}
      </div>

      {/* Miniatures */}
      {images.length > 1 && (
        <div style={{ display: 'flex', gap: 8, overflowX: 'auto', paddingBottom: 2 }}>
          {images.map((img, i) => (
            <img
              key={i}
              src={img}
              data-legacy={legacy[i] ?? ''}
              alt=""
              onError={mpImgFallback}
              onClick={() => setActive(i)}
              style={{
                width: 72, height: 48, objectFit: 'cover', borderRadius: 6, cursor: 'pointer', flexShrink: 0,
                border: `2px solid ${i === active ? 'var(--color-primary)' : 'var(--color-border)'}`,
                opacity: i === active ? 1 : 0.6, transition: 'opacity .15s',
              }}
            />
          ))}
        </div>
      )}

      {/* Lightbox plein écran */}
      {zoom && (
        <div
          onClick={() => setZoom(false)}
          style={{ position: 'fixed', inset: 0, zIndex: 9999, background: 'rgba(0,0,0,0.88)', display: 'flex', alignItems: 'center', justifyContent: 'center', padding: 24, animation: 'melisMpFadeIn .12s ease-out' }}
        >
          <style>{'@keyframes melisMpFadeIn{from{opacity:0}to{opacity:1}}'}</style>
          {/* Fermer */}
          <button aria-label="Fermer" onClick={() => setZoom(false)}
            style={{ position: 'absolute', top: 16, right: 20, width: 40, height: 40, borderRadius: '50%', border: 'none', cursor: 'pointer', background: 'rgba(255,255,255,0.12)', color: '#fff', fontSize: 22, lineHeight: '40px', padding: 0 }}>×</button>

          {/* Image (clic dessus ne ferme pas) */}
          <img
            src={images[active]}
            alt=""
            onError={mpImgFallback}
            onClick={(e) => e.stopPropagation()}
            style={{ maxWidth: '90vw', maxHeight: '86vh', objectFit: 'contain', borderRadius: 8, boxShadow: '0 8px 40px rgba(0,0,0,0.5)' }}
          />

          {images.length > 1 && (<>
            <button aria-label="Précédent" onClick={(e) => { e.stopPropagation(); prev() }} style={lightboxArrow('left')}>‹</button>
            <button aria-label="Suivant" onClick={(e) => { e.stopPropagation(); next() }} style={lightboxArrow('right')}>›</button>
            <div style={{ position: 'absolute', bottom: 20, left: '50%', transform: 'translateX(-50%)', padding: '4px 14px', borderRadius: 999, background: 'rgba(255,255,255,0.14)', color: '#fff', fontSize: 13, fontWeight: 600 }}>{active + 1} / {images.length}</div>
          </>)}
        </div>
      )}
    </div>
  )
}

function sliderArrow(side: 'left' | 'right', size: number): CSSProperties {
  return {
    position: 'absolute', top: '50%', transform: 'translateY(-50%)', [side]: 8,
    width: size, height: size, borderRadius: '50%', border: 'none', cursor: 'pointer',
    background: 'rgba(0,0,0,0.5)', color: '#fff', fontSize: 18, lineHeight: `${size}px`, padding: 0,
  } as CSSProperties
}

function lightboxArrow(side: 'left' | 'right'): CSSProperties {
  return {
    position: 'absolute', top: '50%', transform: 'translateY(-50%)', [side]: 16,
    width: 48, height: 48, borderRadius: '50%', border: 'none', cursor: 'pointer',
    background: 'rgba(255,255,255,0.14)', color: '#fff', fontSize: 30, lineHeight: '48px', padding: 0,
  } as CSSProperties
}

// ── Détail d'un package (natif) + gestion native (install/update/remove) ──────
function PackageDetail({ id, onBack, onOpen }: { id: number; onBack: () => void; onOpen: (id: number) => void }) {
  const t = useT()
  const narrow = useIsNarrow()
  // Droits « capacités » (onglet Rights du formulaire Utilisateur, cf. config/react.capabilities.php) :
  //   download → Télécharger (installer) ET Mettre à jour (même geste composer) · remove → Supprimer.
  // Default-allow (admin ou cap non déclarée → permis). Masquage UI seulement — actions legacy.
  const { can } = useCaps(MELIS_KEY)
  const [pkg, setPkg] = useState<PackageDetailData | null>(null)
  const [error, setError] = useState('')
  const [manage, setManage] = useState<ManageAction | null>(null)

  useEffect(() => {
    setPkg(null); setError(''); setManage(null)
    fetchPackageById(id).then(setPkg).catch((e) => setError(String(e)))
  }, [id])

  if (error) return (
    <div style={{ padding: 24 }}>
      <button style={{ ...btnGhost, height: 32, padding: '0 10px', marginBottom: 16 }} onClick={onBack}>← {t('back')}</button>
      <div style={{ color: 'var(--color-destructive,#ef4444)', fontSize: 14 }}>{error}</div>
    </div>
  )
  if (!pkg) return <div style={{ padding: 24, fontSize: 14, color: 'var(--color-muted-foreground)' }}>{t('loading')}</div>

  return (
    <div style={{ height: '100%', minHeight: 0, overflow: 'auto' }}>
      <div style={{ padding: narrow ? 12 : 24, display: 'flex', flexDirection: narrow ? 'column' : 'row', gap: narrow ? 20 : 24, alignItems: 'flex-start' }}>
        <div style={{ flex: 1, minWidth: 0, width: narrow ? '100%' : undefined, display: 'flex', flexDirection: 'column', gap: 20 }}>
        <div>
          <button style={{ ...btnGhost, height: 32, padding: '0 10px', marginBottom: 12 }} onClick={onBack}>← {t('back')}</button>
          {/* Bandeau héro : fond dégradé teinté par groupe + filigrane du logo → allure « page produit ». */}
          <div style={{
            position: 'relative', overflow: 'hidden', borderRadius: 12, padding: narrow ? 14 : 20,
            border: '1px solid var(--color-border)',
            background: pkg.groupName
              ? `linear-gradient(135deg, ${groupTint(pkg.groupName, 16)} 0%, var(--color-card) 62%)`
              : 'var(--color-card)',
          }}>
            {pkg.groupName && (
              <div style={{ position: 'absolute', top: -24, right: -12, opacity: 0.1, pointerEvents: 'none' }}>
                <GroupLogo color={groupColor(pkg.groupName)} size={150} />
              </div>
            )}
            <div style={{ position: 'relative', zIndex: 1 }}>
          <div style={{ display: 'flex', alignItems: 'center', gap: 10, flexWrap: 'wrap' }}>
            {pkg.groupName && <GroupLogo color={groupColor(pkg.groupName)} size={narrow ? 22 : 26} />}
            <h1 style={{ fontSize: narrow ? 18 : 22, fontWeight: 700, margin: 0, minWidth: 0 }}>{pkg.title || pkg.moduleName}</h1>
            {pkg.installed && <Badge kind="installed">{t('installed_badge')}</Badge>}
            {pkg.versionStatus === 'need_update' && <Badge kind="update">{t('need_update_badge')}</Badge>}
            {/* Narrow : pas d'espaceur (il pousserait les boutons hors ligne) — chaque bouton
                d'action prend sa propre ligne pleine largeur, cible tactile confortable. */}
            {!narrow && <div style={{ flex: 1 }} />}
            {pkg.isPrivate ? (
              <button style={{ ...btnGhost, ...(narrow ? actionBtnNarrow : null), cursor: 'not-allowed', opacity: 0.75 }} disabled>🔒 {t('btn_private')}</button>
            ) : (<>
              {!pkg.installed && can('download') && (
                <button style={{ ...btnGhost, ...(narrow ? actionBtnNarrow : null), background: 'var(--color-primary)', color: 'var(--color-primary-foreground,#fff)', borderColor: 'transparent' }}
                  onClick={() => setManage('require')}>↓ {t('btn_download')}</button>
              )}
              {pkg.installed && pkg.versionStatus === 'need_update' && can('download') && (
                <button style={{ ...btnGhost, ...(narrow ? actionBtnNarrow : null), background: 'var(--color-primary)', color: 'var(--color-primary-foreground,#fff)', borderColor: 'transparent' }}
                  onClick={() => setManage('update')}>↑ {t('btn_update')}</button>
              )}
              {pkg.installed && !pkg.isExempted && can('remove') && (
                <button style={{ ...btnGhost, ...(narrow ? actionBtnNarrow : null), color: 'var(--color-destructive,#ef4444)', borderColor: 'var(--color-destructive,#ef4444)' }}
                  onClick={() => setManage('remove')}>✕ {t('btn_remove')}</button>
              )}
            </>)}
          </div>
          <p style={{ fontSize: narrow ? 13 : 14, color: 'var(--color-muted-foreground)', margin: '8px 0 0' }}>{stripHtml(pkg.subtitle)}</p>
            </div>
          </div>
        </div>

        <ImageGallery
          images={pkg.images && pkg.images.length > 0 ? pkg.images : (pkg.image ? [pkg.image] : [])}
          legacy={pkg.images && pkg.images.length > 0 ? (pkg.imagesLegacy ?? []) : (pkg.imageLegacy ? [pkg.imageLegacy] : [])} />

        <p style={{ fontSize: 14, lineHeight: 1.6, color: 'var(--color-foreground)', margin: 0, whiteSpace: 'pre-line' }}>{stripHtml(pkg.description)}</p>

        <div style={{ ...card, padding: narrow ? 14 : 20 }}>
          <h3 style={{ fontSize: 13, fontWeight: 700, textTransform: 'uppercase', letterSpacing: '.04em', color: 'var(--color-muted-foreground)', margin: '0 0 12px' }}>{t('additional_info')}</h3>
          {/* Narrow : colonne « libellé » élastique et plus étroite — la colonne fixe de 150px
              ne laissait plus assez de place à la valeur (URLs, nom de package). */}
          <div style={{ display: 'grid', gridTemplateColumns: narrow ? '18px minmax(0,90px) minmax(0,1fr)' : '20px 150px 1fr', rowGap: 12, columnGap: narrow ? 8 : 10, alignItems: 'center', fontSize: narrow ? 12.5 : 13 }}>
            <span style={{ color: 'var(--color-muted-foreground)', display: 'inline-flex' }}><Ic>{ICON_TAG}</Ic></span>
            <span style={{ color: 'var(--color-muted-foreground)' }}>{t('latest_version')}</span><span>{fmtVersion(pkg.version)}</span>
            {pkg.installed && (<>
              <span style={{ color: 'var(--color-muted-foreground)', display: 'inline-flex' }}><Ic>{ICON_CHECK}</Ic></span>
              <span style={{ color: 'var(--color-muted-foreground)' }}>{t('current_version')}</span>
              <span>{pkg.currentVersion ? fmtVersion(pkg.currentVersion) : '—'}</span>
            </>)}
            {pkg.repository && (<>
              <span style={{ color: 'var(--color-muted-foreground)', display: 'inline-flex' }}><Ic>{ICON_GITHUB}</Ic></span>
              <span style={{ color: 'var(--color-muted-foreground)' }}>{t('github')}</span>
              <a href={pkg.repository} target="_blank" rel="noreferrer" style={{ color: 'var(--color-primary)', overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap' }}>{pkg.repository}</a>
            </>)}
            {pkg.url && (<>
              <span style={{ color: 'var(--color-muted-foreground)', display: 'inline-flex' }}><Ic>{ICON_LINK}</Ic></span>
              <span style={{ color: 'var(--color-muted-foreground)' }}>{t('packagist')}</span>
              <a href={pkg.url} target="_blank" rel="noreferrer" style={{ color: 'var(--color-primary)', overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap' }}>{pkg.url}</a>
            </>)}
            <span style={{ color: 'var(--color-muted-foreground)', display: 'inline-flex' }}><Ic>{ICON_BOX}</Ic></span>
            <span style={{ color: 'var(--color-muted-foreground)' }}>{t('package_name')}</span><span style={{ fontFamily: 'monospace', overflowWrap: 'anywhere' }}>{pkg.name}</span>
            <span style={{ color: 'var(--color-muted-foreground)', display: 'inline-flex' }}><Ic>{ICON_DOWNLOAD}</Ic></span>
            <span style={{ color: 'var(--color-muted-foreground)' }}>{t('downloads_label')}</span>
            <span>{pkg.totalDownloads.toLocaleString()}</span>
          </div>
        </div>

        {pkg.isPrivate && (
          <div style={{ ...card, padding: narrow ? 14 : 20 }}>
            <h3 style={{ fontSize: 14, fontWeight: 700, margin: '0 0 8px' }}>🔒 {t('private_title')}</h3>
            <p style={{ fontSize: 13, color: 'var(--color-muted-foreground)', margin: '0 0 8px', lineHeight: 1.6 }}>{t('private_body')}</p>
            <p style={{ fontSize: 13, margin: 0, lineHeight: 1.8 }}>
              <a href="mailto:contact@melistechnology.com" style={{ color: 'var(--color-primary)' }}>contact@melistechnology.com</a><br />
              (+33) 972 386 280<br />
              <a href="https://www.melistechnology.com/transversal/contact-us/id/37" target="_blank" rel="noreferrer" style={{ color: 'var(--color-primary)' }}>{t('private_form')}</a>
            </p>
          </div>
        )}
        </div>

        <Sidebar t={t} onOpen={onOpen} />
      </div>

      {/* Native manage flow — reproduces the legacy download/update/remove workflow (progress
          console, dbdeploy, activate) by calling the same PHP endpoints. Not an iframe. */}
      {manage && <ManageModal pkg={pkg} action={manage} onClose={() => setManage(null)} />}
    </div>
  )
}

