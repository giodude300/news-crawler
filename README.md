# News Crawler

A web application that crawls the first 30 entries from Hacker News and processes them according to title length, points, and comment count.

## Project Overview

This project is part of a technical coding assessment.

The application will:

1. Crawl the first 30 entries from Hacker News.
2. Extract the entry number, title, points, and number of comments.
3. Filter entries with more than five words in the title and sort them by comment count.
4. Filter entries with five words or fewer and sort them by points.
5. Record each execution with its timestamp and applied filter.

### Technology Stack

- Backend: PHP 8.3 and Laravel 13
- Frontend: React and TypeScript
- Database: PostgreSQL 16
- Frontend tooling: Vite
- Backend code style: PHP-CS-Fixer
- Frontend code style: ESLint and Prettier
- Containers: Docker and Docker Compose
- Backend tests: PHPUnit
- Frontend tests: Vitest

## Technical Decisions

### Why Docker?

Docker was chosen to avoid compatibility issues between software versions and dependencies, and to ensure that every developer works with the same environment.

This approach simplifies the initial setup, reduces differences between local and production environments, and makes the application easier to reproduce, run, and deploy. PHP, Composer, Node.js, npm, and PostgreSQL run inside containers, so the project does not depend on the versions installed directly on each developer's machine.

## Requirements

You only need the following tools installed locally:

- Docker Desktop
- Docker Compose
- Git

PHP, Composer, Node.js, npm, and PostgreSQL run inside Docker containers.

## Getting Started

### 1. Clone the repository

```bash
git clone https://github.com/giodude300/news-crawler.git
cd news-crawler
```

### 2. Configure Laravel

Create the local environment file:

```powershell
Copy-Item backend/.env.example backend/.env
```

Make sure the database settings in `backend/.env` are:

```dotenv
DB_CONNECTION=pgsql
DB_HOST=database
DB_PORT=5432
DB_DATABASE=news_crawler
DB_USERNAME=crawler
DB_PASSWORD=crawler_password
```

### 3. Start the application

```bash
docker compose up --build -d
```

### 4. Generate the Laravel application key

```bash
docker compose exec backend php artisan key:generate
```

### 5. Run database migrations

```bash
docker compose exec backend php artisan migrate
```

### 6. Access the application

| Service | URL |
|---|---|
| Backend | http://localhost:8000 |
| Frontend | http://localhost:5173 |
| PostgreSQL | localhost:5432 |

## Docker Commands

```bash
# Start
docker compose up -d

# Build and start
docker compose up --build -d

# Stop
docker compose down

# Show containers
docker compose ps

# Follow all logs
docker compose logs -f

# Follow backend logs
docker compose logs -f backend

# Follow frontend logs
docker compose logs -f frontend
```

To stop containers and delete the local PostgreSQL volume:

```bash
docker compose down -v
```

**Warning:** this permanently deletes the local PostgreSQL data.

## Code Quality and Formatting

### Backend — PHP-CS-Fixer

Apply PHP formatting:

```bash
docker compose exec backend composer format
```

Check formatting without modifying files:

```bash
docker compose exec backend composer format:check
```

These Composer scripts must be added to `backend/composer.json`:

```json
{
    "scripts": {
        "format": "php-cs-fixer fix --config=.php-cs-fixer.dist.php",
        "format:check": "php-cs-fixer fix --config=.php-cs-fixer.dist.php --dry-run --diff"
    }
}
```

Keep the existing Composer scripts; add these two entries to the existing `scripts` object.

### Frontend — ESLint

Check JavaScript and TypeScript:

```bash
docker compose exec frontend npm run lint
```

Automatically fix JavaScript and TypeScript issues:

```bash
docker compose exec frontend npm run lint:fix
```

### Frontend — Prettier

Format supported frontend files:

```bash
docker compose exec frontend npm run format
```

Check formatting without modifying files:

```bash
docker compose exec frontend npm run format:check
```

Prettier should ignore JavaScript and TypeScript files so ESLint can enforce the project's brace and indentation conventions.

## Testing

### Backend

```bash
docker compose exec backend php artisan test
```

### Frontend

```bash
docker compose exec frontend npm run test
```

---

# News Crawler

Aplicación web que recopila las primeras 30 entradas de Hacker News y las procesa según la longitud del título, los puntos y el número de comentarios.

## Descripción del proyecto

Este proyecto forma parte de una evaluación técnica de programación.

La aplicación deberá:

