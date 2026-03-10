# Use the official PHP image
FROM php:8.2-cli

# Install MySQL extensions for Laravel
RUN docker-php-ext-install pdo pdo_mysql

# Set the working directory inside the container
WORKDIR /app

# Copy your project files into the container
COPY . .

# Start the Laravel server
CMD php artisan serve --host=0.0.0.0 --port=8000