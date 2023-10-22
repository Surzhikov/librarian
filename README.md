### Librarian

To start project:

1) Copy `.env.example` to `.env` and fill DB_HOST, DB_DATABASE and DB_USERNAME
2) Build project `docker compose --env-file .env up --detach` (wait few minutes)
3) Open PHPMyAdmin on localhost:8888 (login and password in .env)
4) Create row in `sites` table 
5) Go inside container (`docker exec -it librarian_app sh`)
6) In terminal run `php artisan app:explore-site 1` to explore site with ID = 1

