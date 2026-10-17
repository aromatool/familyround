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
- Livrare: metoda Colete Online activă în zona RO; curierul și lockerele depind de serviciile oferite de contul Colete Online pentru adresa și metoda de plată alese. Ridicarea personală este dezactivată până la stabilirea unui punct real.
- Plată: transfer bancar nativ WooCommerce activ temporar, cu instrucțiunea de a aștepta detaliile de plată de la FamilyRound; ramburs dezactivat. Plugin-urile NETOPIA și SmartBill sunt instalate, dar fără chei.
- `familyround-core`: tabele proprii, alocare automată de 5 lei pentru fiecare joc eligibil din comanda confirmată (+5 lei per joc doar la prima comandă a cumpărătorului venit prin recomandare), verificare după email sau telefon, fără auto-recomandare, recalcul la schimbarea statusului și la rambursări, Hartă cu o înregistrare unică per comandă și total de jocuri cumpărate
- Programul de recomandări complet: înscriere, cod/link, cookie de atribuire, dashboard privat prin token
- Panou admin propriu (meniul „Pușculița”) pentru creat/activat/finalizat misiuni
- Pagini Pușculița / Harta / Dă timpul mai departe — insule React reale, conectate la API-ul propriu
- Emailul de confirmare a comenzii include automat suma alocată în Pușculiță și linkul propriu de recomandare al cumpărătorului (fiecare client devine recomandator eligibil) — vizibil în Mailpit la fiecare comandă
- Butoanele „Comandă acum” de pe pagina jocului trimit direct la checkout după adăugarea produsului, păstrând cantitatea aleasă. Cantitatea se poate modifica direct în sumarul checkoutului; actualizarea folosește coșul WooCommerce și recalculează checkoutul. Pagina de coș rămâne accesibilă separat. Formularul nativ Colete Online din checkout colectează adresa, oferă tarifele și harta lockerelor. Interfața grupează tarifele disponibile în „La locker”, „La adresă, prin curier” și „Ridicare personală”; transportatorii și prețurile apar după alegerea curierului. WooCommerce este configurat cu o singură adresă pentru factură și livrare, fără formular pentru o a doua adresă. Nota standard a comenzii este prezentată drept mesaj opțional pentru colet, inclusiv pentru un cadou. Pentru factură se poate alege persoană fizică sau firmă, cu denumirea firmei și CUI/CIF. Pagina de confirmare folosește prezentarea FamilyRound.
- Pentru produsele fizice care nu sunt jocuri, se poate debifa „Joc FamilyRound eligibil” în editorul produsului.

## Blocat pe conturi externe (nu pe cod)

- **NETOPIA** — plugin instalat, așteaptă cont comerciant (necesită PFA)
- **SmartBill (e-Factura)** — plugin instalat, așteaptă cont
- **Curier/AWB** — pluginul **Colete Online** este conectat în WordPress-ul local și metoda lui este activă în zona „România”. Tarifele fixe locale sunt dezactivate. Ridicarea personală nativă WooCommerce este activă și gratuită; localitatea afișată este Florești, Cluj, iar adresa exactă de preluare trebuie completată când este confirmată. Configurația Colete Online afișează trei curieri disponibili, în ordinea prețului, cu numele lor, și nu include Poșta Română. Pentru lockere sunt folosite punctele apropiate într-o rază de 5 km; cardul „La locker” apare numai dacă există tarife valide și deschide direct harta pluginului. Cardul „La adresă, prin curier” permite alegerea explicită a serviciului. O protecție mică în `FR_Checkout` elimină tarifele de locker pe care pluginul le returnează fără niciun punct apropiat când harta este activă. La schimbarea localității trebuie aleasă sugestia Colete Online pentru a actualiza și codul poștal. Regula Colete Online de transport gratuit peste 250 lei a fost dezactivată; tarifele sunt cele calculate de API. Rambursul rămâne dezactivat până la configurarea titularului contului în Colete Online. Produsul nu are încă greutatea și dimensiunile coletului ambalat; până la completarea lor, cotațiile folosesc valorile implicite ale pluginului (1 kg, 15 × 15 × 15 cm) și sunt doar orientative. Credențialele API sunt salvate în opțiunile WordPress locale, nu în repository.

- Paginile legale (Termeni, Confidențialitate/GDPR, Retur, FAQ) au conținut real, funcțional pentru lansare — cu `[placeholder-uri]` clar marcate pentru CUI/sediu (de completat după PFA) și metoda finală de plată

## Următorii pași

Configurare NETOPIA + SmartBill, completarea greutății și dimensiunilor coletului pentru Colete-Online și verificarea setărilor pe domeniul final, completarea placeholder-elor din paginile legale (CUI, sediu) odată cu PFA, revizuire finală a textelor legale de către un avocat/contabil, rafinare vizuală (poze, animații).

Înainte de lansare: reconcilierea alocărilor istorice făcute per comandă și conectarea unui status real de livrare înainte ca Harta să fie descrisă ca hartă a jocurilor livrate.

Reconstruirea Hărții rulează după încărcarea completă a WordPress și WooCommerce. Dacă o comandă veche nu poate fi citită de WooCommerce, migrarea continuă pentru celelalte și afișează în administrare ID-ul omis, care trebuie verificat separat înainte de lansare.
