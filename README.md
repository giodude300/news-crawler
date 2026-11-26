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

- **Crawler:\*\*** Retrieves and extracts the first 30 stories from Hacker News.
- **Story Processor:\*\*** Counts words, classifies stories, and applies the sorting rules.
- **Story Processing Service:\*\*** Coordinates the crawling and processing workflow and persists execution results.
- **Models:\*\*** Represent processing executions and their associated stories.
- **Controller:\*\*** Exposes the REST API endpoints.

The processing workflow uses database transactions to keep execution metadata and associated stories consistent.

### Story Processing Rules

| Category | Condition | Sorting |
|---|---|---|
| Long titles | More than 5 words | Comments descending |
| Short titles | 5 words or fewer | Points descending |

Words are counted while ignoring punctuation and symbols.

Example:

```text
"This is - a self-explained example"
```

The result is **5 words\*\***.

When two stories have the same sorting value, their original relative order is preserved.

### Data Persistence

The application uses two main entities.

**Processing Executions\*\*** store:

- Request timestamp.
- Applied filters.
- Number of processed stories.
- Processing duration.

**Stories\*\*** store:

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

The execution history supports these query parameters:

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

Install the following tools locally:

- Docker Desktop
- Docker Compose
- Git

PHP, Composer, Node.js, npm, and PostgreSQL run inside Docker containers.

## Getting Started

### 1. Clone the repository

```powershell
git clone https://github.com/giodude300/news-crawler.git
cd news-crawler
```

### 2. Configure Laravel

Create the local environment file (PowerShell):
```powershell
Copy-Item backend/.env.example backend/.env
# macOS (Terminal)
cp backend/.env.example backend/.env
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

### 3. Build and start the application
```powershell

docker compose up --build -d
docker compose exec backend php artisan optimize:clear
docker compose exec backend php artisan l5-swagger:generate
```
The `frontend` service is a one-shot build container. It installs frontend dependencies, builds the React application, and copies the generated files to the shared `frontend_dist` volume. The `backend` service waits for that build and for PostgreSQL to become healthy, then serves the application through Laravel.

### 4. Generate the Laravel application key

```powershell
docker compose exec backend php artisan key:generate
```

### 5. Run database migrations

```powershell

docker compose exec backend php artisan migrate
```

### 6. Access the application

| Service | URL |
|---|---|

| Web application (React served by Laravel) | http://localhost:8000/ |

| Backend API | http://localhost:8000/api |

| Swagger documentation | http://localhost:8000/api/documentation |

| PostgreSQL | localhost:5432 |

The main application entry point is `http://localhost:8000/`. The frontend is built with Vite; Laravel serves the generated frontend assets and the REST API. There is no always-running Vite development server in the Docker Compose setup described here.

## Docker Commands

```powershell

\# Build and start all services

docker compose up --build -d

\# Start existing services

docker compose up -d

\# Stop services (preserves named volumes)

docker compose down

\# Show service status

docker compose ps

\# Follow all service logs

docker compose logs -f

\# Follow backend logs

docker compose logs -f backend

\# Show frontend build logs

docker compose logs frontend

\# Show database logs

docker compose logs -f database

```

The `frontend` service completes after building the assets, so it does not remain running. Use `docker compose run --rm -v "${PWD}/frontend:/app" frontend ...` to execute one-off frontend commands. Do not use `docker compose exec frontend`.

To stop containers and delete the local PostgreSQL data volume, run:

```powershell

docker compose down -v
```

**Warning:\*\*** this permanently deletes the local PostgreSQL data.

## Code Quality and Formatting

### Backend — PHP-CS-Fixer

Apply PHP formatting:

```powershell

docker compose exec backend composer format
```

Check formatting without modifying files:

```powershell

docker compose exec backend composer format:check
```

These Composer scripts must exist in `backend/composer.json`. If they are not configured yet, add the following entries to the existing `scripts` object, preserving the other scripts:

```json
{
  "scripts": {
    "format": "php-cs-fixer fix --config=.php-cs-fixer.dist.php",
    "format:check": "php-cs-fixer fix --config=.php-cs-fixer.dist.php --dry-run --diff"
  }
}
```

### Frontend — ESLint

Check JavaScript and TypeScript:

```powershell

docker compose run --rm -v "${PWD}/frontend:/app" frontend npm run lint
# macOS (Terminal)
docker compose run --rm -v "$(pwd)/frontend:/app" frontend npm run lint

```

Automatically fix JavaScript and TypeScript issues, if the script is available:

```powershell

docker compose run --rm -v "${PWD}/frontend:/app" frontend npx eslint src/App.tsx --fix
# macOS (Terminal)
docker compose run --rm -v "$(pwd)/frontend:/app" frontend npx eslint src/App.tsx --fix

```

### Frontend — Prettier

Format frontend source files:

