# Environment Setup Guide

## Quick Start

1. Copy environment files:
```bash
cp .env.example .env
cp frontend/.env.example frontend/.env
```

2. Generate APP_SECRET:
```bash
openssl rand -hex 32
```

3. Update .env with the generated secret

4. Start services:
```bash
docker-compose up -d
```

## Environment Variables

### Backend (.env)
- `APP_SECRET`: Symfony application secret
- `DATABASE_URL`: PostgreSQL connection string
- `CORS_ALLOW_ORIGIN`: CORS configuration for frontend
- `JWT_*`: JWT authentication settings

### Frontend (frontend/.env)
- `VITE_API_URL`: Backend API endpoint
- `VITE_PORT`: Development server port

## Verification

Test connectivity:
```bash
curl -X POST http://localhost:8000/api/login
```