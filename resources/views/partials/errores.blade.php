@if($errors->any())
    <div class="alert alert-danger fade-in" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2"></i> Revise los datos ingresados:
        <ul class="mb-0 mt-1">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
