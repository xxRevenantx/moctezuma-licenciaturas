git clone https://github.com/xxRevenantx/moctezuma-licenciaturas && cd moctezuma-licenciaturas && composer install && npm install && code .


php artisan key:generate && php artisan storage:link && php artisan migrate:fresh --seed && npm run dev


Evaluar que al momento de asignar generaciones no se repitan



php artisan make:livewire Admin.Cuatrimestre.crear-cuatrimestre
php artisan make:livewire Admin.Cuatrimestre.editar-cuatrimestre
php artisan make:livewire Admin.Cuatrimestre.mostrar-cuatrimestres


php artisan make:export PeriodoExport --model=Periodo
