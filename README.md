# Reservation System (Paluto)

PHP + Google Sheets + PHPMailer based reservation system with QR code confirmation and check-in scanner.

---

## 🚀 Local Setup

1. Clone repo:
   ```bash
   git clone https://github.com/<your-username>/<your-repo>.git
   cd <your-repo>

2. Install dependencies:

bash
Copy code
composer install

3. Copy .env.example → .env and set values:

ini
Copy code
APP_TZ=Asia/Manila
GOOGLE_SHEET_ID=your-sheet-id
GOOGLE_SHEET_NAME=UNLI_PALUTO
GOOGLE_APPLICATION_CREDENTIALS=config/credentials.json

SMTP_HOST=smtp.gmail.com
SMTP_PORT=587
SMTP_USER=your@gmail.com
SMTP_PASS=your-app-password
SMTP_FROM_EMAIL=your@gmail.com
SMTP_FROM_NAME=PALUTO PH

BASE_URL=http://localhost/reservation/

4. Place Google service account JSON at config/credentials.json.

5. Run locally:

bash
Copy code
php -S localhost:8000 -t .


--🧑‍💻 Git & GitHub--
    bash
    Copy code
    # first time
    git init
    git remote add origin https://github.com/<your-username>/<your-repo>.git

    # save changes
    git add .
    git commit -m "update"
    git push origin main

    # pull updates
    git pull origin main


--☁️ Deploy to Render--

1. Add Dockerfile:

    dockerfile
    Copy code
    FROM php:8.2-apache
    RUN apt-get update && apt-get install -y \
        git unzip libzip-dev libpng-dev libonig-dev libxml2-dev \
    && docker-php-ext-install zip \
    && a2enmod rewrite headers \
    && rm -rf /var/lib/apt/lists/*
    COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
    WORKDIR /var/www/html
    COPY . .
    RUN composer install --no-dev --prefer-dist
    ENV APACHE_PORT=8080
    RUN sed -ri -e 's!^Listen 80$!Listen ${APACHE_PORT}!g' /etc/apache2/ports.conf \
        && sed -ri -e 's!VirtualHost \*:80!VirtualHost \*:${APACHE_PORT}!g' /etc/apache2/sites-available/000-default.conf
    EXPOSE 8080
    CMD ["apache2-foreground"]

2. Push code:

    bash
    Copy code
    git add .
    git commit -m "deploy"
    git push origin main

3. In Render:

    New Web Service → Connect GitHub repo
    Environment: Docker
    Branch: main
    Port: 8080
    Add environment variables from .env
    Upload secret file config/credentials.json

4. Deploy → done 🎉