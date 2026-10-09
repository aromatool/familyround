# FamilyRound.ro — mediu local de dezvoltare

## Pornire

```bash
cp .env.example .env   # doar prima dată
docker compose up -d
```

Site: http://localhost:8080
Admin: http://localhost:8080/wp-admin (user: `admin`, parolă: `admin`)
Emailuri (Mailpit, local, nu trimite real): http://localhost:8025

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

## Stare curentă

- WordPress + WooCommerce instalate, monedă RON, checkout clasic (shortcode, nu block-based)
- Temă FamilyRound activă: homepage, pagini (Povestea noastră, Contact, legale), meniu + footer
- Livrare: curier / easybox / ridicare personală (zonă RO) — toate testate la checkout
- Plată: ramburs activ (temporar, pentru testare locală); plugin-urile NETOPIA și SmartBill instalate și active, dar fără chei — se configurează imediat ce există PFA
- `familyround-core`: tabele proprii, alocare automată Pușculiță la fiecare comandă confirmată, validare recomandări (anti-fraudă: auto-recomandare, client existent), agregare Hartă — toate verificate end-to-end cu comenzi de test
- Programul de recomandări complet: înscriere, cod/link, cookie de atribuire, dashboard privat prin token
- Panou admin propriu (meniul „Pușculița”) pentru creat/activat/finalizat misiuni
- Pagini Pușculița / Harta / Dă timpul mai departe — insule React reale, conectate la API-ul propriu
- Emailul de confirmare a comenzii include automat suma alocată în Pușculiță și linkul propriu de recomandare al cumpărătorului (fiecare client devine recomandator eligibil) — vizibil în Mailpit la fiecare comandă

## Blocat pe conturi externe (nu pe cod)

- **NETOPIA** — plugin instalat, așteaptă cont comerciant (necesită PFA)
- **SmartBill (e-Factura)** — plugin instalat, așteaptă cont
- **Curier/AWB** — plugin **Colete-Online** instalat (fără CUI la înregistrare); așteaptă doar cheia API din contul creat pe colete-online.ro, plus greutatea/dimensiunile reale ale produsului

## Următorii pași

Configurare NETOPIA + SmartBill + Colete-Online cu chei reale, conținut real pentru paginile legale (acum sunt placeholder), rafinare vizuală (poze, animații).
