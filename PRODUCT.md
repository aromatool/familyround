# Product

<!-- impeccable:product-schema 1 -->

## Platform

web

## Users

Primary users are **Romanian families with children** who want real, unhurried time together, away from screens. The buyer is usually a parent; the product is also bought as a **gift** (birthdays, holidays, christenings) and is designed to be played across generations — several challenges deliberately involve grandparents and extended family (e.g. "Arborele Genealogic", "Faceți o Surpriză Bunicilor").

## Product Purpose

FamilyRound is a Romanian family-connection brand. Its first product is a **premium family card game, "30 de provocări în familie"** — a box meant to sit in the bookshelf or be given as a gift. Inside: 30 challenge cards, a **"hartă a amintirilor"** (a wall poster, framed or not), and accessories for building a keepsake. Families pick a challenge, live it together, and pin what it leaves behind — a drawing, a leaf, a photo, a ticket — onto the memory map, which over time becomes a **tableau of the family's real moments**.

The game has **no rules and no end**: each family creates its own moments. The purpose is to bring families closer and reclaim real time together in a world pulled apart by screens — and, through the **Pușculița**, to turn part of every sale into help for less fortunate families. Success means families buying, playing repeatedly, keeping the tableau growing on their wall, and the brand expanding into a line of games and a community of giving.

## Positioning

Not a disposable conversation-card game but a **keepsake experience**: the memory-map poster becomes a lasting wall tableau of a family's own moments. Three things a neighboring product could not truthfully copy, taken together:
- the **memory-map / tableau** mechanic (the game leaves a physical trace on the wall, not just a played-and-forgotten deck);
- a built-in, transparent **giving mechanism** — the Pușculița: 5 lei from each game, +5 lei when bought through a referral, set aside for a cause (contribution, not commission);
- a **community map (Harta)** of the localities the games have reached, and the brand idea in the name itself — *FamilyRound*, the family circle gathered together.

## Operating Context

- **E-commerce (WooCommerce):** the store sells the confirmed product "30 de provocări în familie" (111 lei, RON). On a paid order, the system allocates 5 lei (+5 lei when the order came through a referral link) to the active Pușculița mission's ledger, and adds a point for the buyer's locality to the Harta.
- **Homepage is the brand hub**, in order: hero → Pușculița band → the game (collection) → "Voi cum vreți să fiți împreună?" (brand intentions) → Pușculița progress → Harta → Povestea noastră → closing CTA integrated into the footer.
- **Dedicated pages:** Pușculița, Harta FamilyRound (with an accessible, searchable locality list), Dă timpul mai departe (referral), Povestea noastră, Contact, and legal pages (termeni și condiții, politica de confidențialitate, politica de retur, întrebări frecvente).
- **Referral program ("Dă timpul mai departe"):** a personal link; each new family that buys through it adds a bonus to the Pușculița — it is *not* a commission paid to the referrer.

## Capabilities and Constraints

- Stack: **WordPress + WooCommerce (HPOS)**, custom theme `familyround` and plugin `familyround-core`; interactive pieces are **React islands** built with Vite (IIFE bundle) — the Pușculița progress panel and the Harta map; REST namespace `familyround/v1` (`piggybank/summary`, `map/points`). `FR_THEME_VERSION` must be bumped on CSS/JS changes to bust cache.
- **Pușculița:** the public figure is the **amount allocated to the current active mission**, read from an order ledger; the UI distinguishes **allocated vs. donated** (donated is currently 0 — no transfers have been recorded yet). The contribution-calculation mechanism is product logic and must not be changed casually.
- **Harta:** localities are derived from **paid orders** (the counter increments **once per eligible order, at payment** — i.e. purchases, not confirmed deliveries, and it counts orders, **not** game quantity or families). Geographic SVG with an equirectangular projection; name variants are aggregated; no personal data or exact addresses are ever shown.
- **Terminology (must stay precise):** *provocări* (the 30 challenge cards); *harta amintirilor* (the memory-map wall poster); *Pușculița* (the giving fund/jar); *Harta FamilyRound* (the community map of localities); *Dă timpul mai departe* (the referral program); *jocuri cumpărate* (purchases — never equated with games, orders, or families).
- The **collection must support adding more games** (built as a reusable component); challenge-specific content is tied to the product, not hardcoded globally.
- **Open / undecided product facts (do not fabricate):** the Pușculița **beneficiary/cause is not yet confirmed** ("Cauza urmează să fie anunțată"); **no funds have been donated yet** (allocated only); the poster ships **framed or not — customer's choice**; real founder/family and product photography is **still pending**.

## Brand Commitments

- **Name & logo:** FamilyRound — the family *round/circle*, togetherness, connection. Do **not** redesign the logo; use the provided mark (`logo-mark.svg`) with the "FamilyRound" wordmark.
- **Language:** Romanian only (Romania market), correct diacritics throughout.
- **Voice:** warm, calm, editorial, family-centered; emotion- and benefit-led; never childish, gimmicky, or hard-sell.
- **Durable brand pillars that must persist across future work:**
  1. Connection — real time together, away from screens.
  2. A keepsake that leaves a trace on the wall (the memory tableau).
  3. Giving — part of every sale helps less fortunate families (Pușculița), shown with honest numbers.
  4. Community — the Harta of families reached.
  5. Referral that feeds the cause, not a personal commission.
- "**No rules, no end**" is a deliberate product and brand idea, not a copy flourish.

## Evidence on Hand

- **Real product:** "30 de provocări în familie", 111 lei (RON), WooCommerce product.
- **Real game content:** the 30 challenge names in `fr_challenge_cards()` (`functions.php`) — e.g. Arborele Genealogic, Comoara din Natură, Povești la Lumina Lanternei, Vânătoare de Comori, Blazonul Familiei. Printed card art (`card-01..30.jpg`), `product-box.jpg`.
- **Real data systems:** Pușculița ledger + missions (active mission goal 2.500 lei, ~40 lei allocated); Harta map points (e.g. Cluj-Napoca, Florești, București) from real orders.
- **Assets:** `logo-mark.svg` (provided by the user); lifestyle/concept photography (`together-*.jpg`, `story-family.jpg`, `hero-atmosphere.jpg`, `hero-lifestyle.jpg`) — these are **AI-generated concept images, not real founders or customers**.
- **Absences future work must NOT fabricate:** real testimonials/reviews; real social-media URLs (Instagram/Facebook); a confirmed charity/beneficiary for the Pușculița; donated (transferred) amounts; company/operator legal details beyond what is already configured; any product that does not exist yet.

## Product Principles

1. **The brand is the family circle.** Every surface should deepen connection and real time together, not merely sell a box.
2. **Memories made visible.** The keepsake tableau (harta amintirilor) is the product's magic — keep it central; never flatten the game into a generic deck of cards.
3. **Give transparently.** The Pușculița is core, not a gimmick: show real, honest figures (allocated vs. donated) and never invent a beneficiary or inflate impact.
4. **Truth over polish.** Use only real product content, data, and assets; label concepts as concepts; never fabricate testimonials, causes, metrics, or products.
5. **Built to grow.** Structure for a growing line of games and community, without implying products or claims that don't exist yet.

## Accessibility & Inclusion

Romanian-language throughout. Established working constraints to preserve: respect `prefers-reduced-motion` (motion is discreet and never essential to meaning), keyboard-accessible and large-enough touch targets for interactive elements (e.g. map points), progress shown with accessible semantics (`role="progressbar"`), and legible small text with sufficient contrast on the ivory/green palette. No formal external standard has been mandated yet.
