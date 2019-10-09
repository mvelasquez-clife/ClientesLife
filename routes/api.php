<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::group(["prefix" => "app-life"], function() {
    Route::any("busca", "ClientesLife@busqueda");
	Route::any("registro", "ClientesLife@registro");
	Route::any("login", "ClientesLife@login");
	Route::any("auth-dependiente", "ClientesLife@auth_dependiente");
    //
	Route::group(/*["middleware" => "jwt.auth"]*/[], function () {
		Route::get("user", "ClientesLife@getAuthUser");
		Route::any("cuenta-corriente", "ClientesLife@cuenta_corriente");
		Route::any("pagos-documento", "ClientesLife@pagos_documento");
		Route::any("ultimos-pedidos", "ClientesLife@ultimos_pedidos");
		Route::any("info-cliente", "ClientesLife@info_cliente");
		Route::any("lista-locales", "ClientesLife@lista_locales");
		Route::any("lista-dependientes-local", "ClientesLife@lista_dependientes_local");
		Route::any("registra-dependiente-local", "ClientesLife@registra_dependiente_local");
		Route::any("registra-local", "ClientesLife@registra_local");
		Route::any("datos-dependiente", "ClientesLife@datos_dependiente");
		Route::any("auth-dependiente", "ClientesLife@auth_dependiente");
		Route::any("info-dependiente", "ClientesLife@info_dependiente");
		Route::any("info-reparticion-puntos-local", "ClientesLife@info_reparticion_puntos_local");
		Route::any("asigna-puntos-local", "ClientesLife@asigna_puntos_local");
		Route::any("info-asignar-puntos-dependiente", "ClientesLife@info_asignar_puntos_dependiente");
		Route::any("asignar-puntos-dependiente", "ClientesLife@asignar_puntos_dependiente");
		Route::any("stock-puntos", "ClientesLife@stock_puntos");
		Route::any("premios-disponibles", "ClientesLife@premios_disponibles");
		Route::any("descripcion-premio", "ClientesLife@descripcion_premio");
		Route::any("reclamar-premio", "ClientesLife@reclamar_premio");
		Route::any("direccion-local", "ClientesLife@direccion_local");
		Route::any("detalle-factura", "ClientesLife@detalle_factura");
		Route::any("guarda-calificacion-pedido", "ClientesLife@guarda_calificacion_pedido");
		Route::any("procesar-pago-culqi", "ClientesLife@procesar_pago_culkk");
		Route::any("consulta-cargo", "ClientesLife@consulta_cargo");
		Route::any("info-perfil", "ClientesLife@info_perfil");
		Route::any("lista-logros", "ClientesLife@lista_logros");
		Route::any("reclama-logro", "ClientesLife@reclamar_logro");
		Route::any("cambiar-permisos-dependiente", "ClientesLife@cambiar_permisos_dependiente");
	});
	//registro via app
	Route::group(["prefix" => "registro-app"], function() {
		Route::any("validar-ruc-cliente", "ClientesLife@validar_ruc_cliente");
		Route::any("cargar-info-cliente", "ClientesLife@cargar_info_cliente");
		Route::any("guardar-info-cliente", "ClientesLife@guardar_info_cliente");
	});
	//
	Route::group(["prefix" => "intranet"], function() {
		Route::any("validar-token", "Intranet@validar_token");
		Route::get("ls-usuarios-life", "Intranet@ls_usuarios_life");
		Route::get("sv-habilita-puntos", "Intranet@sv_habilita_puntos");
		Route::get("ls-locales-salon/{salon}", "Intranet@ls_locales_salon");
		Route::get("ls-dependientes-local/{salon}/{local}", "Intranet@ls_dependientes_local");
		Route::get("ls-bonificaciones-catalogo/{tipo}", "Intranet@ls_bonificaciones_catalogo");
		Route::get("ls-bonificaciones-catalogo-detalle/{catalogo}", "Intranet@ls_bonificaciones_catalogo_detalle");
		//publicidad
		Route::group(["prefix" => "publicidad"], function() {
			Route::get("ls-notificaciones", "Intranet@ls_notificaciones");
			Route::post("sv-guarda-publicidad", "Intranet@sv_guarda_publicidad");
			Route::get("dt-informacion-publicidad", "Intranet@dt_informacion_publicidad");
		});
	});
	//
	Route::any("prueba-mail", "ClientesLife@prueba_mail");
});