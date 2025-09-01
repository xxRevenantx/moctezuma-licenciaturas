{{-- resources/views/livewire/plantillas/editar-grapes.blade.php --}}
<div class="space-y-6">
    <div class="rounded-2xl border p-4 grid gap-4 sm:grid-cols-2">
        <div>
            <label class="block text-sm font-medium">Título</label>
            <input class="w-full rounded border p-2" wire:model.live="titulo">
        </div>
        <div>
            <label class="block text-sm font-medium">Slug</label>
            <input class="w-full rounded border p-2" wire:model.live="slug">
        </div>
    </div>

    <div class="rounded-2xl border">
        <div class="flex items-center gap-2 p-3 border-b">
            <span class="text-sm">Variables:</span>
                @foreach (array_keys($variables) as $k)
                <button type="button"
                        class="text-xs border rounded px-2 py-1"
                        data-var="{!! '{{'.$k.'}}' !!}"
                        x-on:click="$dispatch('insert-var', $el.dataset.var)">
                    @{{ $k }}
                </button>
                @endforeach

            <button type="button" class="ml-auto text-xs border rounded px-2 py-1"
                x-data x-on:click="$dispatch('grapes-gethtml')">Guardar HTML</button>
        </div>

        <div x-data x-init="initGrapes()">
            <link rel="stylesheet" href="https://unpkg.com/grapesjs/dist/css/grapes.min.css">
            <div id="gjs" style="min-height:520px; border-top:1px solid #eee"></div>

            <script src="https://unpkg.com/grapesjs"></script>
            <script>
            function initGrapes() {
                const editor = grapesjs.init({
                    container: '#gjs',
                    fromElement: false,
                    height: '520px',
                    storageManager: false,
                    plugins: [],
                    canvas: { styles: [] },
                });

                editor.setComponents(@js($contenido_html));

                // Escuchar inserción de variables
                window.addEventListener('grapes-insert', e => {
                    editor.runCommand('core:component-exit'); // asegurar foco
                    const sel = editor.getSelected();
                    const ins = e.detail || '';
                    if (sel) sel.append(ins);
                    else editor.getWrapper().append(`<span>${ins}</span>`);
                });

                // Guardar HTML hacia Livewire
                window.addEventListener('grapes-gethtml', () => {
                    const html = editor.getHtml() + `<style>${editor.getCss()}</style>`;
                    $wire.set('contenido_html', html);
                });

                // Botón de guardar cada 2s (auto-sync simple)
                setInterval(() => {
                    const html = editor.getHtml() + `<style>${editor.getCss()}</style>`;
                    $wire.set('contenido_html', html);
                }, 2000);
            }
            </script>
        </div>
    </div>

    <div class="rounded-2xl border p-4">
        <div class="grid sm:grid-cols-3 gap-3">
            @foreach($variables as $k => $v)
                <div class="flex items-center gap-2">
                  
                  
                </div>
            @endforeach
        </div>

        <div class="mt-4 flex gap-2">
            <button class="px-4 py-2 rounded-xl bg-blue-600 text-white" wire:click="guardar">Guardar plantilla</button>
            {{-- <a class="px-4 py-2 rounded-xl border" href="{{ route('oficios.preview', ['slug'=>$slug]) }}" target="_blank">Vista previa</a> --}}
            {{-- <a class="px-4 py-2 rounded-xl border" href="{{ route('oficios.pdf', ['slug'=>$slug]) }}">Descargar PDF</a> --}}
        </div>
    </div>
</div>
