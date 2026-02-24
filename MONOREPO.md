# Monorepo Structure

## Dossiers
- `backend/`: application Laravel (API).
- `frontend/`: application Vue (Vite, JavaScript).
- `_archive/`: anciens doublons déplacés depuis la racine (aucune suppression destructive).

## Endpoints API
- `POST /api/auth/register`
- `POST /api/register` (alias legacy)
- `GET /api/profil-sante` (auth sanctum bearer token)
- `POST /api/profil-sante` (auth sanctum bearer token)

## Variables d'environnement
- `backend/.env`:
  - `APP_URL=http://localhost:8000`
  - `CORS_ALLOWED_ORIGINS=http://localhost:5173`
- `frontend/.env`:
  - `VITE_API_URL=http://localhost:8000/api`

## Lancement
- Backend:
  - `cd backend`
  - `composer install`
  - `php artisan key:generate`
  - `php artisan migrate`
  - `php artisan serve`
- Frontend:
  - `cd frontend`
  - `npm install`
  - `npm run dev`

## Scripts racine
- `npm run dev:backend`
- `npm run dev:frontend`