```powershell

docker compose run --rm -v "${PWD}/frontend:/app" frontend npx prettier --write src
# macOS (Terminal)
docker compose run --rm -v "$(pwd)/frontend:/app" frontend npx prettier --write src

```

Check formatting without modifying files:

```powershell

docker compose run --rm -v "${PWD}/frontend:/app" frontend npm run format:check
# macOS (Terminal)
docker compose run --rm -v "$(pwd)/frontend:/app" frontend npm run format:check

```

The frontend service is a one-shot container, so use `docker compose run --rm -v "${PWD}/frontend:/app" frontend` for these commands rather than `docker compose exec frontend`.

## Testing

### Backend

Run the PHPUnit test suite:

```powershell

docker compose exec backend php artisan test
```

### Frontend

Run the Vitest test suite:

```powershell

docker compose run --rm -v "${PWD}/frontend:/app" frontend npm run test
# macOS (Terminal)
docker compose run --rm -v "$(pwd)/frontend:/app" frontend npm run test

```

## Full Validation Checklist

Run these commands from the project root before submitting the assessment. Frontend commands run in temporary containers because the `frontend` service exits after its build.

```powershell

\# Backend tests

docker compose exec backend php artisan test

\# Backend formatting check

docker compose exec backend composer format:check

\# Frontend tests

docker compose run --rm -v "${PWD}/frontend:/app" frontend npm run test
# macOS (Terminal)
docker compose run --rm -v "$(pwd)/frontend:/app" frontend npm run test

\# Frontend lint

docker compose run --rm -v "${PWD}/frontend:/app" frontend npm run lint
# macOS (Terminal)
docker compose run --rm -v "$(pwd)/frontend:/app" frontend npm run lint

\# Frontend formatting check

docker compose run --rm -v "${PWD}/frontend:/app" frontend npm run format:check
# macOS (Terminal)
docker compose run --rm -v "$(pwd)/frontend:/app" frontend npm run format:check

\# Frontend production build and asset copy

docker compose up --build -d

\# Check service status

docker compose ps

```

If any npm script is not defined in `frontend/package.json`, npm will report `Missing script`. Check the available scripts in that file and use the matching configured script.

**---**

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

- **Crawler:\*\*** obtiene y extrae las primeras 30 historias de Hacker News.
- **Story Processor:\*\*** cuenta las palabras, clasifica las historias y aplica las reglas de ordenamiento.
- **Story Processing Service:\*\*** coordina el flujo de obtención y procesamiento, y persiste los resultados de cada ejecución.
- **Modelos:\*\*** representan las ejecuciones de procesamiento y sus historias asociadas.
- **Controlador:\*\*** expone los endpoints de la API REST.

El flujo de procesamiento utiliza transacciones de base de datos para mantener consistentes los metadatos de cada ejecución y las historias asociadas.

### Reglas de procesamiento

| Categoría | Condición | Ordenamiento |
|---|---|---|
| Long titles | Más de 5 palabras | Comentarios descendente |
| Short titles | 5 palabras o menos | Puntos descendente |

Las palabras se cuentan ignorando los signos de puntuación y símbolos.

Ejemplo:

```text
"This is - a self-explained example"
```

El resultado es **5 palabras\*\***.

Cuando dos historias tienen el mismo valor de ordenamiento, se conserva su orden relativo original.

### Persistencia de datos

La aplicación utiliza dos entidades principales.

**Processing Executions\*\*** almacena:

- Fecha y hora de la solicitud.
- Filtros aplicados.
- Número de historias procesadas.
- Duración del procesamiento.

**Stories\*\*** almacena:

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

Instala localmente:

- Docker Desktop
- Docker Compose
- Git

PHP, Composer, Node.js, npm y PostgreSQL se ejecutan dentro de contenedores Docker.

## Instalación

### 1. Clonar el repositorio

```powershell

git clone https://github.com/giodude300/news-crawler.git
cd news-crawler
```

### 2. Configurar Laravel

Crear el archivo de entorno local (PowerShell):

```powershell
Copy-Item backend/.env.example backend/.env
# macOS (Terminal)
cp backend/.env.example backend/.env
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

### 3. Construir e iniciar la aplicación

```powershell

docker compose up --build -d
docker compose exec backend php artisan optimize:clear
docker compose exec backend php artisan l5-swagger:generate
```
El servicio `frontend` es un contenedor de compilación que se ejecuta una sola vez. Instala las dependencias del frontend, compila React y copia los archivos generados al volumen compartido `frontend_dist`. El servicio `backend` espera a que termine esa compilación y a que PostgreSQL esté saludable; después sirve la aplicación mediante Laravel.

### 4. Generar la clave de Laravel

```powershell

docker compose exec backend php artisan key:generate
```

### 5. Ejecutar las migraciones

```powershell

