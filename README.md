# JR Consumption Tracker

A comprehensive consumption tracking system for monitoring and managing utilities and resources usage.

## 📋 Overview

This organization contains all repositories related to the Consumption Tracker project - a web-based application designed to help users track, analyze, and manage their consumption data (electricity, water, gas, etc.).

## 🏗️ Project Structure

The project is divided into separate repositories for better organization and maintainability:

### Repositories

#### `consumption-api`

Backend REST API built with PHP that handles all business logic, data processing, and database operations.

- **Tech Stack**: PHP 8.3+, MySQL
- **Purpose**: Core API for consumption data management
- **Structure**:
  - `/docker` - Docker development environment
  - `/src` - Complete API application code

#### `consumption-web`

Frontend monorepo containing both the public web application and admin interface.

- **Tech Stack**: React/Next.js (monorepo setup)
- **Purpose**: User-facing applications
- **Structure**:
  - `/apps/web` - Public consumption tracking interface
  - `/apps/admin` - Administration dashboard
  - `/packages` - Shared UI components and utilities

## 🚀 Getting Started

### Prerequisites

- Docker & Docker Compose
- Git
- Node.js 18+ (for frontend)
- Composer (for backend, runs in Docker)

### Quick Start

1. **Clone the repositories**

```bash
mkdir jr-consumption-tracker
cd jr-consumption-tracker

# Clone API
git clone git@github.com:jr-consumption-tracker/consumption-api.git

# Clone Web
git clone git@github.com:jr-consumption-tracker/consumption-web.git
```

2. **Start the API**

```bash
cd consumption-api
cp src/.env.example src/.env
cd docker
docker-compose up -d
docker-compose exec php composer install
```

3. **Start the Web Application**

```bash
cd consumption-web
npm install
npm run dev
```

## 🔧 Development

Each repository contains its own detailed README with specific setup instructions, development guidelines, and contribution rules.

### API Development

- API runs on `http://localhost:8080`
- MySQL available on `localhost:3306`
- See `consumption-api/README.md` for details

### Web Development

- Web app runs on `http://localhost:3000`
- Admin runs on `http://localhost:3001`
- See `consumption-web/README.md` for details

## 📚 Documentation

- [API Documentation](https://github.com/jr-consumption-tracker/consumption-api#readme)
- [Web Documentation](https://github.com/jr-consumption-tracker/consumption-web#readme)

## 🤝 Contributing

This is a personal project, but suggestions and feedback are welcome! Please open an issue in the relevant repository.

## 📄 License

This project is private and proprietary.

## 👤 Author

**Jan Ribka**

---

_Last updated: January 2026_
