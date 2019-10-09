<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use DB;
use JWTAuth;

class Intranet extends Controller {
    
    public function __construct() {
        //
    }

    public function home() {
    	return view("intranet.home");
    }

    public function login() {
        return view("intranet.login");
    }

    public function validar_token(Request $request) {
    	try {
    		if(!$user = JWTAuth::toUser($request->get("token"))) {
				return response()->json(['user_not_found'], 404);
			}
    	}
    	catch (Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
    		return response()->json(['token_expired'], $e->getStatusCode());
    	}
    	catch (Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
    		return response()->json(['token_invalid'], $e->getStatusCode());
    	}
    	catch (Tymon\JWTAuth\Exceptions\JWTException $e) {
    		return response()->json(['token_absent'], $e->getStatusCode());
    	}
		return response()->json([
			"state" => true,
			"usuario" => $user
		]);
    }

    public function catalogo(Request $request) {
    	return view("intranet.catalogo");
    }

    public function publicidad(Request $request) {
        return view("intranet.publicidad");
    }

    //rest api

    public function ls_usuarios_life(Request $request) {
    	$user = JWTAuth::toUser($request->get("token"));
    	DB::statement("alter session set nls_date_format = 'dd/mm/yyyy'");
    	$usuarios = DB::select("select
				ssm.co_salon salon,
				cu.de_razon_social rsocial,
				cu.de_nombre_comercial ncomercial,
				vtnm.de_nombre tpnegocio,
				cu.fe_suscripcion fecha,
				cu.st_cuenta_activada activo,
				cu.st_verifica_mail verificado,
				cu.st_puntos habilitado,
				ssm.nu_stock_puntos puntos
			from sl_salon_m ssm
				join vt_clie_m vcm on vcm.co_empresa = ssm.co_empresa and ssm.co_salon = vcm.co_cliente
				join vt_tipo_nego_m vtnm on vtnm.co_tipo_negocio = vcm.co_tipo_negocio
				left join cl_usuarios cu on ssm.co_salon = cu.co_cliente and ssm.co_empresa = cu.co_empresa and cu.st_tipo_usuario = 'D'
			where ssm.co_empresa = ?
				and cu.st_tipo_usuario = 'D'
			order by cu.de_razon_social asc", [$user->co_empresa]);
    	return response()->json([
    		"state" => "success",
    		"data" => compact("usuarios")
    	]);
    }

    public function sv_habilita_puntos(Request $request) {
        $user = JWTAuth::toUser($request->get("token"));
        $estado = $request->get("estado");
        $salon = $request->get("salon");
        //
        if(strcmp($estado,"true") == 0) {//retirar al cliente
            DB::table("cl_usuarios")
                ->where("co_cliente", $salon)
                ->where("co_empresa", $user->co_empresa)
                ->update([
                    "st_puntos" => "N"
                ]);
        }
        else {
            DB::table("cl_usuarios")
                ->where("co_cliente", $salon)
                ->where("co_empresa", $user->co_empresa)
                ->update([
                    "st_puntos" => "S"
                ]);
        }
        return response()->json([
            "state" => "success",
            "data" => compact("estado")
        ]);
    }

    public function ls_locales_salon(Request $request) {
    	$user = JWTAuth::toUser($request->get("token"));
    	$salon = explode("/", $request->getPathInfo())[5];
    	DB::statement("alter session set nls_date_format = 'dd/mm/yyyy'");
    	$locales = DB::select("select
    			slm.co_local codigo,
    			slm.de_nombre local,
    			std.de_direccion direccion,
    			std.fe_registro fecha,
    			slm.es_vigencia vigencia
			from sl_local_m slm
			    left join sl_temp_direcciones std on std.co_empresa = slm.co_empresa and std.co_salon = slm.co_salon and std.co_local = slm.co_local
			where slm.co_salon = ? and slm.co_empresa = ?", [$salon, $user->co_empresa]);
    	return response()->json([
    		"state" => "success",
    		"data" => compact("locales")
    	]);
    }

    public function ls_dependientes_local(Request $request) {
    	$user = JWTAuth::toUser($request->get("token"));
    	$salon = explode("/", $request->getPathInfo())[5];
    	$local = explode("/", $request->getPathInfo())[6];
    	DB::statement("alter session set nls_date_format = 'dd/mm/yyyy'");
    	$datos = DB::table("sl_local_m")
    		->where("co_salon", $salon)
    		->where("co_local", $local)
    		->select("de_nombre as nombre")
    		->first();
    	$dependientes = DB::select("select
    			splm.co_personal_salon dni,
				mcem.de_razon_social nombre,
				splm.de_email email,
				splm.de_telefono telefono,
				splm.fe_registro fecha,
				splm.nu_puntos puntos
			from sl_personal_local_m splm
				join ma_cata_enti_m mcem on splm.co_personal_salon = mcem.co_catalogo_entidad
			where splm.co_empresa = ?
				and splm.co_salon = ?
				and splm.co_local = ?
				and splm.es_vigencia = 'Vigente'", [$user->co_empresa, $salon, $local]);
    	return response()->json([
    		"state" => "success",
    		"data" => compact("dependientes", "datos")
    	]);
    }

    public function ls_bonificaciones_catalogo(Request $request) {
    	$tipo = explode("/", $request->getPathInfo())[5];
    	DB::statement("alter session set nls_date_format = 'dd/mm/yyyy'");
    	$catalogo = DB::select("select
				cbc.co_bonificacion codigo,
				ctb.de_nombre tipo,
				cbc.de_nombre descripcion,
				vtnm.de_nombre tpnegocio,
				cbc.fe_inicio_promo finicio,
				cbc.fe_fin_promo ffin,
				cbc.nu_minimo minimo,
				cbc.nu_maximo maximo,
				cbc.nu_limite limite,
				cbc.nu_multiplicador multiplicador,
				cbc.es_vigencia vigencia,
				sgum.de_nombre usregistra,
				cbc.fe_registro fregistra
			from cl_bonificacion_c cbc
				join cl_tipo_bonificacion ctb on ctb.co_tipo_bonificacion = cbc.co_tipo_bonificacion
				join vt_tipo_nego_m vtnm on vtnm.co_tipo_negocio = cbc.co_tipo_negocio
				join sg_usua_m sgum on cbc.co_usuario_registra = sgum.co_usuario and cbc.co_empresa = sgum.co_empresa_usuario
			where cbc.co_empresa = 11
				and (cbc.co_tipo_bonificacion = ? or 0 = ?)", [$tipo, $tipo]);
    	return response()->json([
    		"state" => "success",
    		"data" => compact("catalogo")
    	]);
    }

    public function ls_bonificaciones_catalogo_detalle(Request $request) {
    	$catalogo = explode("/", $request->getPathInfo())[5];
    	$cabecera = DB::table("cl_bonificacion_c")
    		->where("co_bonificacion", $catalogo)
    		->select("de_nombre as bonificacion")
    		->first();
    	$detalle = DB::select("select
				cbd.nu_bonificacion id,
				cbd.co_producto codigo,
				pr.de_marca marca,
				pr.de_submarca submarca,
				pr.de_producto producto
			from cl_bonificacion_d cbd
				join v_catalogo_producto pr on cbd.co_producto = pr.co_catalogo_producto
			where cbd.co_bonificacion = ?", [$catalogo]);
    	return response()->json([
    		"state" => "success",
    		"data" => compact("cabecera", "detalle")
    	]);
    }

    //publicidad

    public function ls_notificaciones(Request $request) {
        $user = JWTAuth::toUser($request->get("token"));
        $notificaciones = DB::select("select co_notificacion \"codigo\", de_titulo \"titulo\", de_descripcion \"descripcion\", to_char(fe_registro,'yyyy-mm-dd') \"fecha\"
            from cl_publicidad_notificaciones_c
            where co_empresa = ?", [$user->co_empresa]);
        return response()->json([
            "data" => compact("notificaciones")
        ]);
    }

    public function sv_guarda_publicidad(Request $request) {
        $user = JWTAuth::toUser($request->get("token"));
            $empresa = $user->co_empresa;
            $usuario = $user->co_co_cliente;
        $titulo = $request->get("titulo");
        $descripcion = $request->get("descripcion");
        $banner = $request->get("base64");
        //inserta el registro
        try {
            $pdo = DB::getPdo();
            $stmt = $pdo->prepare("call pack_new_clife_clientes.sp_inserta_notif_public(:p_titulo,:p_descripcion,:p_empresa,:p_usureg,:o_codigo)");
                $stmt->bindParam(":p_titulo", $titulo, \PDO::PARAM_STR);
                $stmt->bindParam(":p_descripcion", $descripcion, \PDO::PARAM_STR);
                $stmt->bindParam(":p_empresa", $empresa, \PDO::PARAM_INT);
                $stmt->bindParam(":p_usureg", $usuario, \PDO::PARAM_INT);
                $stmt->bindParam(":o_codigo", $o_codigo, \PDO::PARAM_INPUT_OUTPUT);
            $stmt->execute();
            //el id es "$o_codigo"
        }
        catch(\PDOException $e) {
            return $e;
        }
        $periodo = date("Ym");
        $id = $o_codigo;
        //guarda la imagen alv
        $banner = explode(",", $banner);
        $img_path = implode(DIRECTORY_SEPARATOR, [env("APP_PATH_PUBLICIDAD"),$periodo,$id . ".jpg"]);
        file_put_contents($img_path, base64_decode($banner[1]));
        return response()->json([
            "data" => compact("id")
        ]);
    }

    public function dt_informacion_publicidad(Request $request) {
        $user = JWTAuth::toUser($request->get("token"));
            $empresa = $user->co_empresa;
        $codigo = $request->get("codigo");
        $notificacion = DB::table("cl_publicidad_notificaciones_c")
            ->where("co_empresa", $empresa)
            ->where("co_notificacion", $codigo)
            ->select(
                "de_titulo as titulo",
                "de_descripcion as descripcion",
                DB::raw("to_char(fe_registro,'dd/mm/yyyy') as fecha")
            )
            ->first();
        $periodo = explode("/", $notificacion->fecha);
        $path = implode(DIRECTORY_SEPARATOR, [env("APP_PATH_PUBLICIDAD"), $periodo[2] . $periodo[1], $codigo . ".jpg"]);
        $data = file_get_contents($path);
        $base64 = "data:image/jpeg;base64," . base64_encode($data);
        $notificacion->base64 = $base64;
        return response()->json([
            "data" => compact("notificacion")
        ]);
    }
}