<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 18px; }
        html, body { margin: 0; padding: 0; width: 100%; height: 100%; }
        .page { width: 100%; height: 100%; text-align: center; }
        img { max-width: 100%; max-height: 96%; object-fit: contain; }
    </style>
</head>
<body>
    <div class="page"><img src="{{ $dataUri }}" alt="Documento convertido a PDF"></div>
</body>
</html>
