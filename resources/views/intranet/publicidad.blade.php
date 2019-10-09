<!DOCTYPE html>
<html>
	<head>
		@include('common.styles')
		<title>Bienvenido a CLife</title>
		<style type="text/css">
			#img-preview > img {
				max-height: 100%;
				max-width: 100%;
			}
			.img-preview-info {
				position: absolute;
				bottom: 8px;
				left: 24px;
			}
			.btn-docker {
				bottom: 0;
				padding: 8px 32px;
				position: fixed;
				right: 0;
			}
			.circle-btn{
				border-radius: 16px;
				display: inline-block;
				height: 32px;
				position: relative;
				width: 32px;
			}
			.circle-btn>div{
				background-color: transparent;
				border-radius: 16px;
				display: block;
				height: 32px;
				position: absolute;
				transition: background 150ms ease-in;
				width: 32px;
			}
			.circle-btn:hover{
				box-shadow: 0 0 2px 1px #b0b0b0;
			}
			.circle-btn:hover>div{
				background-color: rgba(0,0,0,.15);
			}
			.circle-btn:active{
				top: 1px;
			}
			/*---*/
			.circle-blue{
				background-color: #3f51b5;
			}
		</style>
	</head>
	<body>
		@include('common.header')
		<div class="container">
			<div class="row">
				<div class="col">
					<table id="tabla-mensajes" class="table table-striped table-hover">
						<thead>
							<tr>
								<th width="1%">#</th>
								<th width="15%">Nombre</th>
								<th>Descripción</th>
								<th width="5%">Fecha</th>
								<th width="10%">Contenido</th>
								<th width="5%"></th>
							</tr>
						</thead>
						<tbody></tbody>
					</table>
				</div>
			</div>
		</div>
		<div class="btn-docker">
			<a href="#" class="circle-btn circle-blue" data-toggle="modal" data-target="#modal-formulario" data-modo="I"><div></div></a>
		</div>
		<!-- -->
		<div id="modal-logout" class="modal fade bd-example-modal-sm" tabindex="-1" role="dialog">
			<div class="modal-dialog modal-sm" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title">Cerrar sesión</h5>
						<button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
							<span aria-hidden="true">&times;</span>
						</button>
					</div>
					<div class="modal-body">
						<p>¿Desea cerrar su sesión? Tendrá que ingresar sus credenciales nuevamente la próxima vez</p>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-light" data-dismiss="modal">Cancelar</button>
						<button type="button" class="btn btn-danger">Cerrar sesión</button>
					</div>
				</div>
			</div>
		</div>
		<div id="modal-formulario" class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog">
			<div class="modal-dialog modal-lg" role="document">
				<form class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title">Nueva notificación</h5>
						<button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
							<span aria-hidden="true">&times;</span>
						</button>
					</div>
					<div class="modal-body">
						<input type="hidden" id="rg-modo" value="I">
						<div class="row">
							<div class="col-4">
								<div class="form-group">
									<label for="rg-titulo">Título</label>
									<input type="text" class="form-control form-control-sm" id="rg-titulo" placeholder="Ingrese el título de la notificación">
								</div>
								<div class="form-group">
									<label for="rg-descripcion">Descripción</label>
									<textarea id="rg-descripcion" class="form-control form-control-sm" placeholder="Ingrese una breve descripción acerca de la notificación que desea enviar." rows="3" style="resize:none;"></textarea>
								</div>
								<div class="form-group">
									<label for="rg-banner">Imagen de la notificación</label>
									<input type="file" class="form-control-file" id="rg-banner" aria-describedby="rg-banner-tooltip" accept="image/jpeg,image/png">
									<small id="rg-banner-tooltip" class="form-text text-muted">Seleccione una imagen en formato JPEG o PNG. Esta imagen se enviará como notificación a los usuarios de la app.</small>
								</div>
							</div>
							<div class="col-8">
								<div id="img-preview" class="h-100 w-100">
									<p class="img-preview-info mb-0">Dimensiones: 2000x950px</p>
								</div>
							</div>
						</div>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-sm btn-light" data-dismiss="modal">Cancelar</button>
						<button type="button" class="btn btn-sm btn-primary"><i class="fas fa-plus"></i> Crear notificacion</button>
					</div>
				</form>
			</div>
		</div>
		<!-- scripts -->
		<script src="https://code.jquery.com/jquery-3.4.1.min.js" integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo=" crossorigin="anonymous"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
		<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
		<script src="https://kit.fontawesome.com/13a0a70e59.js"></script>
		<script type="text/javascript">
			var usrToken, usrJson, ListaUsuarios;
			var notificaciones;
			var CurrentId;
			//eventos
			async function ModalFormularioOnShow(args) {
				const modalModo = args.relatedTarget.dataset.modo;
				document.getElementById('rg-titulo').value = '';
				document.getElementById('rg-descripcion').value = '';
				document.getElementById('rg-banner').value = '';
				$('#img-preview').empty();
				if(modalModo == 'I') {
					$('#modal-formulario .modal-footer .btn-primary').off('click').on('click', GuardarPublicidad);
				}
				else { //modalModo == 'E'
					let result;
					let codigo = args.relatedTarget.dataset.codigo;
					try {
						result = await $.ajax({
							url: '{{ url("api/app-life/intranet/publicidad/dt-informacion-publicidad") }}',
							method: 'get',
							data: {
								token: usrToken,
								codigo: codigo
							},
							dataType: 'json'
						});
					}
					catch(err) {
						console.error(err);
						return;
					}
					if(result.error) {
						alert(result.error);
						return;
					}
					let notificacion = result.data.notificacion;
					document.getElementById('rg-titulo').value = notificacion.titulo;
					document.getElementById('rg-descripcion').value = notificacion.descripcion;
					$('#img-preview').append(
						$('<img>').attr('src', notificacion.base64)
					);
					CurrentId = args.relatedTarget.dataset.codigo;
				}
			}
			//
			const toBase64 = file => new Promise((resolve, reject) => {
			    const reader = new FileReader();
			    reader.readAsDataURL(file);
			    reader.onload = () => resolve(reader.result);
			    reader.onerror = error => reject(error);
			});
			CargarPreviewImagen = async () => {
				const file = document.querySelector('#rg-banner').files[0];
				const base64 = await toBase64(file);
				//$('#img-preview').css('background-image','url("' + base64 + '")');
				$('#img-preview').empty().append(
					$('<img>').attr('src', base64)
				);
			}
			GuardarPublicidad = async () => {
				const file = document.querySelector('#rg-banner').files[0];
				const base64 = await toBase64(file);
				let params = {
					titulo: document.getElementById('rg-titulo').value,
					descripcion: document.getElementById('rg-descripcion').value,
					base64: base64,
					token: usrToken
				};
				let result;
				try {
					result = await $.ajax({
						url: '{{ url("api/app-life/intranet/publicidad/sv-guarda-publicidad") }}',
						method: 'post',
						data: params,
						dataType: 'json'
					});
				}
				catch(error) {
					console.error(error);
					return;
				}
				if(result.error) {
					alert(result.error);
					return;
				}
				$('#modal-formulario').modal('hide');
				CargarListaMensajes();
				alert('¡Notificación registrada!');
			}
			EscribirListaNotificaciones = () => {
				let tabla = $('#tabla-mensajes');
				if(notificaciones.length > 0) {
					let tbody = $('<tbody>');
					for(notificacion of notificaciones) {
						tbody.append(
							$('<tr>').append(
								$('<td>').html(notificacion.codigo)
							).append(
								$('<td>').html(notificacion.titulo)
							).append(
								$('<td>').html(notificacion.descripcion)
							).append(
								$('<td>').html(notificacion.fecha)
							).append(
								$('<td>').append(
									$('<a>').attr({
										'data-modo': 'E',
										'data-codigo': notificacion.codigo,
										'data-toggle': 'modal',
										'data-target': '#modal-formulario',
										'href': '#'
									}).html('Ver anuncio').addClass('btn btn-xs btn-primary')
								)
							).append(
								$('<td>').html('GG WP')
							)
						);
					}
					tabla.children('tbody').empty().append(tbody.children());
				}
				else {
					tabla.children('tbody').empty().append(
						$('<tr>').append(
							$('<td>')
						).append(
							$('<td>').attr('colspan',4).html('No hay mensajes programados para enviar')
						)
					);
				}
			}
			CargarListaMensajes = async () => {
				let result;
				try {
					result = await $.ajax({
						url: '{{ url("api/app-life/intranet/publicidad/ls-notificaciones") }}',
						method: 'get',
						data: {
							token: usrToken
						},
						dataType: 'json'
					});
					if(result.error) {
						console.err(error);
						return;
					}
					notificaciones = result.data.notificaciones;
					EscribirListaNotificaciones();
				}
				catch(err) {
					console.log(err);
				}
			}
			IniciarComponentes = () => {
				CargarListaMensajes();
				$('#rg-banner').on('change', CargarPreviewImagen);
				$('#modal-formulario').on('show.bs.modal', ModalFormularioOnShow);
			}
			//
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
					console.log(error);
					location.href = '{{ url("intranet/login") }}';
				}
				if(result.state) {
					usrJson = result.usuario;
					console.log('%c*** Identidad verificada ***', 'background:#009688;color:#ffffff;font-size:13px;');
					console.log('%cBienvenido, ' + usrJson.de_nombre_comercial, 'background:#f5f5f5;color:#1976d2;');
					$('.overlay').fadeOut(150, IniciarComponentes);
				}
				else {
					location.href = '{{ url("intranet/login") }}';
				}
			}
			CerrarSesion = (event) => {
				event.preventDefault();
				localStorage.removeItem('token');
				location.reload();
			}
			IniciarApp = async () => {
				if(localStorage.getItem('token')) {
					console.log('%c! Token identificado. Comprobando su validez', 'background:#f5f5f5;color:#1976d2;');
					usrToken = localStorage.getItem('token');
					ValidarToken();
				}
				else {
					console.log('%c! No se encontró un token de sesión', 'background:#f5f5f5;color:#f44336;');
					location.href = '{{ url("intranet/login") }}';
				}
				//
				$('#modal-logout .btn-danger').on('click', CerrarSesion);
			}
			//
			$(IniciarApp);
		</script>
	</body>
</html>