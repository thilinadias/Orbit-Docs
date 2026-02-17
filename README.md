# OrbitDocs

**OrbitDocs** is an open-source IT Documentation Platform designed for MSPs, internal IT teams, and system administrators. It serves as a centralized hub for managing assets, credentials, and documentation with multi-organization support.

![OrbitDocs Dashboard Placeholder](https://via.placeholder.com/800x400?text=OrbitDocs+Dashboard)

## Features

- **Multi-Organization Support**: Manage multiple clients or departments with isolation.
- **Asset Management**: Track servers, workstations, networking gear, and more.
- **Credential Vault**: Securely store and share passwords with AES-256 encryption.
- **Documentation**: Write and organize internal wikis using Markdown.
- **Activity Logs**: Audit trail for all changes.
- **Modern UI**: Clean, dark-mode compatible interface built with TailwindCSS and Alpine.js.

## Installation via Docker (Recommended)

OrbitDocs is designed to be installed easily using Docker. This method includes a built-in **Web Installer** that guides you through the setup process.

### Prerequisites

- Docker Engine
- Docker Compose

### Quick Start

1.  **Clone the Repository**
    ```bash
    git clone https://github.com/thilinadias/orbit-docs.git
    cd orbit-docs
    ```

2.  **Start and Build the Application**
    **Important:** You must use the `--build` flag on the first run to ensure the latest code is used.
    ```bash
    docker-compose up -d --build
    ```

3.  **Run Database Migrations**
    Run the migrations to set up the database schema and seed default data.
    ```bash
    docker-compose exec app php artisan migrate:fresh --seed
    ```
    *Note: The migration script is idempotent and safe to run multiple times.*

4.  **Access the Application**
    Open your browser and navigate to `http://localhost` (or your server's IP).

### Troubleshooting Installation

**Installer Timeout / Stuck?**
If the web installer times out (504 Gateway Timeout) due to long-running migrations, you can manually bypass it:
1.  Run the migrations via CLI (step 3 above).
2.  Run this command to mark the app as installed:
    ```bash
    docker-compose exec app touch storage/app/installed
    ```
3.  Refresh your browser.

**Default Login Credentials**
If you used the seeder (step 3), the default admin account is:
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