docker compose exec backend php artisan migrate
```

### 6. Acceder a la aplicación

| Servicio | URL |
|---|---|

| Aplicación web (React servido por Laravel) | http://localhost:8000/ |

| Backend API | http://localhost:8000/api |
| Documentación Swagger | http://localhost:8000/api/documentation |

| PostgreSQL | localhost:5432 |

La URL principal es `http://localhost:8000/`. El frontend se compila con Vite; Laravel sirve los archivos generados y la API REST. La configuración Docker Compose descrita aquí no mantiene un servidor de desarrollo Vite ejecutándose permanentemente.

## Comandos Docker

```powershell

\# Construir e iniciar todos los servicios

docker compose up --build -d

\# Iniciar los servicios existentes

docker compose up -d

\# Detener servicios (conserva los volúmenes nombrados)

docker compose down

\# Ver estado de los servicios

docker compose ps

\# Seguir los logs de todos los servicios

docker compose logs -f

\# Seguir los logs del backend

docker compose logs -f backend

\# Consultar los logs de la compilación frontend

docker compose logs frontend

\# Seguir los logs de la base de datos

docker compose logs -f database

```

El servicio `frontend` termina después de compilar los archivos, por lo que no permanece ejecutándose. Para ejecutar comandos puntuales del frontend, utiliza `docker compose run --rm -v "${PWD}/frontend:/app" frontend ...`. No utilices `docker compose exec frontend`.

Para detener los contenedores y eliminar el volumen local de PostgreSQL:

```powershell

docker compose down -v
```

**Advertencia:\*\*** este comando elimina permanentemente los datos locales de PostgreSQL.

## Calidad de código y formato

### Backend — PHP-CS-Fixer

Aplicar formato a los archivos fuente del frontend:

```powershell

docker compose exec backend composer format
```

Comprobar formato sin modificar archivos:

```powershell

docker compose exec backend composer format:check
```

Estos scripts de Composer deben existir en `backend/composer.json`. Si todavía no están configurados, agrega las siguientes entradas al objeto `scripts` existente, conservando los demás scripts:

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

```powershell

docker compose run --rm -v "${PWD}/frontend:/app" frontend npm run lint
# macOS (Terminal)
docker compose run --rm -v "$(pwd)/frontend:/app" frontend npm run lint

```

Corregir automáticamente los problemas de JavaScript y TypeScript, si el script está disponible:

```powershell

docker compose run --rm -v "${PWD}/frontend:/app" frontend npx eslint src/App.tsx --fix
# macOS (Terminal)
docker compose run --rm -v "$(pwd)/frontend:/app" frontend npx eslint src/App.tsx --fix

```

### Frontend — Prettier

Aplicar formato:

```powershell

docker compose run --rm -v "${PWD}/frontend:/app" frontend npx prettier --write src
# macOS (Terminal)
docker compose run --rm -v "$(pwd)/frontend:/app" frontend npx prettier --write src

```

Comprobar formato sin modificar archivos:

```powershell

docker compose run --rm -v "${PWD}/frontend:/app" frontend npm run format:check
# macOS (Terminal)
docker compose run --rm -v "$(pwd)/frontend:/app" frontend npm run format:check

```

El servicio frontend es un contenedor temporal; por eso estos comandos utilizan `docker compose run --rm -v "${PWD}/frontend:/app" frontend` en lugar de `docker compose exec frontend`.

## Pruebas

### Backend

Ejecutar la suite de PHPUnit:

```powershell

docker compose exec backend php artisan test
```

### Frontend

Ejecutar la suite de Vitest:

```powershell

docker compose run --rm -v "${PWD}/frontend:/app" frontend npm run test
# macOS (Terminal)
docker compose run --rm -v "$(pwd)/frontend:/app" frontend npm run test

```

## Lista de validación final

Ejecuta estos comandos desde la raíz del proyecto antes de entregar el assessment. Los comandos del frontend se ejecutan en contenedores temporales porque el servicio `frontend` termina después de compilar.

```powershell

\# Pruebas del backend

docker compose exec backend php artisan test

\# Verificar formato del backend

docker compose exec backend composer format:check

\# Pruebas del frontend

docker compose run --rm -v "${PWD}/frontend:/app" frontend npm run test
# macOS (Terminal)
docker compose run --rm -v "$(pwd)/frontend:/app" frontend npm run test

\# ESLint del frontend

docker compose run --rm -v "${PWD}/frontend:/app" frontend npm run lint
# macOS (Terminal)
docker compose run --rm -v "$(pwd)/frontend:/app" frontend npm run lint

\# Verificar formato del frontend

docker compose run --rm -v "${PWD}/frontend:/app" frontend npm run format:check
# macOS (Terminal)
docker compose run --rm -v "$(pwd)/frontend:/app" frontend npm run format:check

\# Compilar frontend y copiar los assets al volumen compartido

docker compose up --build -d

\# Revisar estado de los servicios

docker compose ps

```

Si un script de npm no está definido en `frontend/package.json`, npm mostrará `Missing script`. Revisa los scripts disponibles en ese archivo y utiliza el comando que esté configurado.
