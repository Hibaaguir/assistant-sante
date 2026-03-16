# Configuration de l'Accès Administrateur

## ✅ État du Système

### Utilisateur Administrateur
- **Email**: admin@gmail.com
- **Mot de passe**: admin1234
- **Rôle**: administrateur
- **Statut**: Actif

### Base de Données
- Tables créées ✓
- Migrations appliquées ✓
- Utilisateur admin créé ✓

## 🔐 Flux de Connexion

1. **Connexion**
   - L'administrateur se connecte via la **même interface que les autres utilisateurs**
   - Email: `admin@gmail.com`
   - Mot de passe: `admin1234`

2. **Authentification Backend** (AuthController)
   - Vérifie les identifiants
   - Détecte le rôle = 'administrateur'
   - Retourne le token avec redirection vers `/main/dashboard`

3. **Navigation Frontend** (Router)
   - Le guard `beforeEach()` vérifie `estAdministrateur`
   - Redirige vers `/main/dashboard` (tableau-de-bord)
   - Pas de besoin de profil santé pour l'admin

4. **Affichage du Dashboard** (TableauDeBordPage)
   - Détecte `authStore.estAdministrateur`
   - Affiche le `TableauDeBordAdministrateur`
   - Affiche la gestion complète des utilisateurs

## 🎯 Pointde des Utilisateurs Normaux

- L'utilisateur normal se connecte avec les **mêmes identifiants que les autres**
- S'il n'a pas encore de profil santé → redirigé vers `/profil-sante`
- S'il a un profil santé → redirigé vers `/main/dashboard` (v patient)

## 📋 Comportement du Dashboard Admin

Le dashboard administrateur affiche:
- ✅ Statistiques (nombre d'utilisateurs, admins, médecins)
- ✅ Tableau de tous les utilisateurs
- ✅ Recherche par nom
- ✅ Filtre par rôle
- ✅ Édition des utilisateurs (changer nom, email, rôle, statut)
- ✅ Suppression des utilisateurs
- ✅ Activation/Désactivation du statut

### API Endpoints
- `GET /api/admin/utilisateurs` - Liste tous les utilisateurs
- `PUT /api/admin/utilisateurs/{id}` - Modifie un utilisateur
- `DELETE /api/admin/utilisateurs/{id}` - Supprime un utilisateur

## 🧪 Comment Tester

### Test 1: Connexion Admin
1. Aller à `http://localhost/login` (ou votre URL frontend)
2. Entrer:
   - Email: `admin@gmail.com`
   - Mot de passe: `admin1234`
3. ✅ Vérifié: Redirigé vers le dashboard administrateur

### Test 2: Les Utilisateurs Normaux
- Créer des comptes utilisateur normaux via le formulaire d'inscription
- Vérifier qu'ils vont sur leur propre dashboard (patient)
- Vérifier que seul l'admin peut voir la gestion des utilisateurs

### Test 3: Fonctionnalités Admin
1. Lister les utilisateurs
2. Modifier un utilisateur (changer le nom, email, rôle)
3. Activer/désactiver un utilisateur
4. Supprimer un utilisateur (vérifier la confirmation)
5. Rechercher/filtrer les utilisateurs

## 🔒 Sécurité

- Le rôle 'administrateur' est stocké dans la base de données
- Les endpoints API sont protégés par le guard `verifierAccesAdministrateur()`
- Seuls les utilisateurs avec role='administrateur' ou 'admin' peuvent accéder au dashboard
- Les tokens sont invalidés à chaque connexion

## 📝 Code Important

### Backend (AuthController)
```php
$isAdmin = in_array($user->role, ['admin', 'administrateur'], true);
// Redirige vers '/main/dashboard' si admin
```

### Frontend (Router)
```javascript
if (authStore.estAdministrateur) {
  return { name: "tableau-de-bord" };
}
```

### Frontend (Auth Store)
```javascript
const estAdministrateur = computed(() => 
  roleUtilisateur.value === 'administrateur' || 
  roleUtilisateur.value === 'admin'
)
```

## ⚠️ Points Importants

- ✅ L'admin se connecte via la **même interface** que les autres utilisateurs
- ✅ **Pas de page de connexion distincte** pour l'admin
- ✅ Le code reste **simple et en français**
- ✅ Les fonctionnalités existantes ne sont **pas affectées**
- ✅ Le dashboard admin a ses **propres routes** et composants

## 🚀 Prochaines Étapes (Optionnel)

- Ajouter des permissions fines (empêcher un admin de supprimer un autre admin)
- Ajouter la pagination de la liste des utilisateurs
- Ajouter les logs d'audit des actions de l'admin
- Envoyer des emails lors des changements d'état utilisateur
