This is a platform developed in Laravel 9 to execute user studies in a simulated email client to assess the effectiveness of warnings against phishing attacks.

The <b>emails</b> are visible in the <b>emails_screens.pdf</b> file in the root folder of this repository. The emails' HTML files are located in the <b>public/assets/email_files<b> folder.

## Requirements

- PHP >= 8.0.2
- Composer

## Installation

- Clone this repo on your Web Server (or in a local web server, e.g., using XAMPP)
- Create a MySQL (or MariaDB) database and a user with the right privileges:
    - CREATE SCHEMA db_name;
    - CREATE USER 'user_db'@'localhost' IDENTIFIED BY 'password';
    - GRANT PRIVILEGE ON db_name.* TO 'user_db'@'localhost';
    - FLUSH PRIVILEGES;
- Copy the .env.example file in .env
- Open the .env file and edit these values:
    - APP_URL with the URL of the Web Server
    - DB_HOST with the IP address of the MySQL server (if different from the Web Server, otherwise leave 127.0.0.1)
    - DB_PORT with the port of the MySQL server (the default is 3306)
    - DB_DATABASE with the name of the database created in the previous step
    - DB_USERNAME with the username of the database created in the previous step
    - DB_PASSWORD with the password of the database created in the previous step
    - QUEUE_CONNECTION with "database"
    - PROLIFIC_STUDY_CODE with the code of the study in Prolific
    - (only if OpenAI is used) OPENAI_API_KEY with the api key of OpenAI
- Execute the following commands within the root folder of this repo
    - `composer install --optimize-autoloader --no-dev`
    - `npm install`
    - `php artisan key:generate`
    - `php artisan migrate:fresh`
    - `php artisan config:cache` (optional)
    - `php artisan route:cache` (optional)
    - `php artisan view:cache` (optional)
    - `php artisan db:seed`

For LLM-generated training, run a job dispatcher in the background:

`nohup php artisan queue:work --timeout 6000 > storage/logs/jobs.log 2>storage/logs/jobs.err < /dev/null &`

The command `ps -xw | grep "php artisan"` can be used to verify the background job is running.
