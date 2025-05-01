<div>



    <flux:navlist >
        <flux:navlist.group :heading="__('Licenciaturas')" expandable >

        @foreach ($licenciaturas as $licenciatura)
            <flux:navlist.item icon="rectangle-stack" :href="route('admin.generaciones.index')"
            :current="request()->routeIs('admin.generaciones.index')"
            wire:navigate>{{$licenciatura->nombre_corto}}</flux:navlist.item>
        @endforeach

        </flux:navlist.group>
    </flux:navlist>
</div>
