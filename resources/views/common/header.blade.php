
		<div class="overlay"></div>
		<nav class="navbar navbar-expand-lg navbar-dark bg-green">
			<a class="navbar-brand" href="#">
				<img src="{{ asset('images/web/clifeapp-logo-light.svg') }}" width="30" height="30" alt="">
			</a>
			<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
				<span class="navbar-toggler-icon"></span>
			</button>
			<div class="collapse navbar-collapse" id="navbarSupportedContent">
				<ul class="navbar-nav mr-auto">
					<li class="nav-item active">
						<a class="nav-link" href="javascript:void(0)">Panel administrativo</a>
					</li>
					<li class="nav-item dropdown">
						<a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
							Opciones
						</a>
						<div class="dropdown-menu" aria-labelledby="navbarDropdown">
							<a class="dropdown-item active" href="{{ url('intranet') }}">Lista de Clientes</a>
							<a class="dropdown-item" href="{{ url('intranet/catalogo') }}">Catálogo de promociones</a>
							<div class="dropdown-divider"></div>
							<a class="dropdown-item" href="{{ url('intranet/publicidad') }}">Publicidad</a>
						</div>
					</li>
				</ul>
				<div class="form-inline my-2 my-lg-0">
					<ul class="navbar-nav">
						<li class="nav-item active">
							<a class="nav-link" href="#" data-toggle="modal" data-target="#modal-logout">Salir</a>
						</li>
					</ul>
				</div>
			</div>
		</nav>