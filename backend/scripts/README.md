# Scripts Utilitaires d'Administration

Ce dossier contient des scripts utilitaires pour gérer l'administration de l'application.

## Fichiers et utilisation

### 1. **creer_admin.php**

**Rôle:** Crée un nouvel compte administrateur

**Utilisation:**

```bash
php scripts/creer_admin.php
```

**Ce qu'il fait:**

- Crée un compte administrateur avec email `admin@gmail.com`
- Définit le mot de passe `admin1234`
- Assigne le rôle `administrateur`
- Si le compte existe déjà, il est écrasé (risque de doublons)

⚠️ **Note:** Utiliser plutôt `creer_admin_new.php` qui supprime l'ancien avant de recreer.

---

### 2. **creer_admin_new.php** ⭐ (RECOMMANDÉ)

**Rôle:** Crée un nouveau compte administrateur en supprimant l'ancien d'abord

**Utilisation:**

```bash
php scripts/creer_admin_new.php
```

**Ce qu'il fait:**

- Supprime d'abord le compte admin existant (if any)
- Crée un nouveau compte administrateur
- Email: `admin@gmail.com`
- Mot de passe: `admin1234`
- Rôle: `administrateur`
- Le mot de passe est hashé de manière sécurisée

**Utilisé pour:** Réinitialiser le compte admin si oublié ou corrompu

---

### 3. **tester_connexion_admin.php**

**Rôle:** Teste si le compte admin peut se connecter

**Utilisation:**

```bash
php scripts/tester_connexion_admin.php
```

**Ce qu'il fait:**

- Cherche le compte `admin@gmail.com` en BDD
- Affiche ses détails (email, rôle, mot de passe hashé)
- Teste si le mot de passe `admin1234` correspond
- Génère un token de test pour simuler une connexion

**Utilisé pour:** Déboguer les problèmes de connexion admin

---

### 4. **verifier_admin.php**

**Rôle:** Vérifie la configuration complète de l'admin

**Utilisation:**

```bash
php scripts/verifier_admin.php
```

**Ce qu'il fait:**

- Affiche les infos du compte admin en BDD
- Compte le nombre total d'utilisateurs
- Liste tous les utilisateurs et leurs rôles

**Utilisé pour:** Inspecter l'état global des utilisateurs

---

## Flux recommandé

1. **Initialiser l'admin:**

    ```bash
    php scripts/creer_admin_new.php
    ```

2. **Vérifier la création:**

    ```bash
    php scripts/verifier_admin.php
    ```

3. **Tester la connexion:**

    ```bash
    php scripts/tester_connexion_admin.php
    ```

4. **Connexion dans l'app:**
    - Email: `admin@gmail.com`
    - Mot de passe: `admin1234`

---

## Notes de sécurité

⚠️ **ATTENTION:**

- Ces scripts aident à développer/déboguer localement
- Les mots de passe sont en dur dans le code (JAMAIS en production!)
- Ne jamais committer des credentials réels en Git
- En production, utiliser des variables d'environnement et des migrations Laravel

---
