<!DOCTYPE html>
<html>
	<head>
		@include('common.styles')
		<title>Bienvenido a CLife</title>
	</head>
	<body>
		<div class="container">
			<div class="row">
				<div class="col">
					<div id="carouselExampleSlidesOnly" class="carousel slide carousel-fade" data-ride="carousel">
						<div class="carousel-inner">
							<div class="carousel-item active" data-interval="5000">
								<img src="{{ asset('fmedia/1/xqI0q6cf8f6xotFD.jpg') }}" class="d-block w-100" alt="Banner 1">
							</div>
							<div class="carousel-item">
								<img src="{{ asset('fmedia/1/qy9IxlJgOsb6JQcd.jpg') }}" class="d-block w-100" alt="Banner 2">
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<!-- scripts -->
		<script src="https://code.jquery.com/jquery-3.4.1.min.js" integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo=" crossorigin="anonymous"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
		<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
		<script type="text/javascript">
			IniciarApp = async () => {
				console.log('campania');
			}
			$(IniciarApp);
		</script>
	</body>
</html>