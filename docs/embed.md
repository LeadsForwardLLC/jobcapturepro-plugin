# JobCapturePro JavaScript Embed

A framework-agnostic embed of the JobCapturePro widgets for non-WordPress
sites (Wix, Squarespace, Webflow, plain HTML). It renders the same
components as the WordPress shortcodes, driven by the same API, from a
single self-contained script: `dist/embed/jcp-embed.min.js`.

> **⚠️ Shipping gate:** the embed places the customer's API key in
> page markup, same as the WP plugin already does via
> `wp_localize_script`. Per team decision (Aug 2026), the embed does
> **not ship to customers** until the API supports domain-restricted
> embed keys. Development and internal testing may proceed.

## Usage

```html
<script src="https://WIDGET_HOST_TBD/v1/jcp-embed.min.js" async></script>

<jcp-widget
    component="combined"
    companyid="COMPANY_ID"
    api-key="API_KEY"
></jcp-widget>
```

The bundle is fully self-contained: Swiper, the Google Maps loader,
icons, and the compiled stylesheet (injected once as a `<style>` tag)
are all included. The Google Maps API key is delivered by the `/map`
API response, exactly as on WordPress.

### Attributes

| Attribute | Meaning |
| --- | --- |
| `component` | `combined` (default) \| `checkins` \| `map` \| `company-info` \| `checkin` |
| `api-key` | **Required.** JobCapturePro API key (Bearer token) |
| `api-base` | Override the API base URL (defaults to production) |
| *everything else* | Forwarded to the API as query params, mirroring WP shortcode atts. Dashes are stripped: `companyid` / `company-id`, `checkinid` / `checkin-id`, `pagesize`, `state`, … |

### Components

- `combined` — company info header + check-ins map + slider + footer
  (equivalent of `[jobcapturepro_combined]`)
- `checkins` — check-ins slider with infinite pagination
  (`[jobcapturepro_checkins]`); with `checkinid` and a single result it
  renders the single check-in detail view
- `map` — clustered check-ins map (`[jobcapturepro_map]`)
- `company-info` — company header only (`[jobcapturepro_company_info]`, requires `companyid`)
- `checkin` — one specific check-in as a single-card slider
  (`[jobcapturepro_checkin]`, requires `checkinid`)

## Wix integration

Requires a paid Wix plan with a connected domain (custom elements do
not run on free `*.wixsite.com` sites).

1. In the Wix editor: **Add Elements → Embed Code → Custom Element**.
2. Set *Server URL* to the hosted bundle URL, *Tag name* to `jcp-widget`.
3. In the element's **Set Attributes** panel add `component`,
   `companyid`, and `api-key`.
4. Size the container generously — the combined view is tall (header +
   500px map + slider). Wix gives the element a fixed-height box.

Fallback if the plan doesn't support custom elements: host a minimal
page using the embed and add it via **Embed a Site** (iframe).

## Prerequisites before a customer go-live

- [ ] **Embed-scoped keys** — API team ships domain-restricted keys
      (the gate above); the widget needs no code change, the customer
      just uses the scoped key in `api-key`.
- [ ] **CORS** — confirm the API allows the customer's domain (browser
      calls are already made from WP sites today, so this is likely
      already permissive).
- [ ] **Google Maps key referrers** — the Maps key returned by `/map`
      must allow the customer's domain in Google Cloud Console, or the
      map renders grey.
- [ ] **Hosting** — Firebase Hosting config + CI workflows are in the
      repo (see *Hosting & deployment* below); the Firebase projects,
      CI secrets, and prod custom domain still need to be created, and
      `WIDGET_HOST_TBD` above replaced with the real host.

## Hosting & deployment

The bundle is hosted on Firebase Hosting, one project per environment
(aliases `staging` / `production` in `.firebaserc`). The same
environment-agnostic artifact is deployed to both — API selection
happens via the widget's `api-base` attribute, not the build.

- `firebase.json` serves `dist/hosting/` — assembled from the built
  bundle by `scripts/prepare-hosting.sh` — at `/v1/jcp-embed.min.js`
  with `Cache-Control: public, max-age=300` (short, so floating-URL
  updates propagate quickly) and `Access-Control-Allow-Origin: *`
  (lets embedders use `crossorigin` / SRI).
- **Staging** auto-deploys via
  `.github/workflows/deploy-embed-staging.yml` on every push to `main`
  touching embed-relevant paths (also runnable manually).
- **Production** deploys via
  `.github/workflows/deploy-embed-prod.yml`, manual trigger only, gated
  by the GitHub `production` environment's required reviewers.
- CI auth: repo secrets `FIREBASE_SERVICE_ACCOUNT_STAGING` /
  `FIREBASE_SERVICE_ACCOUNT_PROD` (service-account JSON with the
  Firebase Hosting Admin role).
- Local deploy (first-time validation or emergencies):
  `npm run build && bash scripts/prepare-hosting.sh && npx firebase-tools deploy --only hosting --project staging`
- Rollback: Firebase console -> Hosting -> Release history -> Rollback
  (previous deploys are kept).
- TODO: immutable per-version URLs (`/releases/x.y.z/`) need released
  artifacts to persist somewhere CI can restore them from — every
  Firebase deploy replaces the whole site, so deploying only the
  current build would drop older versioned copies. Simplest option
  when needed: a tracked `firebase/releases/` dir (~160 KB per
  release) that `prepare-hosting.sh` copies in.

## Development

- Source: `src/embed/` (custom element) on top of `src/core/`
  (framework-agnostic modules shared with the WP bundles in `src/wp/`).
  Nothing in `src/core/` may reference WP globals — that boundary is
  what keeps a future extraction to a standalone package mechanical.
- Build: `npm run build` → `dist/embed/jcp-embed.min.js`.
- Local demo: `examples/embed-demo.html` (serve the repo root, e.g.
  `npx serve .`).
- The WP plugin release (`scripts/release.sh`) deliberately excludes
  `dist/embed/` from the plugin zip.
