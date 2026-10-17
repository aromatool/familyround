# Product

<!-- impeccable:product-schema 1 -->

## Platform

web

## Users

Primary users are **Romanian families with children** who want real, unhurried time together, away from screens. The buyer is usually a parent; the product is also bought as a **gift** (birthdays, holidays, christenings) and is designed to be played across generations — several challenges deliberately involve grandparents and extended family (e.g. "Arborele Genealogic", "Faceți o Surpriză Bunicilor").

## Product Purpose

FamilyRound is a Romanian brand of **family experiences and games**. The name evokes the family circle: a place for parents, children and sometimes grandparents to reconnect despite busy work, school and care schedules. The homepage must tell this umbrella-brand story before introducing individual experiences.

Its first product, already in production, is **"30 de provocări în familie"**, a premium magnetic box with 30 cards prompting substantial activities together: a train trip, a picnic, building a machine from things at home. It is not a deck of two-minute table prompts. The box also contains a **"hartă a amintirilor"** (wall poster) and craft accessories such as washi tape, notes and photo corners. Families add a drawing, leaf, photo, ticket or other trace after each experience, making a personal tableau that remains on the wall.

The second game, **Family Box**, is being designed and is **not for sale yet**. The planned larger magnetic box has five sets of 40 cards each, for shorter at-home sessions around a table. The five working set names in its card artwork are Râs, Descoperă, Imaginează, Între noi and Mișcă. Its design and keepsake component are still in development; do not present a speculative box image as a finished product.

The game has **no rules and no end**: each family creates its own moments. The purpose is to bring families closer and reclaim real time together in a world pulled apart by screens — and, through the **Pușculița**, to turn part of every sale into help for less fortunate families. Success means families buying, playing repeatedly, keeping the tableau growing on their wall, and the brand expanding into a line of games and a community of giving.

## Positioning

Not a disposable conversation-card game but a **keepsake experience**: the memory-map poster becomes a lasting wall tableau of a family's own moments. Three things a neighboring product could not truthfully copy, taken together:
- the **memory-map / tableau** mechanic (the game leaves a physical trace on the wall, not just a played-and-forgotten deck);
- a built-in, transparent **giving mechanism** — the Pușculița: the current rule for new confirmed orders is 5 lei per eligible physical game unit, plus 5 lei per eligible unit on any confirmed order with a valid referral code other than the buyer’s own;
- a **community map (Harta)** of the localities the games have reached, and the brand idea in the name itself — *FamilyRound*, the family circle gathered together.

## Operating Context

- **E-commerce (WooCommerce):** the store sells the confirmed product "30 de provocări în familie" (111 lei, RON). On a confirmed/processing order, the system allocates 5 lei per eligible game unit (+5 lei per unit for a valid new-customer referral) to the Pușculița ledger. If no mission is active, contributions remain unassigned and are attached to the next activated mission. The Harta counts purchased game units by locality from one unique record per eligible order; delivery confirmation is not integrated yet.
- **Homepage is the brand hub**: a photographic family moment and brand promise → why shared time matters → different ways to be together → growing collection (first game for sale, Family Box clearly in development) → the first game's physical keepsake → Pușculița and referral → Harta → closing invitation. Do not turn the homepage into a long single-product page.
- **Navigation architecture:** the desktop header leads with Experiențe (the homepage collection, including the coming Family Box), then Povestea noastră, Pușculița, and Harta. Contact and Dă timpul mai departe remain in the mobile menu and footer. The header action opens the only published experience directly, changes to the collection when more products are published, and scrolls to the order form on the first game's product page. The header navigation is curated in `header.php`, independent of the old WordPress menu assignment.
- **Dedicated pages:** Pușculița, Harta FamilyRound (with an accessible, searchable locality list), Dă timpul mai departe (referral), Povestea noastră, Contact, and legal pages (termeni și condiții, politica de confidențialitate, politica de retur, întrebări frecvente).
- **Referral program ("Dă timpul mai departe"):** a personal link; each new family that buys through it adds a bonus to the Pușculița — it is *not* a commission paid to the referrer.
- **Sharing path:** the homepage introduces the referral beside the Pușculița and closes with an invitation to pass the idea on. The dedicated referral page creates a personal link and offers native sharing where available, copying, and WhatsApp. An eligible buyer sees the same personal link on the order confirmation page and in WooCommerce customer order emails. A persistent site button opens a code-entry dialog and leads to a public, aggregate-only impact view; the complete personal dashboard still requires the private emailed token. The Harta can be filtered by referral code to show aggregated localities and eligible game units from validated referred orders; without a code it shows all confirmed purchases. Sharing is always initiated by the person; the site does not post on their behalf. A recommendation counts for each confirmed qualifying purchase made with a valid code other than the buyer’s own. Periodic referral progress emails require an unchecked-by-default opt-in at signup or checkout, go out no more than weekly and only when new validated impact exists, and contain a signed unsubscribe link.

