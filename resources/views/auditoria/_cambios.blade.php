@php $v = fn ($x) => is_array($x) ? json_encode($x, JSON_UNESCAPED_UNICODE) : ($x === null ? '∅' : (is_bool($x) ? ($x ? 'sí' : 'no') : $x)); @endphp
@if($registro->accion === 'modificacion')
    @foreach($registro->campos as $campo)
        <div class="small"><code>{{ $campo }}</code>:
            <span class="text-danger text-decoration-line-through">{{ \Illuminate\Support\Str::limit($v($registro->valores_anteriores[$campo] ?? null), 60) }}</span>
            → <strong class="text-success">{{ \Illuminate\Support\Str::limit($v($registro->valores_nuevos[$campo] ?? null), 60) }}</strong>
        </div>
    @endforeach
@elseif($registro->accion === 'alta')
    <details class="small"><summary>{{ count($registro->valores_nuevos ?? []) }} campos registrados</summary>
        @foreach($registro->valores_nuevos ?? [] as $campo => $valor)
            <div><code>{{ $campo }}</code>: {{ \Illuminate\Support\Str::limit($v($valor), 80) }}</div>
        @endforeach
    </details>
@elseif($registro->accion === 'baja')
    <span class="small text-muted">Baja lógica del registro (se conservan los valores originales).</span>
@else
    <span class="small text-muted">Registro restaurado.</span>
@endif
