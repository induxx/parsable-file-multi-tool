# Installation Guide

This guide will walk you through installing and setting up the Parsable File Multi-Tool on your system.

## Prerequisites

Before installing the tool, ensure you have the following prerequisites installed on your system:

### Required Software

- **Docker** (version 20.10 or higher)
- **Docker Compose** (version 2.0 or higher)
- **Git** (for cloning the repository)

### System Requirements

- **Operating System**: Linux, macOS, or Windows with WSL2
- **Memory**: Minimum 4GB RAM (8GB recommended for large datasets)
- **Storage**: At least 2GB free disk space
- **Network**: Internet connection for downloading dependencies

## Installation Methods

### Method 1: Docker Installation (Recommended)

The Docker installation method provides a consistent environment across all platforms and is the recommended approach.

#### Step 1: Clone the Repository

```bash
git clone <repository-url>
cd parsable-file-multi-tool
```

#### Step 2: Build Docker Environment

```bash
docker-compose build
```

This command will:
- Build the PHP 8.1 FPM container with all required extensions
- Install Composer dependencies
- Set up Python environment with pandas for advanced transformations
- Configure Redis for caching

#### Step 3: Install PHP Dependencies

```bash
bin/docker/composer install
```

#### Step 4: Verify Installation

Test that the installation was successful:

```bash
bin/docker/console --help
```

You should see the command-line interface help output.

### Method 2: Native PHP Installation

If you prefer to run the tool directly on your system without Docker:

#### Step 1: Install PHP and Extensions

**Ubuntu/Debian:**
```bash
sudo apt update
sudo apt install php8.1 php8.1-cli php8.1-json php8.1-iconv \
                 php8.1-xml php8.1-curl php8.1-zip php8.1-simplexml \
                 composer
```

**macOS (using Homebrew):**
```bash
brew install php@8.1 composer
```

**Windows:**
Download PHP 8.1+ from [php.net](https://www.php.net/downloads) and install Composer from [getcomposer.org](https://getcomposer.org/).

#### Step 2: Clone and Install Dependencies

```bash
git clone <repository-url>
cd parsable-file-multi-tool
composer install
```

#### Step 3: Verify Installation

```bash
php bin/console --help
```

## Post-Installation Setup

### Directory Permissions

Ensure the following directories are writable:

```bash
chmod -R 755 examples/
chmod -R 755 var/
mkdir -p var/cache var/logs
chmod -R 777 var/
```

### Optional: Python Environment Setup

For advanced transformations using Python pandas:

```bash
# Install Python 3.8+ and pip
python3 -m venv venv
source venv/bin/activate  # On Windows: venv\Scripts\activate
pip install pandas
```

### Optional: Redis Setup

For improved performance with large datasets:

**Docker:**
```bash
docker run -d --name redis -p 6379:6379 redis:alpine
```

**Native installation:**
```bash
# Ubuntu/Debian
sudo apt install redis-server

# macOS
brew install redis
brew services start redis
```

## Configuration

### Environment Variables

Create a `.env` file in the project root for environment-specific settings:

```bash
# Database connections (if needed)
DATABASE_URL=mysql://user:password@localhost/database

# Redis configuration
REDIS_URL=redis://localhost:6379

# API endpoints
AKENEO_BASE_URL=https://your-akeneo-instance.com
```

### File Permissions

Ensure proper permissions for execution:

```bash
chmod +x bin/console
chmod +x bin/docker/*
```

## Verification

### Basic Functionality Test

Run a simple transformation to verify everything works:

```bash
# Using Docker
bin/docker/console transformation --file examples/app-example/transformation_in_steps_main.yaml --source examples --workpath /tmp/test-output

# Using native PHP
php bin/console transformation --file examples/app-example/transformation_in_steps_main.yaml --source examples --workpath /tmp/test-output
```

### Performance Test

Test with a larger dataset:

```bash
bin/docker/console transformation --file examples/app-example/transformation_in_steps_main.yaml --source examples --workpath /tmp/test-output --try 1000
```

## Troubleshooting

### Common Issues

#### Docker Build Fails

**Problem**: Docker build fails with permission errors
**Solution**: 
```bash
sudo usermod -aG docker $USER
# Log out and back in, then retry
```

#### Memory Issues

**Problem**: PHP runs out of memory during large transformations
**Solution**: Increase memory limit in Docker or PHP configuration
```bash
# For Docker usage
bin/docker/php -d memory_limit=8G bin/console transformation ...

# For native PHP
php -d memory_limit=8G bin/console transformation ...
```

#### File Permission Errors

**Problem**: Cannot write to output directories
**Solution**: 
```bash
sudo chown -R $USER:$USER .
chmod -R 755 examples/ var/
```

#### Missing PHP Extensions

**Problem**: Required PHP extensions not found
**Solution**: Install missing extensions based on error messages
```bash
# Example for Ubuntu/Debian
sudo apt install php8.1-<extension-name>
```

### Getting Help

If you encounter issues not covered here:

1. Check the [Troubleshooting Guide](../user-guide/troubleshooting.md)
2. Review the [FAQ](../user-guide/faq.md)
3. Search existing issues in the project repository
4. Create a new issue with detailed error information

## Next Steps

After successful installation:

1. Read the [Quick Start Tutorial](quick-start.md)
2. Explore [Configuration Guide](configuration.md)
3. Try the [Basic Examples](../examples/)
4. Review the [User Guide](../user-guide/) for detailed usage instructions

## Updating

### Docker Installation

```bash
git pull origin main
docker-compose build
bin/docker/composer install
```

### Native Installation

```bash
git pull origin main
composer install
```

## Uninstallation

### Docker Installation

```bash
docker-compose down
docker rmi parsable-file-multi-tool_fpm
# Remove project directory
rm -rf parsable-file-multi-tool/
```

### Native Installation

```bash
# Remove project directory
rm -rf parsable-file-multi-tool/
```