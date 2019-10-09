<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class Extras extends Controller {
    
    public function __construct() {
        //
    }

	public function politica_privacidad() {
		return view("publico.politica_privacidad");
	}

	public function campania($id) {
		return view("publico.campania");
	}
}