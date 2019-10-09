<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de usuarios</title>
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
</head>
<body>
    <div class="container">
        <div class="row">
            <div class="col">
                <form id="form-busca-empresa" class="mt-3">
                    <div class="form-group">
                        <label for="f-rucdni">Código de cliente</label>
                        <input type="text" class="form-control" id="f-rucdni" placeholder="Ingresa el RUC o DNI de tu negocio">
                    </div>
                    <button class="btn btn-primary">Buscar</button>
                </form>
                <form id="form-registra-cliente" class="mt-3">
                    <input type="hidden" id="f-codigo">
                    <p class="text-secondary">A continuación, confirma tus datos</p>
                    <div class="form-group">
                        <label for="f-ncomercial">Nombre comercial</label>
                        <input type="text" class="form-control" id="f-ncomercial">
                    </div>
                    <div class="form-group">
                        <label for="f-rsocial">Razón social</label>
                        <input type="text" class="form-control" id="f-rsocial">
                    </div>
                    <div class="form-group">
                        <label for="f-email">e-mail</label>
                        <input type="text" class="form-control" id="f-email">
                    </div>
                    <div class="form-group">
                        <label for="f-telefono">Teléfono</label>
                        <input type="text" class="form-control" id="f-telefono">
                    </div>
                    <div class="form-group">
                        <label for="f-password">Clave</label>
                        <input type="password" class="form-control" id="f-password">
                    </div>
                    <div class="form-group">
                        <label for="f-rpassword">Repite clave</label>
                        <input type="password" class="form-control" id="f-rpassword">
                    </div>
                    <button class="btn btn-primary">Registrar</button>
                </form>
            </div>
        </div>
    </div>
</body>
<script>
    CargarDatosCliente = async (event) => {
        event.preventDefault();
        let args = {
            rucdni: document.getElementById('f-rucdni').value
        };
        let result = await $.ajax({
            url: '{{ url("api/app-life/busca") }}',
            type: 'POST',
            data: args
        });
        if(result.status) {
            $('#form-registra-cliente').fadeIn(150);
            let data = result.data.cliente;
            document.getElementById('f-codigo').value = args.rucdni;
            document.getElementById('f-ncomercial').value = data.ncomercial;
            document.getElementById('f-rsocial').value = data.rsocial;
            document.getElementById('f-email').value = data.email;
            document.getElementById('f-telefono').value = data.telefono;
        }
    }

    RegistraDatosCliente = async (event) => {
        event.preventDefault();
        let password = document.getElementById('f-password').value;
        let rpassword = document.getElementById('f-rpassword').value;
        if(password == rpassword) {
            let result = await $.ajax({
                url: '{{ url("api/app-life/registro") }}',
                type: 'POST',
                data: {
                    codigo: document.getElementById('f-codigo').value,
                    ncomercial: document.getElementById('f-ncomercial').value,
                    rsocial: document.getElementById('f-rsocial').value,
                    email: document.getElementById('f-email').value,
                    telefono: document.getElementById('f-telefono').value,
                    clave: document.getElementById('f-password').value
                }
            });
            if(result.status) {
                alert('Cliente registrado!');
            }
            else alert(result.message);
        }
        else alert('Las claves ingresadas deben coincidir');
    }
    //
    $('#form-registra-cliente').hide();
    $('#form-busca-empresa').on('submit', CargarDatosCliente);
    $('#form-registra-cliente').on('submit', RegistraDatosCliente);
</script>
</html>