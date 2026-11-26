# News Crawler

A web application that crawls the first 30 entries from Hacker News and processes them according to title length, points, and comment count.

## Project Overview

This project is part of a technical coding assessment.

The application:

1. Crawls the first 30 entries from Hacker News.
2. Extracts the entry ID, title, points, and number of comments.
3. Classifies entries with more than five words in the title as long titles and sorts them by comment count in descending order.
4. Classifies entries with five words or fewer as short titles and sorts them by points in descending order.
5. Counts title words while ignoring punctuation and symbols.
6. Records each execution with its timestamp, applied filters, number of processed stories, and processing duration.
7. Stores the processed stories associated with each execution, allowing previous results to be retrieved.
8. Provides an execution history and supports filtering stories by category.

### Technology Stack

- Backend: PHP 8.3 and Laravel 13
- Frontend: React 19 and TypeScript
- Database: PostgreSQL 16
- Frontend tooling: Vite 8
- Backend code style: PHP-CS-Fixer
- Frontend code style: ESLint and Prettier
- Containers: Docker and Docker Compose
- Backend tests: PHPUnit
- Frontend tests: Vitest

## Technical Decisions

### Why Docker?

Docker was chosen to avoid compatibility issues between software versions and dependencies, and to ensure that every developer works with the same environment.

This approach simplifies the initial setup, reduces differences between local and production environments, and makes the application easier to reproduce, run, and deploy. PHP, Composer, Node.js, npm, and PostgreSQL run inside containers, so the project does not depend on the versions installed directly on each developer's machine.

### Backend Architecture

The backend follows a separation of responsibilities:

- **Crawler:** Retrieves and extracts the first 30 stories from Hacker News.
- **Story Processor:** Counts words, classifies stories, and applies the sorting rules.
- **Story Processing Service:** Coordinates the crawling and processing workflow and persists execution results.
- **Models:** Represent processing executions and their associated stories.
- **Controller:** Exposes the REST API endpoints.

The processing workflow uses database transactions to keep execution metadata and associated stories consistent.

### Story Processing Rules

Titles are classified according to their word count:

| Category | Condition | Sorting |
|---|---|---|
| Long titles | More than 5 words | Comments descending |
| Short titles | 5 words or fewer | Points descending |

Words are counted using whitespace separation while ignoring punctuation and symbols.

For example:

```text
"This is - a self-explained example"
```

The result is **5 words**.

When two stories have the same sorting value, their original relative order is preserved.

### Data Persistence

The application uses two main entities:

**Processing Executions**

Stores metadata for each processing request:

- Request timestamp.
- Applied filters.
- Number of processed stories.
- Processing duration.

**Stories**

Stores the stories associated with each execution:

- Hacker News story ID.
- Title.
- Points.
- Comments.
- Word count.
- Assigned category.
- Position within the processed results.

Each execution maintains its own associated stories, allowing historical results to be retrieved independently.

### API Endpoints

| Method | Endpoint | Description |
|---|---|---|
| POST | `/api/stories/process` | Crawls, processes, and stores the first 30 Hacker News stories. |
| GET | `/api/executions` | Returns the processing execution history with pagination and category counts. |
| GET | `/api/executions/{id}` | Returns execution details and associated stories. |

The execution history supports the following query parameters:

| Parameter | Description | Default |
|---|---|---|
| `page` | Page number | `1` |
| `per_page` | Number of executions per page (maximum 30) | `30` |

The execution details endpoint supports an optional `filter` parameter:

| Value | Description |
|---|---|
| `long_titles` | Returns stories with more than 5 words. |
| `short_titles` | Returns stories with 5 words or fewer. |

Examples:

```http
GET /api/executions/1
GET /api/executions/1?filter=long_titles
GET /api/executions/1?filter=short_titles
```

The execution response includes `long_titles_count` and `short_titles_count`, representing the total number of stories in each category, regardless of the applied story filter.

Interactive API documentation is available through Swagger at `/api/documentation`.

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
| Web Application (Frontend) | http://localhost:8000/ |
| Backend API | http://localhost:8000/api |
| Swagger Documentation | http://localhost:8000/api/documentation |
| Frontend Development Server (Vite) | http://localhost:5173/ |
| PostgreSQL | localhost:5432 |

The main application entry point is `http://localhost:8000/`. The React frontend is built with Vite, while Laravel provides the main application entry point and REST API.

The Vite development server is also available at `http://localhost:5173/` when running in development mode.

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

La aplicación:

1. Recopila las primeras 30 entradas de Hacker News.
2. Extrae el identificador, título, puntos y número de comentarios de cada entrada.
3. Clasifica las entradas cuyos títulos tengan más de cinco palabras como títulos largos y las ordena por número de comentarios de mayor a menor.
4. Clasifica las entradas cuyos títulos tengan cinco palabras o menos como títulos cortos y las ordena por puntos de mayor a menor.
5. Cuenta las palabras de los títulos ignorando signos de puntuación y símbolos.
6. Registra cada ejecución con su fecha, hora, filtros aplicados, número de historias procesadas y duración del procesamiento.
7. Almacena las historias procesadas asociadas a cada ejecución, permitiendo consultar resultados anteriores.
8. Proporciona un historial de ejecuciones y permite filtrar las historias por categoría.

