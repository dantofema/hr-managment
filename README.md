# HR System

A modern HR management system built with Symfony, TailwindCSS, and Vue.js featuring interactive components.

## 🚀 Quick Start

### Prerequisites

- Docker and Docker Compose
- PHP 8.4+
- Node.js 18+
- Composer

### Local Development Setup

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd hr-system
   ```

2. **Start the application with Docker (recommended)**
   ```bash
   docker-compose up -d
   ```
   This will start all services:
   - Symfony app container (port 8000)
   - PostgreSQL database (port 5432)
   - Node.js container with Vite dev server (port 5173)

3. **Access the application**
   - Main application: http://localhost:8000
   - Frontend dev server: http://localhost:5173 (or the port indicated in the terminal, e.g., http://localhost:5174)
   - Home page: http://localhost:8000/

**Important**: All development should be done using Docker containers. The Node.js container automatically runs `npm install` and `npm run dev` for hot reloading.

## 🛠️ Technology Stack

### Backend
- **Symfony 7.x** - PHP framework
- **Doctrine ORM** - Database abstraction layer
- **API Platform** - REST API framework

### Frontend
- **TailwindCSS 4.x** - Utility-first CSS framework
- **Vue.js 3.x** - Progressive JavaScript framework
- **Vite** - Build tool and development server

### Infrastructure
- **Docker** - Containerization
- **PostgreSQL** - Database (via Docker)
- **Nginx** - Web server (via Docker)

## 📁 Project Structure

```
hr-system/
├── config/                 # Symfony configuration
├── frontend/               # Frontend assets and build tools
│   ├── src/               # Vue.js components and CSS
│   ├── node_modules/      # Node.js dependencies
│   ├── package.json       # Node.js dependencies and scripts
│   ├── tailwind.config.js # TailwindCSS configuration
│   └── vite.config.js     # Vite build configuration
├── public/                # Public web assets
│   ├── css/              # Compiled CSS files
│   └── js/               # Compiled JavaScript files
├── src/                   # Symfony application code
│   ├── Controller/       # HTTP controllers
│   ├── Entity/          # Doctrine entities
│   └── Repository/      # Data repositories
├── templates/            # Twig templates
├── tests/               # PHPUnit tests
└── docker-compose.yml   # Docker services configuration
```

## 🎨 Frontend Development

### TailwindCSS

TailwindCSS is configured and ready to use. The compiled CSS is located at `public/css/app.css`.

**Key files:**
- `frontend/tailwind.config.js` - TailwindCSS configuration
- `frontend/src/input.css` - TailwindCSS input file
- `public/css/app.css` - Compiled CSS output

### Vue.js

Vue.js components are located in `frontend/src/` directory.

**Key files:**
- `frontend/src/main.js` - Vue.js entry point
- `frontend/src/App.vue` - Main Vue component
- `frontend/src/components/Counter.vue` - Interactive counter component
- `frontend/vite.config.js` - Vite configuration
- `frontend/.env` - Environment variables (including VITE_PORT)

**Port Configuration:**
Vite is configured to use port 5173 by default, but will automatically switch to the next available port (e.g., 5174) if there's a conflict. You can:
- Set a specific port in `frontend/.env`: `VITE_PORT=5173`
- Check the terminal output to see which port Vite is actually using
- Access the frontend at the port shown in the Docker logs: `docker logs hr-system_node_1`

**Vue.js Components:**

#### Counter Component
The Counter component demonstrates Vue.js reactivity with increment/decrement functionality:
- **Features**: Increment, decrement, reset buttons
- **Validation**: Prevents negative values
- **Styling**: TailwindCSS with gradient backgrounds and hover effects
- **State Management**: Tracks current count and total clicks

### Building Assets

#### Vue.js Development and Building

**Docker-based Development (Recommended):**
The Node.js container automatically runs `npm run dev` with hot reloading enabled. Simply edit files in `frontend/src/` and changes will be reflected automatically.

**Manual Commands (if needed):**
```bash
# Development mode (runs automatically in Docker)
docker exec hr-system_node_1 npm run dev

# Production build (compiles to public/js/app.js)
docker exec hr-system_node_1 npm run build

