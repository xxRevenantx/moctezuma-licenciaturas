git clone https://github.com/xxRevenantx/moctezuma-licenciaturas && cd moctezuma-licenciaturas && composer install && npm install && code .


php artisan key:generate && php artisan storage:link && php artisan migrate:fresh --seed && npm run dev
