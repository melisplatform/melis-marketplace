import { type CSSProperties } from 'react'

/* "New (React) / Old (legacy tool in iframe)" toggle — brick-local copy (melis-cms bricks ship
 * one too; bricks can't import each other, only React/ReactRouter are host globals). Inline
 * styles + theme CSS vars. The host page owns the `mode` state + the "Old" iframe mount. */

export type ViewMode = 'react' | 'iframe'

const sIcon = { width: 15, height: 15, flexShrink: 0 } as const
// "code" icon (lucide code-xml) for the New (React) view — same icon as the other bricks.
const CodeIcon = () => <svg style={sIcon} viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true"><path d="m18 16 4-4-4-4" /><path d="m6 8-4 4 4 4" /><path d="m14.5 4-5 16" /></svg>
const LayoutIcon = () => <svg style={sIcon} viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" /><path d="M3 9h18M9 21V9" /></svg>

/**
 * `compact` (opt-in, default false → every existing call site is untouched): icon-only, for
 * narrow viewports. The toggle is never dropped on mobile, only shrunk — the labels move to
 * the `title` tooltip so the control stays discoverable and accessible.
 */
export function ViewToggle({ mode, onChange, compact = false }: { mode: ViewMode; onChange: (m: ViewMode) => void; compact?: boolean }) {
  const tab = (active: boolean): CSSProperties => ({ display: 'inline-flex', alignItems: 'center', justifyContent: 'center', gap: compact ? 0 : 6, height: 30, padding: compact ? '0 8px' : '0 12px', borderRadius: 6, border: 0, fontSize: 12, fontWeight: 500, cursor: 'pointer', background: active ? 'var(--color-card)' : 'transparent', color: active ? 'var(--color-foreground)' : 'var(--color-muted-foreground)', boxShadow: active ? '0 1px 2px rgba(0,0,0,.06)' : 'none' })
  return (
    <div style={{ display: 'inline-flex', gap: 4, padding: 4, borderRadius: 8, border: '1px solid var(--color-border)', background: 'color-mix(in srgb, var(--color-muted,#888) 12%, transparent)' }}>
      <button style={tab(mode === 'react')} onClick={() => onChange('react')} title="New"><CodeIcon />{!compact && 'New'}</button>
      <button style={tab(mode === 'iframe')} onClick={() => onChange('iframe')} title="Old"><LayoutIcon />{!compact && 'Old'}</button>
    </div>
  )
}