## October 2026 Functional Brief — Current Scope

The founder's functional brief describes the intended customer journey and future possibilities. For current work, focus on **physical family games, the purchase journey, the Pușculița, referrals, the community map, and truthful post-purchase communication**. Activity discovery, partner listings, and bookings are outside the present scope.

- A customer should be able to buy as a guest: choose a game and quantity, provide only necessary contact and delivery/invoicing details, choose an available delivery method, pay, and receive clear order, shipping, and invoice communication. NETOPIA, Colete Online, and SmartBill depend on external account configuration.
- The primary purchase path is homepage → experience page → checkout → order confirmation. Every "Comandă acum" action on the first experience submits the quantity-aware WooCommerce form and goes directly to checkout after a successful add. Checkout lets the customer adjust quantity inline; the optional WooCommerce cart page remains available separately. Checkout uses the native Colete Online address form, shipping quotes and locker picker. A compact presentation groups available rates by locker, courier and local pickup; courier selection reveals the actual carrier options and prices. WooCommerce uses one address for billing and delivery, with no second shipping address form. Its standard order note is labelled as an optional message for the parcel, including gifts; staff must add that message during packing. The local store currently offers WooCommerce bank transfer with payment instructions sent separately; cash on delivery is disabled because the Colete Online account has no bank holder name for repayment. Real package weight and dimensions must replace plugin defaults before shipping prices are treated as final. A referral code does not change the customer's price: the extra 5 lei per eligible game is available only on a buyer's first confirmed order, using email or phone to recognize earlier confirmed purchases.
- The Pușculița rule is **5 lei per eligible game unit**, plus **5 lei per eligible referred game unit** only on a referred buyer's first confirmed order. FamilyRound funds both amounts from its own sales. Historical pre-change ledger entries are not silently rewritten and need an explicit reconciliation decision before launch. Keep allocated, reserved/transferred, and actually spent amounts distinct. No beneficiary or donation is confirmed yet.
- Eligibility is snapshotted on each order item at checkout so later product edits do not alter that order's original rule. Only the first confirmed purchase can earn a referral bonus when the code belongs to someone else. A referrer's code can bring unlimited new buyers. Previously awarded bonuses are preserved. If an order loses confirmed status, its allocations and map record are reversed. Adjustments to already used or transferred ledger entries are appended separately.
- Anyone may recommend, even without buying. A personal link attributes qualifying purchases; the recommender receives impact statistics, never money. The dashboard must not reveal buyer identity. The brief suggests 30-day attribution and an optional manual checkout code, both present in the current code.
- A dashboard access token is sent by email and is never returned by the public signup API. Multiple emailed links remain valid for up to 180 days; the private dashboard API receives the token in a POST body and is marked non-cacheable.
- The intended map eventually counts **games delivered to a locality**; the current map counts **game units in confirmed/processing orders** before delivery, with one unique record per order. Public wording says purchased, not delivered. It shows only aggregated real data and a meaningful empty state.
- Keep transactional messages distinct from opt-in community/newsletter messages. Post-purchase sharing is an invitation initiated by the customer, potentially followed by feedback after delivery.
- The community can include non-buyers through free family ideas and stories; avoid invented customer stories or social proof.

