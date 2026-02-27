# Fiere Contatti

Applicazione Laravel per gestire fiere e contatti raccolti allo stand.

## Funzionalità principali
- Area autenticata con CRUD fiere e contatti.
- Fiera con **data singola** o **range** (`start_date` / `end_date`).
- Ricerca contatti server-side (nome, cognome, email, telefono, azienda, note).
- Export Excel `.xls` per i contatti della singola fiera.
- Form pubblico condivisibile via token ULID, con rate limit sul submit.
- Upload file contatto con metadati (`file_path`, nome originale, mime, size).
- Download/preview file consentito solo al proprietario della fiera.

## Requisiti
- PHP 8.2+
- Composer
- Node.js 20+

## Setup locale
```bash
cp .env.example .env
composer install
php artisan key:generate
php artisan migrate --seed
npm install
```

## Front-end (Tailwind + Vite)
### Modalità DEV (con hot reload)
```bash
npm run dev
php artisan serve
```

### Modalità build (senza dev server)
```bash
npm run build
php artisan serve
```

> In modalità build Laravel leggerà `public/build/manifest.json` e gli asset compilati in `public/build/assets`.

## Dati demo
- email: `test@example.com`
- password: `password`

## Comandi qualità
```bash
php artisan test
npm run build
```
