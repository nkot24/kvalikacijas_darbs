1.git clone https://github.com/nkot24/order_mangement.git
2.cd order_mangement, cp.env.example .env
3.Veic nepieciešamās izmaiņas .env failā precīzāk veicot izmaiņas šajā db sadaļā: DB_CONNECTION=mysql
                                                                                  DB_HOST=127.0.0.1
                                                                                  DB_PORT=3307
                                                                                  DB_DATABASE=order_management
                                                                                  DB_USERNAME=root
                                                                                  DB_PASSWORD=
4.composer install
5.npm install, npm run dev
6.php artisan key:generate
7.php artisan storage:link
8.php artisan migrate --seed ja DB jau ir tad php artisa db:seed
9.php artisan serve
