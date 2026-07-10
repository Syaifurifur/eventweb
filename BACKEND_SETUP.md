# EventHub Backend Setup

## Database

Default local database configuration:

- Host: `localhost`
- Database: `eventweb`
- Username: `root`
- Password: empty

Create and seed the database:

```powershell
C:\xampp\mysql\bin\mysql.exe -u root -e "CREATE DATABASE IF NOT EXISTS eventweb CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;"
C:\xampp\php\php.exe -d extension_dir=C:\xampp\php\ext -d extension=intl spark migrate
C:\xampp\php\php.exe -d extension_dir=C:\xampp\php\ext -d extension=intl spark db:seed EventHubSeeder
```

## Demo Accounts

All seeded accounts use password `password`.

- `admin@eventhub.test` - Admin Sistem
- `raka@eventhub.test` - Event Creator
- `dimas@eventhub.test` - Pimpinan
- `maya@eventhub.test` - Marketing Akademik

## Local Server

```powershell
.\serve-local.ps1
```

Open:

```text
http://127.0.0.1:8080/login
```

## Auth Notes

- Passwords use PHP `password_hash()` and `password_verify()`.
- Session ID regenerates on login.
- POST routes use CSRF protection.
- Protected pages use `auth` and `role` filters.
- Failed login attempts are stored in `auth_login_attempts` and throttled after repeated failures.

## Google Calendar API

The app uses OAuth 2.0 web-server flow and the Google Calendar REST API without Composer dependencies.

1. In Google Cloud Console, enable **Google Calendar API**.
2. Create OAuth credentials with application type **Web application**.
3. Add this authorized redirect URI:

```text
http://127.0.0.1:8080/google-calendar/callback
```

4. Fill these values in `.env`:

```env
GOOGLE_CALENDAR_CLIENT_ID=your-client-id.apps.googleusercontent.com
GOOGLE_CALENDAR_CLIENT_SECRET=your-client-secret
GOOGLE_CALENDAR_REDIRECT_URI=http://127.0.0.1:8080/google-calendar/callback
GOOGLE_CALENDAR_ID=primary
GOOGLE_CALENDAR_TIMEZONE=Asia/Jakarta
```

5. Login as `admin@eventhub.test`, open `/calendar`, click **Connect Google Calendar**, approve consent, then click **Sync Google Calendar**.

The integration stores OAuth tokens in `google_oauth_tokens`, local-to-Google sync status/link in `calendar_integrations`, and imported Google events in `google_calendar_events`.

Calendar behavior:

- Use **Bulan Sebelumnya** and **Bulan Berikutnya** to change the visible month.
- **Ambil dari Google** imports Google Calendar events for the visible month into the app.
- **Sync Google Calendar** sends approved local events to Google and then imports Google events for the visible month.
- If you connected Google before this feature was added, reconnect Google Calendar so the token includes the read scope for listing events.