### Tecnologías

- Backend: PHP 8.3 y Laravel 13
- Frontend: React 19 y TypeScript
- Base de datos: PostgreSQL 16
- Herramientas frontend: Vite 8
- Estilo backend: PHP-CS-Fixer
- Estilo frontend: ESLint y Prettier
- Contenedores: Docker y Docker Compose
- Pruebas backend: PHPUnit
- Pruebas frontend: Vitest

## Decisiones técnicas

### ¿Por qué Docker?

Se decidió utilizar Docker para evitar problemas de compatibilidad entre versiones de software y dependencias, y garantizar que todos los desarrolladores trabajen con el mismo entorno.

Este enfoque simplifica la configuración inicial, reduce las diferencias entre los entornos locales y de producción, y facilita la reproducción, ejecución y despliegue de la aplicación. PHP, Composer, Node.js, npm y PostgreSQL se ejecutan dentro de contenedores, por lo que el proyecto no depende de las versiones instaladas directamente en cada máquina de desarrollo.

### Arquitectura del backend

El backend mantiene una separación de responsabilidades:

- **Crawler:** obtiene y extrae las primeras 30 historias de Hacker News.
- **Story Processor:** cuenta las palabras, clasifica las historias y aplica las reglas de ordenamiento.
- **Story Processing Service:** coordina el flujo de obtención y procesamiento, y persiste los resultados de cada ejecución.
- **Modelos:** representan las ejecuciones de procesamiento y sus historias asociadas.
- **Controlador:** expone los endpoints de la API REST.

El flujo de procesamiento utiliza transacciones de base de datos para mantener consistentes los metadatos de cada ejecución y las historias asociadas.

### Reglas de procesamiento

Los títulos se clasifican según su cantidad de palabras:

| Categoría | Condición | Ordenamiento |
|---|---|---|
| Long titles | Más de 5 palabras | Comentarios descendente |
| Short titles | 5 palabras o menos | Puntos descendente |

Las palabras se cuentan mediante separación por espacios en blanco, ignorando los signos de puntuación y símbolos.

Por ejemplo:

```text
"This is - a self-explained example"
```

El resultado es **5 palabras**.

Cuando dos historias tienen el mismo valor de ordenamiento, se conserva su orden relativo original.

### Persistencia de datos

La aplicación utiliza dos entidades principales:

**Processing Executions**

Almacena los metadatos de cada solicitud de procesamiento:

- Fecha y hora de la solicitud.
- Filtros aplicados.
- Número de historias procesadas.
- Duración del procesamiento.

**Stories**

Almacena las historias asociadas a cada ejecución:

- Identificador de Hacker News.
- Título.
- Puntos.
- Comentarios.
- Cantidad de palabras.
- Categoría asignada.
- Posición dentro de los resultados procesados.

Cada ejecución mantiene sus propias historias asociadas, permitiendo consultar los resultados históricos de manera independiente.

### Endpoints de la API

| Método | Endpoint | Descripción |
|---|---|---|
| POST | `/api/stories/process` | Obtiene, procesa y almacena las primeras 30 historias de Hacker News. |
| GET | `/api/executions` | Devuelve el historial de ejecuciones con paginación y contadores por categoría. |
| GET | `/api/executions/{id}` | Devuelve el detalle de una ejecución y sus historias asociadas. |

El historial de ejecuciones admite los siguientes parámetros:

| Parámetro | Descripción | Valor por defecto |
|---|---|---|
| `page` | Número de página | `1` |
| `per_page` | Cantidad de ejecuciones por página (máximo 30) | `30` |

El endpoint de detalle de ejecución admite un parámetro opcional `filter`:

| Valor | Descripción |
|---|---|
| `long_titles` | Devuelve historias con más de 5 palabras. |
| `short_titles` | Devuelve historias con 5 palabras o menos. |

Ejemplos:

```http
GET /api/executions/1
GET /api/executions/1?filter=long_titles
GET /api/executions/1?filter=short_titles
```

La respuesta de cada ejecución incluye `long_titles_count` y `short_titles_count`, que representan el total de historias de cada categoría, independientemente del filtro aplicado a la colección de historias.

La documentación interactiva de la API está disponible mediante Swagger en `/api/documentation`.

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
| Aplicación web (Frontend) | http://localhost:8000/ |
| Backend API | http://localhost:8000/api |
| Documentación Swagger | http://localhost:8000/api/documentation |
| Servidor de desarrollo frontend (Vite) | http://localhost:5173/ |
| PostgreSQL | localhost:5432 |

La URL principal de acceso a la aplicación es `http://localhost:8000/`. El frontend está desarrollado con React y Vite, mientras que Laravel proporciona el punto de entrada principal de la aplicación y la API REST.

El servidor de desarrollo de Vite también está disponible en `http://localhost:5173/` cuando se ejecuta en modo desarrollo.

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
