# Assistant Sante Monorepo

Ce repository contient deux projets séparés:
- `backend/`: API Laravel
- `frontend/`: application Vue (Vite)

Les anciens doublons de la racine ont été déplacés dans `_archive/`.

## Démarrage

### Backend
```bash
cd backend
composer install
php artisan key:generate
php artisan migrate
php artisan serve
```

### Frontend
```bash
cd frontend
npm install
npm run dev
```

### Scripts racine
```bash
npm run dev:backend
npm run dev:frontend
```

## Variables d'environnement
- `backend/.env`: config Laravel + DB MySQL + CORS
- `frontend/.env`: `VITE_API_URL=http://localhost:8000/api`