### Implementation gaps to resolve before launch

1. Reconcile historical pre-change ledger entries against their original game quantities before launch; the new unit rule starts with new orders, and existing entries are not bulk rewritten automatically.
2. Connect a reliable courier delivery event before claiming the Harta shows delivered games. Until then it shows purchased games from confirmed/processing orders, including temporary cash-on-delivery processing orders.
3. Configure the external payment, shipping/AWB, invoicing, and email services when accounts and credentials are available; then review the whole commerce journey with real gateway statuses.
4. Add an explicit administrative export/audit view for the ledger, recommendations, and map source records as operations grow. Woo order notes now record amount adjustments; underlying order/refund data remains the source.

## Capabilities and Constraints

- Stack: **WordPress + WooCommerce (HPOS)**, custom theme `familyround` and plugin `familyround-core`; interactive pieces are **React islands** built with Vite (IIFE bundle) — the Pușculița progress panel and the Harta map; REST namespace `familyround/v1` (`piggybank/summary`, `map/points`). `FR_THEME_VERSION` must be bumped on CSS/JS changes to bust cache.
- **Pușculița:** the public figure is the **amount allocated to the current active mission**, read from an order ledger; the UI distinguishes **allocated vs. donated** (donated is currently 0 — no transfers have been recorded yet). The contribution-calculation mechanism is product logic and must not be changed casually.
- **Harta:** localities are derived from confirmed/processing orders; a unique `fr_map_orders` row makes aggregation idempotent and counts eligible game units, not families. Cancelled/refunded orders are removed and line-item refunds reduce unit counts. It does not yet prove physical delivery. Geographic SVG with an equirectangular projection; name variants are aggregated; no personal data or exact addresses are ever shown.
- **Terminology (must stay precise):** *provocări* (the 30 challenge cards); *harta amintirilor* (the memory-map wall poster); *Pușculița* (the giving fund/jar); *Harta FamilyRound* (the community map of localities); *Dă timpul mai departe* (the referral program); *jocuri cumpărate* (purchases — never equated with games, orders, or families).
- The **collection must support adding more games** from WooCommerce; the first game's custom product page and Open Graph description are guarded by its title, while other published products use the standard WooCommerce product template and their own metadata. Before publishing the next game, give it a deliberately designed product page.
- **Historical accounting:** older local ledger entries were created under the 5 lei per order rule. The new code calculates per game and resynchronizes an old order if Woo reprocesses it, but it does not bulk rewrite old entries at upgrade. Review that history before using totals as launch claims.
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
- **Absences future work must NOT fabricate:** real testimonials/reviews; real social-media URLs (Instagram/Facebook); a confirmed charity/beneficiary for the Pușculița; donated (transferred) amounts; company/operator legal details beyond what is already configured; a finished Family Box product or packaging image.

## Product Principles

1. **The brand is the family circle.** Every surface should deepen connection and real time together, not merely sell a box.
2. **Memories made visible.** The keepsake tableau (harta amintirilor) is the product's magic — keep it central; never flatten the game into a generic deck of cards.
3. **Give transparently.** The Pușculița is core, not a gimmick: show real, honest figures (allocated vs. donated) and never invent a beneficiary or inflate impact.
4. **Truth over polish.** Use only real product content, data, and assets; label concepts as concepts; never fabricate testimonials, causes, metrics, or products.
5. **Built to grow.** Structure for a growing line of games and community, without implying products or claims that don't exist yet.

## Accessibility & Inclusion

Romanian-language throughout. Established working constraints to preserve: respect `prefers-reduced-motion` (motion is discreet and never essential to meaning), keyboard-accessible and large-enough touch targets for interactive elements (e.g. map points), progress shown with accessible semantics (`role="progressbar"`), and legible small text with sufficient contrast on the ivory/green palette. No formal external standard has been mandated yet.
