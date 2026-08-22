# Environment Configuration (.env)

Le framework charge automatiquement le fichier `.env` au startup (via `Env::load()` dans les entry points). **Aucune configuration supplémentaire n'est nécessaire.**

## Workflow

1. **Copie l'exemple:**
   ```bash
   cp .env.example .env
   ```

2. **L'application charge le .env automatiquement:**
   - `public/index.php` → `Env::load($basePath . '/.env')`
   - `bin/console.php` → `Env::load($basePath . '/.env')`

3. **Accès aux variables n'importe où:**
   ```php
   env('DATABASE_URL')        // retourne la valeur
   env('JWT_SECRET', 'fallback') // fallback par défaut
   config('app.name')         // via config/app.php qui utilise env()
   ```

## Variables disponibles

### Essentielles
- `APP_NAME` — Nom de l'app
- `APP_ENV` — `local`, `testing`, `production`
- `APP_DEBUG` — `true`/`false` (affiche les erreurs en detail)
- `APP_URL` — URL base
- `APP_LOCALE` — Langue (en, fr, etc.)

### Database
- `DB_CONNECTION` — `sqlite` (défaut), `mysql`, ou `pgsql`
- `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`

### Authentification
- `JWT_SECRET` — Secret pour les tokens JWT
- `SESSION_TIMEOUT` — Secondes avant expiration de session

### Rate limiting
- `RATE_LIMIT_WINDOW` — Fenêtre de temps (secondes, défaut 60)
- `RATE_LIMIT_REQUESTS` — Max requêtes par fenêtre (défaut 100)

### Cache
- `CACHE_DRIVER` — `file` (seul driver pour l'instant)
- `CACHE_TIMEOUT` — Durée en secondes

### Mail (pour futur usage)
- `MAIL_DRIVER`, `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD`, `MAIL_FROM`

## Exemple d'utilisation

```php
// Dans bootstrap.php ou n'importe quel contrôleur:
Auth::setSecret(env('JWT_SECRET'));
\Core\i18n::setLocale(env('APP_LOCALE', 'en'));

// Dans des middleware/contrôleurs/services:
$timeout = (int) env('SESSION_TIMEOUT', '3600');
$debug = filter_var(env('APP_DEBUG'), FILTER_VALIDATE_BOOLEAN);

if ($debug) {
    Log::info('Debug mode enabled');
}
```

## Notes

- Pas de `.env` requis — le framework marche avec les valeurs par défaut
- Format: `KEY=VALUE` (pas d'espace autour du `=`)
- Commentaires: `# ceci est ignoré`
- Quotes optionnelles: `PASSWORD="my secret"` ou `PASSWORD=my secret`
- Les valeurs sont mises dans `$_ENV` et `putenv()` aussi

