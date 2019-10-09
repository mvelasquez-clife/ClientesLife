<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;

class Publico extends Controller {

	public function landing_page() {
		return view("publico.landing");
	}

    public function form_registro() {
        return view("publico.registro");
    }

    public function activar_cuenta(Request $request) {
    	list($empresa, $cliente) = explode("@", decrypt($request->get("key")));
    	DB::table("cl_usuarios")
    		->where("co_empresa", $empresa)
    		->where("co_cliente", $cliente)
    		->where("st_cuenta_activada", "N")
    		->update([
    			"st_verifica_mail" => "S",
    			"st_cuenta_activada" => "S"
    		]);
    	return view("publico.alta");
    }
}