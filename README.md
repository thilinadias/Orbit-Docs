# OrbitDocs

**OrbitDocs** is an open-source IT Documentation Platform designed for MSPs, internal IT teams, and system administrators. It serves as a centralized hub for managing assets, credentials, and documentation with multi-organization support.

<img width="1918" height="954" alt="10" src="https://github.com/user-attachments/assets/d99246d9-2e0c-4a30-92df-e12609641b0b" />


## Features

- **Multi-Organization Support**: Manage multiple clients or departments with isolation.
- **Asset Management**: Track servers, workstations, networking gear, and more.
- **Credential Vault**: Securely store and share passwords with AES-256 encryption.
- **Documentation**: Write and organize internal wikis using Markdown.
- **Activity Logs**: Audit trail for all changes.
- **Modern UI**: Clean, dark-mode compatible interface built with TailwindCSS and Alpine.js.

## Recent Updates (February 2026)

### Production Deployment Fixes (v1.1 — Feb 22 2026)

A comprehensive overhaul of the Docker deployment pipeline to make production deployments reliable:

- **Critical Fix — Stale Code on Re-deploy:** The entrypoint now uses `rsync` on every container start, so code updates from a rebuilt image are always applied. Previously, the production server permanently ran the first-deployed version of the code.
- **Critical Fix — MySQL Healthcheck:** The DB healthcheck was silently failing on every boot (Docker does not expand `${VARIABLES}` in CMD-array format). Fixed to use `CMD-SHELL`. The `app` container now correctly waits for a healthy database before starting.
- **Security — Debug Files Removed:** Development helper scripts (`debug_roles.php`, `fix_cred_perm.php`, `force_suspend.php`, etc.) have been removed from the repository. They were being baked into the production Docker image.
- **Startup UX:** A branded maintenance page now shows instead of a raw browser 502 error while the app initialises. It auto-refreshes every 8 seconds.
- **Dockerfile:** Removed unnecessary `nginx` from the php-fpm container (~50MB saved). Added `rsync`.
- **PHP Config:** Fixed Windows CRLF line endings in `local.ini` that caused PHP to misread config values. `max_execution_time` now correctly set to `300s` for all requests (the installer sets `0` itself for migrations).
- **Environment:** `.env.example` updated with production-safe defaults (`APP_ENV=production`, `APP_DEBUG=false`, correct Docker service names pre-set).
- **Volumes:** Replaced host bind-mount for `storage/logs` with a named Docker volume to prevent root-ownership issues on fresh servers.

### Key Improvements (Earlier February 2026)
- **Installer Flow:** Added a new step to automatically create your first Organization during installation.
- **Bug Fixes:**
    - Resolved "Table already exists" errors during migration.
    - Fixed "Column not found: role" error when creating organizations.
    - Fixed infinite redirect loop on the Global Dashboard (demo-msp issue).
    - Fixed "slug on string" crash when editing documents.
- **Document Management:** Added support for Tags, Categories, Authors, and Approval Statuses.

---

## Installation via Docker (Recommended)

OrbitDocs is designed to be installed easily using Docker. This method includes a built-in **Web Installer** that guides you through the setup process.

### Prerequisites

- Docker Engine
- Docker Compose

### Quick Start (New Installation)

1.  **Clone the Repository**
    ```bash
    git clone https://github.com/thilinadias/orbit-docs.git
    cd orbit-docs
    ```

2.  **Build and Start**
    ```bash
    docker-compose up -d --build
    ```
    The containers will start. The app automatically:
    - Waits for MySQL to become healthy before starting
    - Runs any pending database migrations in the background
    - Shows a branded loading page while initialising (instead of a 502 error)

3.  **Access the Installer**
    Open your browser and navigate to `http://<your-server-ip>`.
    - Follow the on-screen wizard
    - Create your Admin Account
    - Create your first Organization (your main workspace)
    - You will be automatically redirected to your new dashboard

> **Note:** Database migrations run automatically in the background on first boot. If you prefer to run them manually:
> ```bash
> docker-compose exec app php artisan migrate
> ```

### Updating Existing Installations

For routine updates (code changes, bug fixes):

```bash
git pull origin master
docker-compose up -d --build
```

Code changes from the new image are automatically synced to the running volume on startup. Pending migrations are applied in the background automatically. **No manual cache clearing or migration commands needed.**

> **First-time update from a version before v1.1?** Run this instead to clear old volumes:
> ```bash
> git pull origin master
> docker-compose down -v
> docker-compose up -d --build
> ```
> ⚠️ `down -v` removes ALL data volumes. Only use this on a fresh install or if you don't mind losing existing data.

