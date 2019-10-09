<!DOCTYPE html>
<html>
<head>
	<title></title>
</head>
<body style="background-color:#eeeeee;margin:0;padding:48px;width:100%;">
	<style type="text/css">
		@import url('https://fonts.googleapis.com/css?family=Lato:300,400');
		*{box-sizing: border-box;font-family: "Lato", sans-serif;}
		.myButton{background-color:#404040;-moz-border-radius:28px;-webkit-border-radius:28px;border-radius:28px;border:1px solid #808080;display:inline-block;cursor:pointer;color:#ffffff;font-family:'Lato',sans-serif;font-size:17px;font-weight:400;padding:16px 31px;text-decoration:none;text-shadow:0px 1px 0px #00695d;}
		.myButton:hover{background-color:#202020;}
		.myButton:active{position:relative;top:1px;}
	</style>
	<div style="width:100%;">
		<div style="background-color:#ffffff;border-radius:8px;margin:0 auto;padding:8px 32px;position:relative;text-align:center;width:640px;">
			<img src="{{ asset('images/clife-logo.svg') }}" style="width:96px;margin:-48px -48px;position:absolute;left:50%;">
			<h1 style="color:#808080;font-size:32px;font-weight:300;margin-top:64px;text-align:center;width:100%;">Bienvenido a CLife App</h1>
			<p style="color:#808080;font-size:18px;font-weight:400;margin-top:32px;text-align:center;width:100%;">Estimado {{ $usuario->de_razon_social }}:</p>
			<p style="color:#808080;font-size:16px;font-weight:300;text-align:center;width:100%;">Hemos recibido exitosamente tu solicitud de registro. Para terminar, deberás hacer clic en el siguiente botón para activar tu cuenta.</p>
			<a class="myButton" href="{{ url('activar') }}?key={{ encrypt($key) }}" style="color:#ffffff;margin:8px 0;">Activar mi cuenta ahora -&gt;</a>
			<br><br>
		</div>
		<p style="color:#404040;font-size:14px;font-weight:300;margin:8px auto 0;padding:4px 8px;text-align:center;width:640px;">¿No funciona el botón? Copia y pega el siguiente texto en tu navegador:</p>
		<p style="color:#404040;font-size:14px;font-weight:400;margin:0 auto;padding:4px 8px;text-align:center;width:640px;">{{ url('activar') }}?key={{ encrypt($key) }}</p>
		<p style="color:#404040;font-size:12px;font-weight:300;margin:24px auto 0;padding:4px 8px;text-align:right;width:640px;">2019 &copy; Corporación Life. Todos los derechos reservados</p>
	</div>
</body>
</html>