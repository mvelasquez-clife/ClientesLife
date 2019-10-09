<!DOCTYPE html>
<html>
	<head>
		<title>Ingresar a CLife</title>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
		<link rel="shortcut icon" href="{{ asset('lifeapp.ico')}}" />
		<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
		<link rel="stylesheet" type="text/css" href="https://use.fontawesome.com/releases/v5.8.2/css/all.css">
		<link rel="stylesheet" type="text/css" href="{{ asset('css/login.css') }}">
		<style type="text/css">
			.img-logo{display:block;margin:0 auto 8px;width:50%;}
		</style>
	</head>
	<body>
		<div class="container">
			<div class="row">
				<div class="col-sm-9 col-md-7 col-lg-5 mx-auto">
					<div class="card card-signin my-5">
						<div class="card-body">
							<img class="img-logo" src="{{ asset('images/clife-logo.svg') }}">
							<h5 class="card-title text-center">Bienvenido a Life App</h5>
							<form id="form-login" class="form-signin">
								<div class="form-label-group">
									<input type="number" id="form-usuario" class="form-control" placeholder="Email address" required autofocus>
									<label for="form-usuario">DNI/RUC</label>
								</div>

								<div class="form-label-group">
									<input type="password" id="form-clave" class="form-control" placeholder="Password" required>
									<label for="form-clave">Contraseña</label>
								</div>

								<div class="custom-control custom-checkbox mb-3">
									<input type="checkbox" class="custom-control-input" id="customCheck1">
									<label class="custom-control-label" for="customCheck1">Recordar contraseña</label>
								</div>
								<button class="btn btn-lg btn-primary btn-block text-uppercase" type="submit">Ingresar</button>
								<!--hr class="my-4">
								<button class="btn btn-lg btn-google btn-block text-uppercase" type="submit"><i class="fab fa-google mr-2"></i> Ingresar con Google</button>
								<button class="btn btn-lg btn-facebook btn-block text-uppercase" type="submit"><i class="fab fa-facebook-f mr-2"></i> Ingresar Facebook</button-->
							</form>
						</div>
					</div>
				</div>
			</div>
		</div>
		<!-- scripts -->
		<script src="https://code.jquery.com/jquery-3.4.1.min.js" integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo=" crossorigin="anonymous"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
		<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
		<script type="text/javascript" src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.bundle.min.js"></script>
		<script type="text/javascript">
			var usrToken;
			AutenticarUsuario = async (event) => {
				event.preventDefault();
				let result;
				try {
					result = await $.ajax({
						url: '{{ url("api/app-life/login") }}',
						method: 'post',
						data: {
							co_cliente: document.getElementById('form-usuario').value,
							password: document.getElementById('form-clave').value,
							co_empresa: 11
						},
						dataType: 'json'
					});
				}
				catch(error) {
					console.log(error);
					return;
				}
				if(result.status) {
					localStorage.setItem('token', result.data.token);
					location.reload();
				}
			}
			ValidarToken = async () => {
				let result;
				try {
					result = await $.ajax({
						url: '{{ url("api/app-life/intranet/validar-token") }}',
						method: 'post',
						data: {
							token: usrToken
						},
						dataType: 'json'
					});
				}
				catch(error) {
					$('button').removeClass('btn-success').addClass('btn-primary').html('Token expiró. Ingrese nuevamente');
					console.log(error);
				}
				if(result.state) {
					location.href = '{{ url("intranet") }}';
				}
				else {
					$('button').removeClass('btn-success').addClass('btn-primary').html('Ingresar');
				}
			}

			//localStorage.removeItem('token');
			console.log('%c*** Iniciando CLife App ***', 'background:#009688;color:#ffffff;font-size:13px;');
			if(localStorage.getItem('token')) {
				console.log('%c! Token identificado. Comprobando su validez', 'background:#f5f5f5;color:#1976d2;');
				$('button').removeClass('btn-primary').addClass('btn-success').html('Validando su identidad...');
				usrToken = localStorage.getItem('token');
				ValidarToken();
			}
			else {
				console.log('%c! No se encontró un token de sesión', 'background:#f5f5f5;color:#f44336;');
			}
			$('#form-login').on('submit', AutenticarUsuario);
		</script>
	</body>
</html>