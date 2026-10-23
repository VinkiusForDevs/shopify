# Vinkius Connector for Shopify

> The **Vinkius AI Governance** panel for Shopify — the store owner's
> observation-and-response surface for the Vinkius runtime. Open source,
> **GPLv2 or later**.

![License](https://img.shields.io/badge/License-GPLv2%20or%20later-blue.svg)
![Shopify](https://img.shields.io/badge/Shopify-App-7AB55C?logo=shopify&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-8.0%2B-4F5B93?logo=php&logoColor=white)
![Version](https://img.shields.io/badge/version-1.0.0-orange.svg)

**Vinkius Connector** turns a Shopify store into a first-class node in the
[Vinkius](https://vinkius.com) AI platform. It gives the store owner a live
governance panel: watch governed agent operations, inspect incidents and
execution evidence, search the Vinkius capabilities catalog, and trigger
emergency response — without leaving Shopify and without storing any Vinkius
data on the site.

## The core principle

This app is an **observation-and-response** surface. It follows one
non-negotiable rule:

> **It never manages connectors, credentials, policies, webhooks, or
> publishing inside Shopify.** Those live in the Vinkius cloud. Every value
> in the panel is read **live** from the Vinkius API — nothing is persisted in
> Shopify except the store's own visitor-consent records and local settings.

That boundary is what makes the app safe to ship: no backend secret ever
reaches the database, and any management decision is a deep link into
[cloud.vinkius.com](https://cloud.vinkius.com), not an action taken locally.

## What you get

Every screen from the WordPress plugin is present:

| Screen | What it shows |
| --- | --- |
| **AI Connections** | Landing/connect page — device-flow login and the connectors catalog |
| **Mission Control** | Top-line KPIs and the live request activity feed |
| **Agent Activity** | The agent/client rollup |
| **Connector Traffic** | Requests by connector |
| **Access Tokens** | The token fleet, with per-token activity |
| **Agent Passport** | Per-connector identity, scopes and lifecycle |
| **AI Spend** | Cost by server, plus estimated savings |
| **Capability Reliability** | Slowest and most-failing tools |
| **Security Posture** | DLP/FinOps enforcement state |
| **Request Failures** | Error breakdown by origin |
| **Compliance & Evidence** | Readiness scorecard and evidence packs |
| **Incidents** | Errors, DLP blocks, quarantined connectors |
| **Consent** | Visitor-consent records |
| **Settings** | Connect to Vinkius, language, trust badge, daily digest |

The panel uses the same design tokens, cards and charts as the Vinkius cloud
counterpart. Data is always read live, and every "manage this" affordance
deep-links to the cloud.

## How login works

Vinkius Connector uses the **OAuth 2.0 device flow** (RFC 8628). There are no
passwords and no API keys in the database:

1. In **Settings**, click **Connect with Vinkius** — the app requests a
   device code.
2. Approve the code on your [Vinkius account](https://cloud.vinkius.com).
3. The panel polls in the background and connects automatically.

Revoke or reset the session anytime from **Settings → Connect**.

## Installation

### As a Shopify app

1. Deploy this directory to an HTTPS host and point your Shopify app URL at
   it.
2. Set the environment variables in `.env` (or your host's secret manager):
   `SHOPIFY_API_KEY`, `SHOPIFY_API_SECRET`, `SHOPIFY_APP_URL` and
   `SHOPIFY_SCOPES`.
3. Install the app on a store: `/shopify.php?action=install&shop=your-store.myshopify.com`.
4. Open the panel and connect your Vinkius account from **Settings**.

### Without Shopify OAuth

Set `VC_ADMIN_PASSWORD` and open `/index.php`. The panel asks for that
password once per session. Use this only for private deployments.

## Requirements

- PHP **8.0+** with cURL and sessions.
- A Shopify store (for the OAuth install flow).
- A Vinkius account — the Free plan is enough to connect.

## Security & privacy

- **No secret material in the database** — no API keys, no client secrets, no
  business data. The only credentials stored are the owner's own OAuth session
  tokens, used to renew the connection and never sent to the browser.
- **No Vinkius data is persisted** — agents, incidents, spend and evidence are
  always read live.
- **One local store** — visitor-consent records (keyed IP hash, never raw
  PII).
- **Escaped everywhere** — output escaping and a session guard on every admin
  route.
- **User-authorized only** — external calls go to the fixed Vinkius endpoints
  and happen only after the owner logs in.

## Repository layout

```
.
|-- index.php                 # panel shell + login
|-- ajax.php                  # AJAX bus (all panel + public routes)
|-- bootstrap.php             # constants, storage and WP-compat shims
|-- shopify.php               # Shopify OAuth install/callback
|-- logout.php
|-- includes/                 # ported PHP classes
|-- templates/                # workbench shell + screens
|-- admin/css  admin/js       # vanilla, no build step
|-- languages/                # PO catalogs
|-- data/                     # local runtime data (gitignored)
|-- .env.example
|-- LICENSE
```

No Composer dependencies, no build step, no bundler.

## License

**GPLv2 or later** (see [LICENSE](LICENSE)). The architectural rule above
is non-negotiable.
