# Deploying the JS embed

How `dist/embed/jcp-embed.min.js` gets to Firebase Hosting. For what
the embed is and how sites use it, see [embed.md](embed.md). The
WordPress plugin releases separately via `scripts/release.sh` and does
not involve Firebase.

## Environments

| Env | Firebase project (alias) | Bundle URL |
| --- | --- | --- |
| Staging | `travel-app-eor5yc` (`staging`) | <https://travel-app-eor5yc.web.app/v1/jcp-embed.min.js> |
| Production | `jobcapturepro` (`production`) | <https://jobcapturepro.web.app/v1/jcp-embed.min.js> |

Aliases live in `.firebaserc`. The artifact is environment-agnostic:
the same file ships to both, and API selection happens through the
widget's `api-base` attribute. Staging tracks `main`; production only
moves on a deliberate deploy. **Customer sites should always reference
the production URL** — staging updates on every merge.

## Manual deploy

Prereqs: `npm ci` run at least once, and `npx firebase-tools login`
with a Google account that can access the Firebase projects. Deploy
from a clean, committed checkout so the live file is traceable to a
commit.

```bash
# 1. Build the bundle, then assemble the hosting root (dist/hosting/)
npm run build && bash scripts/prepare-hosting.sh

# 2. Deploy (either or both)
npx firebase-tools deploy --only hosting --project staging
npx firebase-tools deploy --only hosting --project production
```

Order matters in step 1: webpack wipes `dist/` when the build starts,
so the prepare script must run after it (it errors if the bundle is
missing). The deploy uploads whatever is sitting in `dist/hosting/`,
so always rebuild first.

### Verify

```bash
curl -sI https://travel-app-eor5yc.web.app/v1/jcp-embed.min.js
curl -sI https://jobcapturepro.web.app/v1/jcp-embed.min.js
```

Expect `200` with `cache-control: public, max-age=300` and
`access-control-allow-origin: *`; the response body starts with the
Swiper license banner. The 5-minute max-age means embedding sites pick
up a new deploy within about 5 minutes.

## CI pipelines

- **Staging** — `.github/workflows/deploy-embed-staging.yml` deploys
  automatically on every push to `main` touching embed-relevant paths
  (`src/embed/`, `src/core/`, `src/css/`, build configs, lockfile).
  Can also be run by hand from the Actions tab.
- **Production** — `.github/workflows/deploy-embed-prod.yml`, manual
  trigger only (Actions tab → *Deploy embed to production* → Run
  workflow). The job targets the GitHub `production` environment, so
  configured required reviewers must approve before it runs.
- **Auth** — repo secrets `FIREBASE_SERVICE_ACCOUNT_TRAVEL_APP_EOR5YC`
  and `FIREBASE_SERVICE_ACCOUNT_JOBCAPTUREPRO`: service-account JSON
  minted by `npx firebase-tools init hosting:github`, run once per
  project (it names the secret after the project ID). Answer "No" to
  its workflow-generation questions and delete any
  `firebase-hosting-*.yml` files it scaffolds — the two workflows
  above replace them.

## Rollback

Firebase console → Hosting → Release history → Rollback. Previous
releases are retained; rollback is one click and propagates within the
same ~5-minute cache window.

## Not yet implemented

Immutable per-version URLs (`/releases/x.y.z/…`). Every Firebase
deploy replaces the whole site, so deploying only the current build
would drop older versioned copies; when needed, the simplest fix is a
tracked `firebase/releases/` directory (~160 KB per release) that
`scripts/prepare-hosting.sh` folds into the hosting root.
