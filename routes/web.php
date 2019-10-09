<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', "Publico@landing_page");
Route::get("registro", "Publico@form_registro");
Route::any("activar", "Publico@activar_cuenta");
//intranet
Route::group(["prefix" => "intranet"], function() {
	Route::get("/", "Intranet@home");
	Route::get("login", "Intranet@login");
	Route::get("catalogo", "Intranet@catalogo");
	Route::get("publicidad", "Intranet@publicidad");
});
//generales
Route::group(["prefix" => "extras"], function() {
	Route::get("politica-privacidad", "Extras@politica_privacidad");
	Route::get("campania/{id}", "Extras@campania");
});
