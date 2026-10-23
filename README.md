# Vinkius Connector for Shopify

> The **Vinkius AI Governance** panel for Shopify — the store owner's
> observation-and-response surface for the Vinkius runtime. Open source,
> **GPLv2 or later**.

![License](https://img.shields.io/badge/License-GPLv2%20or%20later-blue.svg)
![Shopify](https://img.shields.io/badge/Shopify-App-7AB55C?logo=shopify&logoColor=white)
![Node](https://img.shields.io/badge/Node.js-18%2B-339933?logo=node.js&logoColor=white)
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

## What you get

- **AI Connections** — device-flow login and connectors catalog
- **Mission Control** — top-line KPIs and live request activity feed
- **Agent Activity** — agent/client rollup
- **Connector Traffic** — requests by connector
- **Access Tokens** — token fleet with per-token activity
- **Agent Passport** — per-connector identity, scopes and lifecycle
- **AI Spend** — cost by server plus estimated savings
- **Capability Reliability** — slowest and most-failing tools
- **Security Posture** — DLP/FinOps enforcement state
- **Request Failures** — error breakdown by origin
- **Compliance & Evidence** — readiness scorecard, evidence packs
- **Incidents** — errors, DLP blocks, quarantined connectors
- **Consent** — visitor-consent records
- **Settings** — connect, language, trust badge, daily digest

## How login works

Vinkius Connector uses the **OAuth 2.0 device flow** (RFC 8628). There are no
passwords and no API keys in the database.

## Installation

1. Clone this repository and run `npm install`.
2. Configure your Shopify app credentials in `.env`.
3. Run `npm start` and install the app on your store.
4. Open the app in Shopify admin and click **Connect with Vinkius**.

## Requirements

- Shopify store with app development enabled.
- Node.js **18+**.
- A Vinkius account — the Free plan is enough to connect.

## Security & privacy

- **No secret material stored** — OAuth session tokens only, never sent to
  the browser.
- **No Vinkius data persisted** — agents, incidents, spend and evidence are
  always read live.
- **Escaped everywhere** — output escaping and a token guard on every route.

## License

**GPLv2 or later** (see [LICENSE](LICENSE)). The architectural rule above
is non-negotiable.