# Preview production build
docker exec hr-system_node_1 npm run preview
```

#### TailwindCSS

TailwindCSS is configured and ready to use. The compiled CSS is located at `public/css/app.css`.

**Development Workflow:**
1. Make changes to Vue.js components in `frontend/src/`
2. Changes are automatically detected and hot-reloaded via Docker
3. For production builds, run `docker exec hr-system_node_1 npm run build`

## 🧪 Testing

### Run PHP Tests (Docker)
```bash
# Run all tests
docker exec hr-system_app_1 php vendor/bin/phpunit

# Run specific test file
docker exec hr-system_app_1 php vendor/bin/phpunit tests/Controller/HomeControllerTest.php

# Run with coverage
docker exec hr-system_app_1 php vendor/bin/phpunit --coverage-html coverage/
```

### Frontend Tests
Currently, no frontend tests are configured. The frontend functionality is verified through:
- Manual testing via http://localhost:5173
- Integration with backend via http://localhost:8000

### Test Coverage
- Home page functionality
- TailwindCSS integration
- Vue.js component rendering
- Counter component functionality

### Vue.js Integration Testing

Run the Vue.js integration test script:
```bash
node test_vue_integration.js
```

This script verifies:
- Home page accessibility
- Vue.js app container presence
- JavaScript and CSS file loading
- Vue.js content compilation

## 🐳 Docker Services

The application uses Docker Compose with the following services:

- **app** - Symfony application (PHP 8.2 with built-in server on port 8000)
- **database** - PostgreSQL 15 database (port 5432)
- **node** - Node.js 18 container running Vite dev server (port 5173)

### Docker Commands

```bash
# Start all services
docker-compose up -d

# View logs
docker-compose logs -f

# Stop all services
docker-compose down

# Rebuild containers
docker-compose up -d --build

# Access application container
docker-compose exec app bash

# Access database
docker-compose exec postgres psql -U app -d app
```

## 🔧 Configuration

### Environment Variables

Copy `.env` to `.env.local` and adjust settings:

```bash
cp .env .env.local
```

Key environment variables:
- `DATABASE_URL` - Database connection string
- `APP_ENV` - Application environment (dev/prod)
- `APP_SECRET` - Application secret key

### Database

The database is automatically configured via Docker. Connection details:
- Host: localhost (or postgres container name)
- Port: 5432
- Database: app
- Username: app
- Password: app

## 🚨 Troubleshooting

### Common Docker Issues

1. **Port already in use errors**
   ```bash
   # Check what's using the port
   lsof -i :5173  # or :8000, :5432
   
   # Kill the process
   kill -9 <PID>
   
   # Remove old containers
   docker rm hr-system_node_1
   ```
   
   **Alternative**: If Vite automatically switches ports (e.g., to 5174), check the actual port in use:
   ```bash
   docker logs hr-system_node_1
   ```
   Then access the frontend at the port shown in the logs.

2. **Node container fails to start**
   - Ensure no local npm/node processes are running on port 5173
   - Remove old containers: `docker-compose down && docker-compose up -d`
   - Check logs: `docker logs hr-system_node_1`

3. **Hot reloading not working**
   - Verify Vite is configured with `host: '0.0.0.0'` and `usePolling: true`
   - Restart node container: `docker-compose restart node`
   - Check if files are being watched: `docker logs hr-system_node_1`

4. **Database connection errors**
   - Verify PostgreSQL container is running: `docker-compose ps`
   - Check database credentials in docker-compose.yml
   - Wait for database to fully start before accessing app

5. **Frontend build issues**
   - Restart node container: `docker-compose restart node`
   - Rebuild containers: `docker-compose up -d --build`
   - Check Node.js container logs for errors

### Development Tips

- Use `docker-compose logs -f app` to monitor application logs
- Access the application container with `docker-compose exec app bash`
- Database migrations: `docker exec hr-system_app_1 php bin/console doctrine:migrations:migrate`
- Clear Symfony cache: `docker exec hr-system_app_1 php bin/console cache:clear`
- Run tests: `docker exec hr-system_app_1 php vendor/bin/phpunit`
- Build frontend for production: `docker exec hr-system_node_1 npm run build`

## 📝 API Documentation

API documentation is available via API Platform:
- API docs: http://localhost:8000/api/docs
- API endpoint: http://localhost:8000/api

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Run tests: `./vendor/bin/phpunit`
5. Submit a pull request

## 📄 License

This project is licensed under the MIT License.