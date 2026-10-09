# FamilyRound.ro — mediu local de dezvoltare

## Pornire

```bash
cp .env.example .env   # doar prima dată
docker compose up -d
```

Site: http://localhost:8080
Admin: http://localhost:8080/wp-admin (user: `admin`, parolă: `admin`)

## Structură

- `wp-content/themes/familyround/` — tema custom (homepage, checkout, identitate vizuală)
  - `src/islands/` — componente React montate pe pagini ("insule")
  - `npm install && npm run build` — compilează insulele React în `assets/dist/`
  - `npm run dev` — recompilează automat la fiecare salvare
- `wp-content/plugins/familyround-core/` — Pușculița, Recomandările, Harta — logică + tabele proprii + API REST (`/wp-json/familyround/v1/...`), independent de temă

## Comenzi utile (WP-CLI)

```bash
docker compose run --rm wpcli wp <orice comandă wp-cli>
```

## Stare curentă (Ziua 1)

- WordPress + WooCommerce instalate, monedă RON
- Temă FamilyRound activă, homepage cu poveste + CTA + contor Pușculiță live
- Plugin `familyround-core` activ, tabele proprii create, endpoint `/piggybank/summary` funcțional
- Un produs de test creat ("FamilyRound — 30 de provocări în familie", 111 lei)

## Următorii pași

Checkout simplificat, NETOPIA, facturare/e-Factura, curier/AWB — conform planului din documentul de arhitectură.
