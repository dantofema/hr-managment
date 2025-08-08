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

2. **Start the application with Docker**
   ```bash
   docker-compose up -d
   ```

3. **Install PHP dependencies**
   ```bash
   composer install
   ```

4. **Install Node.js dependencies (for frontend development)**
   ```bash
   cd frontend
   npm install
   cd ..
   ```

5. **Access the application**
   - Main application: http://localhost:8000
   - Home page: http://localhost:8000/

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

**Vue.js Components:**

#### Counter Component
The Counter component demonstrates Vue.js reactivity with increment/decrement functionality:
- **Features**: Increment, decrement, reset buttons
- **Validation**: Prevents negative values
- **Styling**: TailwindCSS with gradient backgrounds and hover effects
- **State Management**: Tracks current count and total clicks

### Building Assets

#### Vue.js Development and Building

For Vue.js development with automatic rebuilding:

```bash
cd frontend

# Development mode (with hot reload)
npm run dev

# Production build (compiles to public/js/app.js)
npm run build

# Preview production build
npm run preview
```

#### TailwindCSS

TailwindCSS is configured and ready to use. The compiled CSS is located at `public/css/app.css`.

**Development Workflow:**
1. Make changes to Vue.js components in `frontend/src/`
2. Run `npm run build` to compile assets
3. Refresh the browser to see changes

## 🧪 Testing

### Run PHP Tests
```bash
# Run all tests
./vendor/bin/phpunit

# Run specific test file
./vendor/bin/phpunit tests/Controller/HomeControllerTest.php

# Run with coverage
./vendor/bin/phpunit --coverage-html coverage/
```

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

- **app** - Symfony application (PHP-FPM)
- **nginx** - Web server
- **postgres** - PostgreSQL database
- **node** - Node.js for frontend builds (optional)

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

### Common Issues

1. **Styles not showing**
   - Verify `public/css/app.css` exists and is accessible
   - Check browser console for 404 errors
   - Ensure TailwindCSS classes are properly compiled

2. **Docker permission issues**
   - Run `sudo chown -R $USER:$USER .` to fix file permissions
   - Ensure Docker daemon is running

3. **Database connection errors**
   - Verify PostgreSQL container is running: `docker-compose ps`
   - Check database credentials in `.env.local`

4. **Frontend build issues**
   - Clear node_modules: `rm -rf frontend/node_modules && cd frontend && npm install`
   - Check Node.js version compatibility

### Development Tips

- Use `docker-compose logs -f app` to monitor application logs
- Access the application container with `docker-compose exec app bash`
- Database migrations: `php bin/console doctrine:migrations:migrate`
- Clear Symfony cache: `php bin/console cache:clear`

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