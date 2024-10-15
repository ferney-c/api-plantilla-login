@if($errors->any())
    <div style="color: red;">
        @foreach($errors->all() as $error)
            <p>{{ $error }}</p>
        @endforeach
    </div>
@endif

<form method="POST">
    @csrf
    <input type="hidden" name="id" value="{{ $user[0]->id }}">
    <input type="password" name="password" placeholder="Nueva Contraseña">
    <br><br>
    <input type="password" name="password_confirmation" placeholder="Confirmar Contraseña">
    <br><br>
    <button type="submit">Guardar</button>
</form>