## Troubleshooting

**App shows "Starting Up" page for a long time**
This is normal on first boot — the database container takes 30-60 seconds to initialise, then migrations run. The page auto-refreshes every 8 seconds. If it persists beyond 5 minutes:
```bash
docker logs orbitdocs-app -f   # look for [OrbitDocs] messages
docker ps                       # check all containers are healthy
```

**After `docker-compose up --build`, the site still shows old content**
You are likely on a pre-v1.1 version of OrbitDocs. The old entrypoint only seeded the volume once and never applied updates. Upgrade by clearing volumes:
```bash
git pull origin master
docker-compose down -v
docker-compose up -d --build
```
> ⚠️ `down -v` removes data volumes. Back up your data first if needed.

**Installer Timeout / "Server Error (Likely Timeout)"**
If the installer times out during migrations, you can run them manually:
```bash
docker-compose exec app php artisan migrate:fresh --seed
```
Then refresh the installer page.

**Default Login Credentials (If Seeder Used)**
*   **Email:** `admin@orbitdocs.com`
*   **Password:** `password`

---

## Manual Configuration (Advanced)

If you prefer to configure the system manually or need to update settings after installation, you can use the following methods.

### Manual SSL Configuration

The Web Installer allows you to upload SSL certificates (`.crt` and `.key`). If you need to update them later:

1.  **Locate the Volume**: The certificates are stored in the `orbitdocs_ssl` Docker volume. You can access this via the host mount path defined in `docker-compose.yml` or by copying files into the container.
    
    *Recommended method (Copy via Docker):*
    ```bash
    # Copy your new certificate and key to the Web container
    docker cp your_domain.crt orbitdocs-web:/etc/nginx/ssl/orbitdocs.crt
    docker cp your_domain.key orbitdocs-web:/etc/nginx/ssl/orbitdocs.key
    ```

2.  **Restart Nginx**:
    ```bash
    docker-compose restart web
    ```

### Manual Domain Configuration

To change the domain name after installation:

1.  **Update .env**:
    Edit the `.env` file in your installation directory and update `APP_URL`.
    ```env
    APP_URL=https://new-domain.com
    ```

2.  **Update Nginx Config (If needed)**:
    The Nginx configuration is mounted at `./docker/nginx/conf.d/default.conf`.
    -   Edit this file to update `server_name` or add manual redirects.
    -   Restart the web container: `docker-compose restart web`.

---

## Contributing

We welcome contributions! Please read [CONTRIBUTING.md](CONTRIBUTING.md) for details.

## Testing

To run the application tests, use the following command:

```bash
php artisan test
```

The testing environment is configured to use an in-memory SQLite database (`:memory:`), ensuring fast and isolated tests without requiring a separate MySQL database.
All migrations are automatically run before each test using the `RefreshDatabase` trait.

## License

This project is licensed under the Apache 2.0 License.

## Few glimpses inside the system

<img width="1886" height="943" alt="1" src="https://github.com/user-attachments/assets/8f8aeee5-bbf6-4aa5-a0bd-0385a03b94e4" />
<img width="1912" height="945" alt="2" src="https://github.com/user-attachments/assets/b810b307-22f0-4d75-bb44-8250eaa289cf" />
<img width="1915" height="949" alt="3" src="https://github.com/user-attachments/assets/ddbd7b6d-27a7-4444-8a51-941fe96aa45f" />
<img width="1916" height="951" alt="4" src="https://github.com/user-attachments/assets/29bb6153-320d-468b-a6cf-e615eebd989b" />
<img width="1921" height="945" alt="5" src="https://github.com/user-attachments/assets/c52546cc-d9cd-4172-b210-aface9d6ed99" />
<img width="1916" height="950" alt="6" src="https://github.com/user-attachments/assets/1878956a-f4ea-4f94-ac01-d91f2618e9cc" />
<img width="1916" height="953" alt="7" src="https://github.com/user-attachments/assets/12e6d967-9b07-43b8-ba60-594ac7bcf42b" />
<img width="1917" height="948" alt="8" src="https://github.com/user-attachments/assets/0eab0d2a-390f-4df5-ac90-f6028e62b822" />
<img width="1918" height="948" alt="9" src="https://github.com/user-attachments/assets/0c2ce785-4f99-4174-bbd6-8bf3e0f71945" />
<img width="1918" height="954" alt="10" src="https://github.com/user-attachments/assets/8cb02f69-13d7-4d13-ad6b-7eff9fd744cb" />

