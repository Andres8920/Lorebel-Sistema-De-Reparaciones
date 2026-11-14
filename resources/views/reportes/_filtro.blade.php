<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-3"><label class="form-label small">Desde</label><input type="date" name="desde" value="{{ $desde->toDateString() }}" class="form-control"></div>
            <div class="col-md-3"><label class="form-label small">Hasta</label><input type="date" name="hasta" value="{{ $hasta->toDateString() }}" class="form-control"></div>
            <div class="col-md-6 d-flex gap-2">
                <button class="btn btn-primary"><i class="bi bi-funnel me-1"></i> Aplicar</button>
                <a href="?desde={{ now()->startOfMonth()->toDateString() }}&hasta={{ now()->toDateString() }}" class="btn btn-outline-secondary">Este mes</a>
                <a href="?desde={{ now()->subDays(6)->toDateString() }}&hasta={{ now()->toDateString() }}" class="btn btn-outline-secondary">7 días</a>
                <button type="button" onclick="window.print()" class="btn btn-outline-secondary ms-auto"><i class="bi bi-printer"></i></button>
            </div>
        </form>
    </div>
</div>
