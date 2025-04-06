# ProjeQtOr Docker Setup

This repository automatically syncs with the official ProjeQtOr releases from SourceForge and builds Docker images for easy deployment with SSL support.

## Features

- **Automatic Releases**: Daily checks for new ProjeQtOr versions
- **Optimized Performance**: Nginx with PHP-FPM for faster response times
- **SSL Support**: Traefik reverse proxy with automatic Let's Encrypt certificates
- **Secure by Default**: Network isolation between frontend and backend
- **Fully Configurable**: All settings easily adjustable via environment variables

## Quick Start

1. Clone the repository:
   ```bash
   git clone https://github.com/kylhuk/projeqtor.git
   cd projeqtor
   ```

2. Configure your settings:
   ```bash
   cp .env.example .env
   # Edit .env file with your domain and settings
   ```

3. Start the stack:
   ```bash
   docker compose up -d
   ```

4. Access ProjeQtOr securely at `https://your-domain.com`

## Configuration

All configuration is done through the `.env` file:

| Variable | Description | Default |
|----------|-------------|---------|
| `DOMAIN_NAME` | Your domain for ProjeQtOr | projeqtor.example.com |
| `ACME_EMAIL` | Email for Let's Encrypt | admin@example.com |
| `HTTP_PORT` | HTTP port | 80 |
| `HTTPS_PORT` | HTTPS port | 443 |
| `DB_NAME` | PostgreSQL database name | projeqtor |
| `DB_USER` | PostgreSQL user | projeqtor |
| `DB_PASSWORD` | PostgreSQL password | projeqtor_password |
| `PROJEQTOR_VERSION` | Version tag to use | latest |
| `TIMEZONE` | Server timezone | UTC |

## Architecture

The setup uses a modern Docker Compose architecture:

- **Frontend Network**: Exposed to the internet, contains Traefik and the web frontend
- **Backend Network**: Internal only, contains the database
- **ProjeQtOr**: Connected to both networks to facilitate communication

## Development Mode

For local development without SSL:

1. Edit the `.env` file and uncomment `DEVELOPMENT_MODE=true`
2. Use the development compose file:
   ```bash
   docker compose -f docker-compose.dev.yml up -d
   ```

## First-time Setup

On first access to ProjeQtOr, configure the database connection:

1. Database type: PostgreSQL
2. Host: postgres
3. Port: 5432
4. Database: The value of `DB_NAME` in your `.env` file
5. Username: The value of `DB_USER` in your `.env` file
6. Password: The value of `DB_PASSWORD` in your `.env` file

## Data Persistence

All data is stored in named Docker volumes:
- `projeqtor_data`: ProjeQtOr files, documents and attachments
- `projeqtor_postgres_data`: Database data
- `traefik_data`: SSL certificates

## Available Versions

The Docker images are tagged with ProjeQtOr version numbers:

```bash
# Use a specific version
docker pull ghcr.io/kylhuk/projeqtor:12.1.0

# Use the latest version
docker pull ghcr.io/kylhuk/projeqtor:latest
```

## Manual Deployment

You can manually trigger the build for any version through GitHub Actions:

1. Go to the Actions tab in your repository
2. Select "Build and Push Docker Image" workflow
3. Click "Run workflow"
4. Enter the version number you want to build