1. Recopilar las primeras 30 entradas de Hacker News.
2. Extraer el número de entrada, título, puntos y número de comentarios.
3. Filtrar las entradas cuyos títulos tengan más de cinco palabras y ordenarlas por número de comentarios.
4. Filtrar las entradas cuyos títulos tengan cinco palabras o menos y ordenarlas por puntos.
5. Registrar cada ejecución con su fecha, hora y filtro aplicado.

### Tecnologías

- Backend: PHP 8.3 y Laravel 13
- Frontend: React y TypeScript
- Base de datos: PostgreSQL 16
- Herramientas frontend: Vite
- Estilo backend: PHP-CS-Fixer
- Estilo frontend: ESLint y Prettier
- Contenedores: Docker y Docker Compose
- Pruebas backend: PHPUnit
- Pruebas frontend: Vitest

## Decisiones técnicas

### ¿Por qué Docker?

Se decidió utilizar Docker para evitar problemas de compatibilidad entre versiones de software y dependencias, y garantizar que todos los desarrolladores trabajen con el mismo entorno.

Este enfoque simplifica la configuración inicial, reduce las diferencias entre los entornos locales y de producción, y facilita la reproducción, ejecución y despliegue de la aplicación. PHP, Composer, Node.js, npm y PostgreSQL se ejecutan dentro de contenedores, por lo que el proyecto no depende de las versiones instaladas directamente en cada máquina de desarrollo.

## Requisitos

Únicamente necesitas tener instalados localmente:

- Docker Desktop
- Docker Compose
- Git

PHP, Composer, Node.js, npm y PostgreSQL se ejecutan dentro de contenedores Docker.

## Instalación

### 1. Clonar el repositorio

```bash
git clone https://github.com/giodude300/news-crawler.git
cd news-crawler
```

### 2. Configurar Laravel

Crear el archivo de entorno local:

```powershell
Copy-Item backend/.env.example backend/.env
```

Comprueba que `backend/.env` tenga esta configuración de base de datos:

```dotenv
DB_CONNECTION=pgsql
DB_HOST=database
DB_PORT=5432
DB_DATABASE=news_crawler
DB_USERNAME=crawler
DB_PASSWORD=crawler_password
```

### 3. Iniciar la aplicación

```bash
docker compose up --build -d
```

### 4. Generar la clave de Laravel

```bash
docker compose exec backend php artisan key:generate
```

### 5. Ejecutar las migraciones

```bash
docker compose exec backend php artisan migrate
```

### 6. Acceder a la aplicación

| Servicio | URL |
|---|---|
| Backend | http://localhost:8000 |
| Frontend | http://localhost:5173 |
| PostgreSQL | localhost:5432 |

## Comandos Docker

```bash
# Iniciar
docker compose up -d

# Construir e iniciar
docker compose up --build -d

# Detener
docker compose down

# Ver contenedores
docker compose ps

# Ver todos los registros
docker compose logs -f

# Ver registros del backend
docker compose logs -f backend

# Ver registros del frontend
docker compose logs -f frontend
```

Para detener los contenedores y eliminar el volumen local de PostgreSQL:

```bash
docker compose down -v
```

**Advertencia:** este comando elimina permanentemente los datos locales de PostgreSQL.

## Calidad de código y formato

### Backend — PHP-CS-Fixer

Aplicar formato:

```bash
docker compose exec backend composer format
```

Comprobar formato sin modificar archivos:

```bash
docker compose exec backend composer format:check
```

Estos scripts de Composer deben agregarse a `backend/composer.json`, conservando los scripts existentes:

```json
{
    "scripts": {
        "format": "php-cs-fixer fix --config=.php-cs-fixer.dist.php",
        "format:check": "php-cs-fixer fix --config=.php-cs-fixer.dist.php --dry-run --diff"
    }
}
```

### Frontend — ESLint

Comprobar JavaScript y TypeScript:

```bash
docker compose exec frontend npm run lint
```

Corregir automáticamente:

```bash
docker compose exec frontend npm run lint:fix
```

### Frontend — Prettier

Aplicar formato:

```bash
docker compose exec frontend npm run format
```

Comprobar formato sin modificar archivos:

```bash
docker compose exec frontend npm run format:check
```

Prettier debe ignorar JavaScript y TypeScript para que ESLint controle las convenciones de llaves e indentación.

## Pruebas

### Backend

```bash
docker compose exec backend php artisan test
```

### Frontend

```bash
docker compose exec frontend npm run test
```
