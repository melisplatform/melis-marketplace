import MarketPlacePage from './MarketPlacePage'

/**
 * Brick entry point. Registers the Market Place tool page with the MelisCore React
 * shell. React / ReactRouter are EXTERNAL (host globals) so hooks/Router/context are shared.
 */
declare global {
  interface Window {
    __melisRegisterBrick?: (b: { id: string; Component: unknown }) => void
  }
}

window.__melisRegisterBrick?.({ id: 'marketplace', Component: MarketPlacePage })
