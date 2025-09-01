<?php

namespace App\Livewire\Admin\Template;

use Livewire\Component;
use Illuminate\Support\Str;

class ConstanciaTemplate extends Component
{

   public ?Template $template = null;
    public string $titulo = '';
    public string $slug = '';
    public string $contenido_html = '';
    public array $variables = [];

    public function mount(?int $id = null)
    {
        $this->template = $id ? Template::findOrFail($id) : null;
        $this->titulo   = $this->template->titulo ?? 'Oficio';
        $this->slug     = $this->template->slug ?? Str::slug($this->titulo);
        $this->contenido_html = $this->template->contenido_html ?? <<<HTML
            <h2 style="text-align:center">OFICIO</h2>
            <p><strong>Folio:</strong> {{folio}}</p>
            <p><strong>Fecha:</strong> {{fecha}}</p>
            <p>Nombre: {{nombre}}<br>Cargo: {{cargo}}</p>
            <p><strong>Asunto:</strong> {{asunto}}</p>
            <p>{{cuerpo}}</p>
            <p>Atentamente,<br>{{remitente}}</p>
        HTML;

        $this->variables = $this->template->variables_defecto ?? [
            'folio' => 'CUM-001',
            'fecha' => now()->format('d/m/Y'),
            'nombre' => '',
            'cargo' => '',
            'asunto' => '',
            'cuerpo' => '',
            'remitente' => 'Centro Universitario Moctezuma',
        ];
    }

    public function guardar()
    {
        $data = $this->validate([
            'titulo' => 'required|min:3',
            'slug'   => 'required|min:3|unique:templates,slug,' . ($this->template->id ?? 'null'),
            'contenido_html' => 'required|min:10',
        ]);

        $tpl = $this->template ?? new Template();
        $tpl->fill([
            'titulo' => $this->titulo,
            'slug'   => $this->slug,
            'contenido_html' => $this->contenido_html,
            'variables_defecto' => $this->variables,
        ])->save();

        $this->template = $tpl;
        $this->dispatch('guardado');
    }
    public function render()
    {
        return view('livewire.admin.template.constancia-template');
    }
}
