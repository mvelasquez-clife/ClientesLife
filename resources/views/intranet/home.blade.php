<!DOCTYPE html>
<html>
	<head>
		@include('common.styles')
		<title>Bienvenido a CLife</title>
	</head>
	<body>
		@include('common.header')
		<div class="container">
			<div class="row">
				<div class="col text-center">
					<div id="div-table"></div>
				</div>
			</div>
		</div>
		<!-- -->
		<div id="modal-logout" class="modal fade bd-example-modal-sm" tabindex="-1" role="dialog">
			<div class="modal-dialog modal-sm" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title">Cerrar sesión</h5>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
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
		<div id="modal-dependientes" class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog">
			<div class="modal-dialog modal-lg" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title"></h5>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">&times;</span>
						</button>
					</div>
					<div class="modal-body"></div>
					<div class="modal-footer">
						<button type="button" class="btn btn-primary" data-dismiss="modal">Cerrar</button>
					</div>
				</div>
			</div>
		</div>
		<!-- scripts -->
		<script src="https://code.jquery.com/jquery-3.4.1.min.js" integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo=" crossorigin="anonymous"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
		<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
		<script src="https://kit.fontawesome.com/13a0a70e59.js"></script>
		<script type="text/javascript">
			var usrToken, usrJson, ListaUsuarios;

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
					$('.overlay').fadeOut(150, CargarListaUsuarios);
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
			CargarListaUsuarios = async () => {
				let result;
				try {
					result = await $.ajax({
						url: '{{ url("api/app-life/intranet/ls-usuarios-life") }}',
						method: 'get',
						data: {
							token: usrToken
						},
						dataType: 'json'
					});
				}
				catch(error) {
					console.log(error);
					return false;
				}
				if(result.state == 'success') {
					ListaUsuarios = result.data.usuarios;
					EscribirListaUsuarios();
				}
				else alert(result.message);
			}
			EscribirListaUsuarios = () => {
				let tbody = $('<tbody>');
				for(let i in ListaUsuarios) {
					let iUsuario = ListaUsuarios[i];
					tbody.append(
						$('<tr>').append(
							$('<td>').append(
								$('<a>').attr({
									'href': '#',
									'data-id': iUsuario.salon
								}).append(
									$('<img>').attr({
										title: 'Ver locales',
										src: '{{ asset("images/icons/ic-detalle.svg") }}'
									}).css("width","24px")
								).on('click', MostrarDetalle)
							)
						).append(
							$('<td>').html(iUsuario.salon).addClass('text-right')
						).append(
							$('<td>').append(
								$('<p>').html(iUsuario.rsocial).addClass('mb-0 font-weight-bold')
							).append(
								$('<p>').html(iUsuario.ncomercial).addClass('mb-0')
							).addClass('text-left')
						).append(
							$('<td>').html(iUsuario.tpnegocio)
						).append(
							$('<td>').html(iUsuario.fecha)
						).append(
							$('<td>').append(
								$('<span>').append(
									$('<i>').addClass(iUsuario.activo == 'S' ? 'fas fa-check' : 'fas fa-times')
								).append(iUsuario.activo == 'S' ? ' Activo' : ' Inactivo').addClass(iUsuario.activo == 'S' ? 'btn btn-xs btn-success' : 'btn btn-xs btn-danger')
							).addClass('text-left')
						).append(
							$('<td>').append(
								$('<span>').append(
									$('<i>').addClass(iUsuario.verificado == 'S' ? 'fas fa-check' : 'fas fa-times')
								).append(iUsuario.verificado == 'S' ? ' Verificado' : ' Pendiente').addClass(iUsuario.verificado == 'S' ? 'btn btn-xs btn-primary' : 'btn btn-xs btn-secondary')
							).addClass('text-left')
						).append(
							$('<td>').append(
								$('<a>').append(
									$('<i>').addClass(iUsuario.habilitado == 'S' ? 'fas fa-check' : 'fas fa-times')
								).attr({
									'href': '#',
									'data-habilitado': iUsuario.habilitado,
									'data-salon': iUsuario.salon
								}).append(iUsuario.habilitado == 'S' ? ' Habilitado' : ' No').addClass(iUsuario.habilitado == 'S' ? 'btn btn-xs btn-info' : 'btn btn-xs btn-light').on('click', HabilitarPuntos)
							).addClass('text-left')
						).append(
							$('<td>').append(
								$('<b>').html(iUsuario.puntos)
							).addClass('text-right')
						)
					).append(
						$('<tr>').hide()
					).append(
						$('<tr>').append(
							$('<td>')
						).append(
							$('<td>').append(
								$('<div>').attr('id','td-' + iUsuario.salon).append(
									$('<img>').attr('src', '{{ asset("images/icons/ic-loader.svg") }}').css('width','96px').addClass('m-3')
								)
							).attr('colspan',7)
						).addClass('tr-container').attr('data-visible',0).hide()
					);
				}
				let table = $('<table>').append(
					$('<thead>').append(
						$('<tr>').append(
							$('<th>').html('')
						).append(
							$('<th>').html('RUC/DNI')
						).append(
							$('<th>').html('Cliente')
						).append(
							$('<th>').html('Tipo negocio')
						).append(
							$('<th>').html('Fecha registro')
						).append(
							$('<th>').html('Activo')
						).append(
							$('<th>').html('Verificado')
						).append(
							$('<th>').html('Habilitado para puntos')
						).append(
							$('<th>').html('Puntos')
						)
					)
				).append(tbody).addClass('table table-striped table-hover bg-light');
				$('#div-table').addClass('text-center').empty().append(table);
			}
			MostrarDetalle = (event) => {
				event.preventDefault();
				let id = 'td-' + event.currentTarget.dataset.id;
				let tr = $('#' + id).parent().parent();
				if(tr.data('visible') == 1) {
					tr.hide();
					tr.data('visible', 0);
				}
				else {
					CargarListaLocales(event.currentTarget.dataset.id);
					tr.show();
					tr.data('visible', 1);
				}
			}
			CargarListaLocales = async (id) => {
				let result;
				try {
					result = await $.ajax({
						url: '{{ url("api/app-life/intranet/ls-locales-salon") }}/' + id,
						method: 'get',
						data: { token: usrToken },
						dataType: 'json'
					});
					//escribe la grid
					let tbody = $('<tbody>');
					let locales = result.data.locales;
					for(let i in locales) {
						let iLocal = locales[i];
						tbody.append(
							$('<tr>').append(
								$('<td>').append(
									$('<a>').attr({
										'href': '#',
										'data-salon': id,
										'data-local': iLocal.codigo,
										'data-toggle': 'modal',
										'data-target': '#modal-dependientes'
									}).append(
										$('<img>').attr({
											title: 'Dependientes del local',
											src: '{{ asset("images/icons/ic-personal.svg") }}'
										}).css("width","24px")
									)
								)
							).append(
								$('<td>').html(iLocal.local)
							).append(
								$('<td>').html(iLocal.direccion)
							).append(
								$('<td>').html(iLocal.fecha)
							).append(
								$('<td>').html(iLocal.vigencia)
							)
						);
					}
					let table = $('<table>').append(
						$('<thead>').append(
							$('<tr>').append(
								$('<th>').html('')
							).append(
								$('<th>').html('Local')
							).append(
								$('<th>').html('Dirección')
							).append(
								$('<th>').html('Fecha registro')
							).append(
								$('<th>').html('Vigencia')
							).addClass('bg-dark text-light')
						)
					).append(
						tbody.addClass('bg-light')
					).addClass('table table-hover text-left');
					$('#td-' + id).empty().append(table);
				}
				catch(error) {
					console.log(error);
				}
			}
			CargarListaDependientes = async (event) => {
				let dataset = event.relatedTarget.dataset;
				let local = dataset.local;
				let salon = dataset.salon;
				let result;
				$('#modal-dependientes .modal-body').empty();
				try {
					result = await $.ajax({
						url: '{{ url("api/app-life/intranet/ls-dependientes-local") }}/' + salon + '/' + local,
						method: 'get',
						data: { token: usrToken },
						dataType: 'json'
					});
				}
				catch(error) {
					console.log(error);
					return;
				}
				if(result.state == 'success') {
					let tbody = $('<tbody>');
					let DatosSalon = result.data.datos;
					let ListaDependientes = result.data.dependientes;
					for(let i in ListaDependientes) {
						let iDependiente = ListaDependientes[i];
						tbody.append(
							$('<tr>').append(
								$('<td>').html(iDependiente.dni)
							).append(
								$('<td>').html(iDependiente.nombre)
							).append(
								$('<td>').html(iDependiente.email)
							).append(
								$('<td>').html(iDependiente.telefono)
							).append(
								$('<td>').html(iDependiente.fecha)
							).append(
								$('<td>').append(
									$('<span>').addClass('btn btn-xs btn-info').html(iDependiente.puntos + ' pts.')
								).addClass('text-right')
							)
						);
					}
					let table = $('<table>').append(
						$('<thead>').append(
							$('<tr>').append(
								$('<th>').html('DNI')
							).append(
								$('<th>').html('Nombre')
							).append(
								$('<th>').html('e-mail')
							).append(
								$('<th>').html('Teléfono')
							).append(
								$('<th>').html('Fecha registro')
							).append(
								$('<th>').html('Puntos')
							)
						)
					).append(
						tbody.addClass('bg-light')
					).addClass('table table-striped table-hover text-left');
					$('#modal-dependientes .modal-body').append(table);
					$('#modal-dependientes .modal-title').html(DatosSalon.nombre);
				}
			}
			HabilitarPuntos = async (event) => {
				event.preventDefault();
				let estado = event.delegateTarget.dataset.habilitado == 'S';
				let salon = event.delegateTarget.dataset.salon;
				if(estado) {
					if(window.confirm('¿Retirar al cliente del programa de puntos? Los puntajes acumulados del cliente y sus dependientes no se borrarán, pero ya no serán accesibles hasta que se vuelva a habilitar al cliente.')) {
						let result;
console.log('{{ url("api/app-life/intranet/sv-habilita-puntos") }}');
return;
						try {
							result = await $.ajax({
								url: '{{ url("api/app-life/intranet/sv-habilita-puntos") }}',
								method: 'get',
								data: {
									token: usrToken,
									estado: estado,
									salon: salon
								},
								dataType: 'json'
							});
							if(result.state == 'success') {
								CargarListaUsuarios();
							}
							else alert(result.message);
						}
						catch(error) {
							console.log(error);
						}
					}
				}
				else {
					if(window.confirm('Se habilitará el control de puntos para el cliente. ¿Desea continuar?')) {
						let result;
						try {
							result = await $.ajax({
								url: '{{ url("api/app-life/intranet/sv-habilita-puntos") }}',
								method: 'get',
								data: {
									token: usrToken,
									estado: estado,
									salon: salon
								},
								dataType: 'json'
							});
							if(result.state == 'success') {
								CargarListaUsuarios();
							}
							else alert(result.message);
						}
						catch(error) {
							console.log(error);
						}
					}
				}
			}
			//
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
				$('#modal-dependientes').on('show.bs.modal', CargarListaDependientes);
			}
			//
			$(IniciarApp);
		</script>
	</body>
</html>