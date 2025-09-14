1. Clone project
2. Run project with command:
>
    docker compose up

3. Before testing the application, run the seeders with commands:
>
    docker exec -it laravel_app bash
    # inside container run 
    php artisan migrate
    php artisan db:seed

4. Access the website at http://localhost:3000