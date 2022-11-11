## Requisiti

- PHP >= 8.0.2
- Composer

## Installazione

- Copiare il contenuto della cartella .zip sul Web Server
- Creare un database MySQL (o MariaDB) e un utente con i privilegi sul database creato:
    - CREATE SCHEMA nome_database;
    - CREATE USER 'utente_db'@'localhost' IDENTIFIED BY 'password';
    - GRANT PRIVILEGE ON nome_database.* TO 'utente_db'@'localhost';
    - FLUSH PRIVILEGES;
- Copiare (o rinominare) il file .env.example in .env
- Aprire il file .env e modificare i seguenti valori:
    - APP_URL con l'url del Web Server
    - DB_HOST con l'ip del server MySQL (se diverso dal Web Server, altrimenti lasciare 127.0.0.1)
    - DB_PORT con la porta del server MySQL (di default è la 3306)
    - DB_DATABASE con il nome del database appena creato
    - DB_USERNAME con il nome dell'utente creato al punto precedente
    - DB_PASSWORD con la password dell'utente creato al punto precedente
- Eseguire i seguenti comandi all'interno della cartella principale
    - composer install --optimize-autoloader --no-dev
    - php artisan key:generate
    - php artisan migrate:fresh
    - php artisan config:cache (facoltativo)
    - php artisan route:cache (facoltativo)
    - php artisan view:cache (facoltativo)

