## Development Setup

### Prerequisites

- Node.js 18+
- npm

### Getting Started

- `git clone https://github.com/LeadsForwardLLC/jobcapturepro-plugin`
- `cd jobcapturepro-plugin`
- `npm install` - Install dependecies
- `npm run build` - Build for production
- `npm run dev` - Development build with watch

### Source layout

- `src/core/` — framework-agnostic modules (API client, renderers, map/slider/pagination). Must not reference WordPress globals.
- `src/wp/` — thin adapters that read `wp_localize_script` data and boot the core modules; built to `dist/js/` for the WP plugin.
- `src/embed/` — the `<jcp-widget>` custom element for non-WordPress sites (Wix etc.); built to `dist/embed/jcp-embed.min.js`. See [docs/embed.md](docs/embed.md).
