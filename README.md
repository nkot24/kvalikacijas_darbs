# Order Management

Šis projekts ir Laravel pamatots rīks pasūtījumu un klientu pārvaldībai (Order Management).

Šajā README atradīsies īss apraksts, prasības un soļi, kā palaist projektu lokāli.

## Galvenais

-   Framework: Laravel
-   Eksporta/Importa funkcionalitāte Excel formātā (Maatwebsite/Excel)

## Prasības

-   PHP 8.1+
-   Composer
-   Node.js + npm (vai pnpm/yarn)
-   MySQL (vai cits atbalstīts datubāzes savienojums)

## Uzstādīšana (lokāli)

1. Klonēt repozitoriju:

```powershell
git clone https://github.com/nkot24/order_mangement.git
cd order_mangement
```

2. Izveidot `.env` failu (PowerShell var izmantot `cp` kā alias vai `Copy-Item`):

```powershell
cp .env.example .env   # vai: Copy-Item .env.example .env
```

3. Atjaunināt `.env` datubāzes iestatījumus (piemērs):

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306   # pievieno 3307, ja tāda porta izmantošana nepieciešama
DB_DATABASE=order_management
DB_USERNAME=root
DB_PASSWORD=
```

4. Instalēt PHP atkarības ar Composer:

```powershell
composer install
```

5. Instalēt JavaScript atkarības un sagatavot front-end resursus:

```powershell
npm install
npm run dev     # vai: npm run build (ražošanai)
```

6. Izveidot lietojumprogrammas atslēgu un simbolisko linku uz storage:

```powershell
php artisan key:generate
php artisan storage:link
```

7. Palaist datubāzes migrācijas un (papildus) seederus:

```powershell
php artisan migrate --seed
# Ja datubāze jau ir, var vienkārši palaist seederus:
php artisan db:seed
```

8. Palaist lokālo serveri:

```powershell
php artisan serve
```

Pēc veiksmīgas palaišanas lietojumprogramma būs pieejama uz http://127.0.0.1:8000 (ja nav norādīts cits ports).

## Testēšana

Projektā ir konfigurēta testu vide (Pest/PHPUnit). Lai palaistu testus:

```powershell
php artisan test
# vai, ja izmanto composer skriptus:
composer test
```

## Eksporta / Importa funkcijas

Projektā ir mape `app/Exports` un `app/Imports` ar sagatavotām klasēm Excel datu eksportam un importam.
Funkcionalitāte izmanto `maatwebsite/excel` bibliotēku (skatīt `composer.json`).

## Biežāk lietotās komandas

-   Instalēt atkarības: `composer install`, `npm install`
-   Front-end būve: `npm run dev` (attīstīšanai) vai `npm run build` (produkcijai)
-   Datubāzes migrācijas: `php artisan migrate`
-   Seederi: `php artisan db:seed`
-   Servisēšana lokāli: `php artisan serve`

## Piezīmes

-   Ja izmanto nestandarta datubāzes portu (piem., `3307`), pārliecinies, ka `.env` laukā `DB_PORT` ir norādīts pareizais ports.
-   Windows PowerShell atbalsta `cp` kā alias uz `Copy-Item`, tāpēc daudzos piemēros redzēsi `cp` — PowerShell to apstrādās.

## Ieguldījums un kļūdu ziņošana

Ja vēlies palīdzēt ar uzlabojumiem vai atradi kļūdu, iesniedz pull request vai atver issue repozitorijā.

## Licence

Skatīt `LICENSE` (ja pieejama) repozitorijā.

---

Ja vēlies, varu šo README paplašināt ar detalizētāku sadaļu par arhitektūru, datu modeļiem vai parastajām kļūdām un to risinājumiem.
