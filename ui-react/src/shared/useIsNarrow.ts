import { useEffect, useState } from 'react'

/**
 * True when the viewport is narrower than `breakpoint`.
 *
 * Single source of truth for every responsive decision in this brick: all mobile/desktop
 * differences are expressed as inline-style ternaries driven by this boolean, never as CSS
 * media queries / Tailwind `sm:` classes (a brick can't use the host's Tailwind anyway, and
 * a JS ternary produces exactly ONE style at a time — no cascade fight, hence zero risk of
 * mobile styling bleeding into the desktop view).
 */
export function useIsNarrow(breakpoint = 640): boolean {
  const [narrow, setNarrow] = useState(() => window.innerWidth < breakpoint)
  useEffect(() => {
    const onResize = () => setNarrow(window.innerWidth < breakpoint)
    window.addEventListener('resize', onResize)
    return () => window.removeEventListener('resize', onResize)
  }, [breakpoint])
  return narrow
}
