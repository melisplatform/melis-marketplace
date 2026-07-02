import { defineConfig } from 'vite'
import path from 'node:path'

/**
 * Build for the MelisMarketPlace React brick.
 *
 * Produces a single IIFE bundle (public/ui-react/brick.js) loaded at runtime by the
 * MelisCore React shell when the module is active. React / ReactRouter are EXTERNAL,
 * mapped to the host globals exposed in MelisCore's main.tsx — so the brick reuses the
 * host React instance (hooks, context, Router all work across the boundary).
 */
export default defineConfig({
  // esbuild handles the automatic JSX runtime; emitted `react/jsx-runtime` import is externalised.
  esbuild: { jsx: 'automatic' },
  build: {
    outDir: path.resolve(import.meta.dirname, '..', 'public', 'ui-react'),
    // Keep brick.manifest.json (authored by hand) next to the bundle.
    emptyOutDir: false,
    lib: {
      entry: path.resolve(import.meta.dirname, 'src/brick.tsx'),
      formats: ['iife'],
      name: 'MelisMarketPlaceBrick',
      fileName: () => 'brick.js',
    },
    rollupOptions: {
      external: ['react', 'react-dom', 'react/jsx-runtime', 'react-router-dom'],
      output: {
        globals: {
          react: 'MelisReact',
          'react-dom': 'MelisReactDOM',
          'react/jsx-runtime': 'MelisReactJsxRuntime',
          'react-router-dom': 'MelisReactRouterDOM',
        },
      },
    },
  },
})
