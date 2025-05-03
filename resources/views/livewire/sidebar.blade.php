<div>
    <flux:navlist>
        <flux:navlist.group :heading="__('Licenciaturas')" expandable>
            @foreach ($licenciaturas as $licenciatura)
                <flux:navlist.item
                    icon="rectangle-stack"
                    :href="route('licenciaturas.seleccionar-modalidad', ['slug' => $licenciatura->slug])"
                    :current="request()->segment(2) === $licenciatura->slug"
                    wire:navigate>
                    {{ $licenciatura->nombre_corto }}
                </flux:navlist.item>
            @endforeach
        </flux:navlist.group>
    </flux:navlist>
</div>
