<!DOCTYPE html>
<html>
	<head>
		@include('common.styles')
		<title>Catálogo de promociones</title>
	</head>
	<body>
		@include('common.header')
		<div class="container-fluid">
			<div class="row">
				<div class="col">
					<table id="tabla-catalogo" class="table table-striped table-hover">
						<thead>
							<tr>
								<th>#</th>
								<th>Tipo</th>
								<th>Descripción</th>
								<th>Tipo negocio</th>
								<th>Fecha inicio</th>
								<th>Fecha fin</th>
								<th>Cant. mín.</th>
								<th>Cant. máx.</th>
								<th>Cant. límite</th>
								<th>Multiplicador</th>
								<th>Vigencia</th>
								<th>Usuario registra</th>
								<th>Fecha registro</th>
							</tr>
						</thead>
						<tbody></tbody>
					</table>
				</div>
			</div>
		</div>
		<!-- modals -->
		<div id="modal-detalle" class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog">
			<div class="modal-dialog modal-lg" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title"></h5>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">&times;</span>
						</button>
					</div>
					<div class="modal-body">
						<p>Cargando datos. Por favor, espere...</p>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-primary" data-dismiss="modal">Cerrar</button>
					</div>
				</div>
			</div>
		</div>
		<!-- -->
		<script src="https://code.jquery.com/jquery-3.4.1.min.js" integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo=" crossorigin="anonymous"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
		<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
		<script type="text/javascript">
			var CatalogoBonificaciones;

			async function CargarCatalogoBonificaciones() {
				$('#tabla-catalogo').children('tbody').empty();
				//let tipo = $('#bt-tp-negocio').data('tipo');
				let tipo = 0;
				let result;
				try {
					result = await $.ajax({
						url: '{{ url("api/app-life/intranet/ls-bonificaciones-catalogo") }}/' + tipo,
						method: 'get',
						data: {
							_token: '{{ csrf_token() }}'
						},
						dataType: 'json'
					});
				}
				catch(error) {
					console.log(error);
					return false;
				}
				if(result.state == 'success') {
					CatalogoBonificaciones = result.data.catalogo;
					EscribirListaBonificaciones();
				}
				else alert(result.message);
			}

			async function CargarDetalleCatalogo(args) {
				let catalogo = args.relatedTarget.dataset.codigo;
				let result;
				try {
					result = await $.ajax({
						url: '{{ url("api/app-life/intranet/ls-bonificaciones-catalogo-detalle") }}/' + catalogo,
						method: 'get',
						data: { _token: '{{ csrf_token() }}' },
						dataType: 'json'
					});
				}
				catch(error) {
					console.log(error);
				}
				if(result.state == 'success') {
					EscribirCatalogoDetalle(result.data);
				}
			}

			function EscribirListaBonificaciones() {
				let tbody = $('#tabla-catalogo').children('tbody');
				for(let i in CatalogoBonificaciones) {
					let iBonificacion = CatalogoBonificaciones[i];
					tbody.append(
						$('<tr>').append(
							$('<th>').append(
								$('<a>').attr({
									'href': '#',
									'data-codigo': iBonificacion.codigo,
									'data-toggle': 'modal',
									'data-target': '#modal-detalle'
								}).addClass('btn btn-xs btn-primary text-left').html('Ver')
							)
						).append(
							$('<td>').html(iBonificacion.tipo)
						).append(
							$('<td>').html(iBonificacion.descripcion)
						).append(
							$('<td>').append(
								$('<b>').html(iBonificacion.tpnegocio)
							)
						).append(
							$('<td>').html(iBonificacion.finicio)
						).append(
							$('<td>').html(iBonificacion.ffin)
						).append(
							$('<td>').addClass('text-right').html(iBonificacion.minimo == -1 ? 'No' : iBonificacion.minimo)
						).append(
							$('<td>').addClass('text-right').html(iBonificacion.maximo)
						).append(
							$('<td>').addClass('text-right').html(iBonificacion.limite == -1 ? 'Ilimitado' : iBonificacion.limite)
						).append(
							$('<td>').addClass('text-right').html(iBonificacion.multiplicador)
						).append(
							$('<td>').append(
								$('<span>').addClass('btn btn-xs btn-' + (iBonificacion.vigencia == 'Vigente' ? 'success' : 'danger')).html(iBonificacion.vigencia)
							)
						).append(
							$('<td>').html(iBonificacion.usregistra)
						).append(
							$('<td>').html(iBonificacion.fregistra)
						)
					);
				}
			}

			function EscribirCatalogoDetalle(DataBonificaciones) {
				let tbody = $('<tbody>');
				let DescripcionBonificacion = DataBonificaciones.cabecera.bonificacion;
				let DetalleBonificaciones = DataBonificaciones.detalle;
				for(let i in DetalleBonificaciones) {
					let iDetalle = DetalleBonificaciones[i];
					tbody.append(
						$('<tr>').append(
							$('<td>').html(iDetalle.codigo)
						).append(
							$('<td>').html(iDetalle.producto)
						).append(
							$('<td>').html(iDetalle.marca)
						).append(
							$('<td>').html(iDetalle.submarca)
						)
					);
				}
				let table = $('<table>').append(
					$('<thead>').append(
						$('<tr>').append(
							$('<th>').html('Código')
						).append(
							$('<th>').html('Descripción')
						).append(
							$('<th>').html('Marca')
						).append(
							$('<th>').html('Submarca')
						)
					)
				).append(tbody).addClass('table table-striped table-hover');
				$('#modal-detalle .modal-title').html(DescripcionBonificacion);
				$('#modal-detalle .modal-body').empty().append(table);
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
					console.log(error);
					location.href = '{{ url("intranet/login") }}';
				}
				if(result.state) {
					usrJson = result.usuario;
					console.log('%c*** Identidad verificada ***', 'background:#009688;color:#ffffff;font-size:13px;');
					console.log('%cBienvenido, ' + usrJson.de_nombre_comercial, 'background:#f5f5f5;color:#1976d2;');
					$('.overlay').fadeOut(150, CargarCatalogoBonificaciones);
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
				$('#modal-detalle').on('show.bs.modal', CargarDetalleCatalogo);
			}
			//
			$(IniciarApp);
		</script>
	</body>
</html>