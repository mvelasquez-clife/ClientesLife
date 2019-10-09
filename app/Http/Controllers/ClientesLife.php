<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use JWTAuth;
use App\User as User;
use JWTAuthException;
use DB;

class ClientesLife extends Controller {

    private $user;
    
    public function __construct(User $user) {
        $this->user = $user;
        //
        define("STRING_SEPARATOR", "|");
        //
        define("REQUEST_BUSQUEDA", 1);
        define("REQUEST_REGISTRO_CLIENTES", 2);
        define("REQUEST_LOGIN", 3);
        define("REQUEST_CUENTA_CORRIENTE", 4);
        define("REQUEST_DETALLE_PAGOS", 5);
        define("REQUEST_ULTIMOS_PEDIDOS", 6);
        define("REQUEST_INFO_CLIENTE", 7);
        define("REQUEST_LISTA_LOCALES", 8);
        define("REQUEST_LISTA_DEPENDIENTES_LOCAL", 9);
        define("REQUEST_REGISTRA_DEPENDIENTE", 10);
        define("REQUEST_REGISTRA_LOCAL", 11);
        define("REQUEST_DATOS_DEPENDIENTE", 12);
        define("REQUEST_AUTH_DEPENDIENTE", 13);
        define("REQUEST_INFO_DEPENDIENTE", 14);
        define("REQUEST_INFO_REPARTE_PUNTOS", 15);
        define("REQUEST_ASIGNA_PUNTAJE", 16);
        define("REQUEST_INFO_ASIGNAR_PTS_DEP", 17);
        define("REQUEST_ASIGNA_PTS_DEP", 18);
        define("REQUEST_STOCK_PUNTAJE", 19);
        define("REQUEST_LISTA_PREMIOS", 20);
        define("REQUEST_DETALLE_PREMIOS", 21);
        define("REQUEST_RECLAMA_PREMIOS", 22);
        define("REQUEST_DETALLE_FACTURA", 23);
        define("REQUEST_CALIFICA_PEDIDO", 24);
        define("REQUEST_PROCESA_PAGO", 25);
        define("REQUEST_INFO_PERFIL", 26);
        define("REQUEST_LISTA_LOGROS", 27);
        define("REQUEST_RECLAMA_LOGRO", 28);
        define("REQUEST_ACTUALIZA_PRIVILEGIOS", 29);
        define("REQUEST_CARGA_DIRECCION", 30);
        //
        define("REQUEST_RUCDNI_CLIENTE", 31);
        define("REQUEST_DATOS_CLIENTE", 32);
        define("REQUEST_GUARDAR_INFO_CLIENTE", 33);
    }

    public function busqueda(Request $request) {
        $codigo = $request->get("rucdni");
        $data = DB::select("select
                mcem.de_nombre_comercial as ncomercial,
                mcem.de_razon_social as rsocial,
                vcm.de_email as email,
                vcm.de_telefono_fel as telefono
            from vt_clie_m vcm
                join ma_cata_enti_m mcem on vcm.co_cliente = mcem.co_catalogo_entidad
            where vcm.co_cliente = ?
                and vcm.co_empresa = 11", [$codigo]);
        if(count($data) > 0) {
            return response()->json([
                "status" => true,
                "data" => [
                    "cliente" => $data[0]
                ]
            ]);
        }
        return response()->json([
            "status" => false,
            "message" => "Cliente no existe"
        ]);
    }
   
    public function registro(Request $request) {
        $empresa = 11;
        $encontrados = DB::table("cl_usuarios")
            ->where("co_cliente", $request->get("codigo"))
            ->where("co_empresa", $empresa)
            ->count();
        if($encontrados == 0) {
            DB::table("cl_usuarios")->insert([
                "co_cliente" => $request->get("codigo"),
                "co_empresa" => $empresa,
                "de_nombre_comercial" => $request->get("ncomercial"),
                "de_razon_social" => $request->get("rsocial"),
                "co_rucdni" => $request->get("codigo"),
                "de_email" => $request->get("email"),
                "de_telefono" => $request->get("telefono"),
                "password" => bcrypt($request->get("clave")),
            ]);
            //registra el salon
            DB::table("sl_salon_m")->insert([
                "co_salon" => $request->get("codigo"),
                "co_empresa" => $empresa,
                "de_descripcion" => "Salón de belleza " . $request->get("ncomercial"),
                "fe_registro" => date("Y-m-d H:i:s"),
                "st_imagen" => "N",
                "nu_stock_puntos" => 0
            ]);
            //registra un local
            DB::table("sl_local_m")->insert([
                "co_local" => 1,
                "co_salon" => $request->get("codigo"),
                "co_empresa" => $empresa,
                "de_nombre" => $request->get("ncomercial")
            ]);
            $usuario = User::find($request->get("codigo"));
            //crea el detalle de los logros
            /*DB::insert("insert into cl_logros_usuario_c(co_logro,co_usuario,co_empresa)
                select co_logro,?,? from cl_catalogo_logros_c",[$request->get("codigo"),$empresa]);
            DB::table("cl_catalogo_logros_c")
                ->where("co_logro", 1)
                ->where("co_cliente", $request->get("codigo"))
                ->update([
                    "nu_progreso" => 1
                ]);*/
            //envia el pinche email
            $key = implode("@",[$usuario->co_empresa, $usuario->co_rucdni]);
            \Mail::send("emails.confirma", compact("usuario","key"), function($message) use($usuario) {
                $message->from(env("MAIL_FROM_ADDRESS"), env("MAIL_FROM_NAME"));
                $message->to($usuario->de_email)->subject("Activar tu cuenta CLife");
            });
            //asigna puntos por los pedidos
            $documentos = DB::table("ma_file_fisi_vent_c")
                ->where("co_cliente", $request->get("codigo"))
                ->where("co_empresa", $empresa)
                ->where("fe_facturacion", ">", date("Y-m-d H:i:s"))
                ->select("co_file_fisico_venta_final as documento")
                ->get();
            foreach($documentos as $documento) {
                DB::statement("call pack_new_clife_clientes.sp_aplicar_bonificaciones(?,?)", [$documento->documento,$empresa]);
            }
            //listijirillo
            return response()->json([
                "status" => true,
                "data" => [
                    "usuario" => $usuario
                ]
            ]);
        }
        return response()->json([
            "status" => false,
            "message" => "El RUC ingresado " . $request->get("codigo") . " ya se encuentra registrado"
        ]);
    }
    
    public function login(Request $request) {
        $credentials = $request->only("co_cliente", "co_empresa", "password");
        $token = null;
        try {
            if (!$token = JWTAuth::attempt($credentials)) {
                //return response()->json(['invalid_email_or_password'], 422);
                return response()->json([
                    "status" => false,
                    "message" => "El usuario no está habilitado para acceder al sistema"
                ], 422);
            }
        }
        catch (JWTAuthException $e) {
            return response()->json([
                "status" => false,
                "message" => "Falló al creat el token"
            ], 500);
        }
        $usuario = User::find($request->get("co_cliente"));
        if(strcmp($usuario->st_cuenta_activada,"N") == 0) {
            //envia el pinche email
            $key = implode("@",[$usuario->co_empresa, $usuario->co_rucdni]);
            \Mail::send("emails.confirma", compact("usuario","key"), function($message) use($usuario) {
                $message->from(env("MAIL_FROM_ADDRESS"), env("MAIL_FROM_NAME"));
                $message->to($usuario->de_email)->subject("Activar tu cuenta CLife");
            });
            return response()->json([
                "status" => false,
                "message" => "Tu cuenta se encuentra inactiva. Deberás utilizar el correo de activación que hemos enviado a " . $usuario->de_email
            ]);
        }
        return response()->json([
            "status" => true,
            "data" => compact("usuario", "token"),
            "rqid" => REQUEST_LOGIN
        ]);
    }

    public function cuenta_corriente(Request $r) {
        $ruc = $r->get("codigo");
        $empresa = 11;
        $moneda = 1;
        $alias = DB::select("select rownum,sgum.de_alias alias from (select co_vendedor,fe_facturacion,co_empresa from ma_file_fisi_vent_c where co_cliente = ? and co_empresa = ? order by fe_facturacion desc) T join sg_usua_m sgum on T.co_vendedor = sgum.co_usuario and T.co_empresa = sgum.co_empresa_usuario where rownum = 1", [$ruc, $empresa]);
        if(count($alias) > 0) {
            $alias = $alias[0]->alias;
            DB::statement("call pack_venta.sm_activar_empresa(?)", [$alias]);
            $xdata = implode("@",[$alias,$ruc,34,1]);
            $ctacte = DB::select("select pack_new_clife_clientes.f_v_ctacte_cliente(?) ctacte from dual",[$xdata])[0];
            $consumo = DB::table("ma_file_fisi_vent_c")
                ->where("co_cliente", $ruc)
                ->where("co_empresa", $empresa)
                ->where("es_vigencia", "Conforme")
                ->sum("im_total");
            list($rzsocial,$disponible,$solicitado,$deuda,$estado,$mensaje,$direccion,$codireccion,$codigo) = explode("@",$ctacte->ctacte);
            $documentos = DB::select("select * from table(pack_new_clife_clientes.f_vt_fact_pendientes_cliente(?,?,?))", [$ruc, $empresa, $moneda]);
            $info = [
                "consumo" => (double) $consumo,
                "deuda" => (double) $deuda,
                "ruc" => $codigo,
                "cliente" => $rzsocial
            ];
            return response()->json([
                "status" => true,
                "data" => compact("documentos","info"),
                "rqid" => REQUEST_CUENTA_CORRIENTE
            ]);
        }
        return response()->json([
            "status" => false,
            "message" => "Parámetros incorrectos",
            "rqid" => REQUEST_CUENTA_CORRIENTE
        ]);
    }

    public function pagos_documento(Request $request) {
        $documento = $request->get("documento");
        $empresa = 11;
        DB::statement("alter session set nls_date_format = 'dd/mm/yyyy'");
        $pagos = DB::select("select nombre fecha, otros fvence, nom_wap detalle, pvta importe, stock pago from table(pack_new_clife_clientes.f_pagos_fact(?,?))", [$documento, $empresa]);
        $info = DB::table("ba_cuen_corr_admi_clie_d")
            ->where("co_documento", $documento)
            ->where("co_empresa", $empresa)
            ->select("im_egreso as importe","im_saldo as deuda","fe_vence_factura as fvence")
            ->first();
        return response()->json([
            "status" => true,
            "data" => compact("pagos", "info"),
            "rqid" => REQUEST_DETALLE_PAGOS
        ]);
    }

    public function ultimos_pedidos(Request $request) {
        $cliente = $request->get("cliente");
        $empresa = $request->get("empresa");
        DB::statement("alter session set nls_date_format = 'dd-mm-yyyy'");
        $pedidos = DB::select("select * from table(pack_new_clife_clientes.f_ultimos_pedidos(?,?))", [$cliente, $empresa]);
        $cantidad = count($pedidos);
        return response()->json([
            "status" => true,
            "data" => compact("pedidos", "cantidad"),
            "rqid" => REQUEST_ULTIMOS_PEDIDOS
        ]);
    }

    public function info_cliente(Request $request) {
        $cSalon = $request->get("salon");
        $cEmpresa = 11;
        $lista = DB::table("sl_local_m")
            ->where("co_salon", $cSalon)
            ->where("co_empresa", $cEmpresa)
            ->where("es_vigencia", "Vigente")
            ->select("co_local as value", "de_nombre as text")
            ->get();
        $locales = count($lista);
        $dependientes = DB::table("sl_personal_local_m")
            ->where("co_salon", $cSalon)
            ->where("co_empresa", $cEmpresa)
            ->where("es_vigencia", "Vigente")
            ->count();
        $puntaje = DB::table("sl_salon_m")
            ->where("co_salon", $cSalon)
            ->where("co_empresa", $cEmpresa)
            ->select("nu_stock_puntos as puntaje")
            ->first()
            ->puntaje;
        return response()->json([
            "status" => true,
            "data" => compact("locales", "dependientes", "puntaje", "lista"),
            "rqid" => REQUEST_INFO_CLIENTE
        ]);
    }

    public function lista_locales(Request $request) {
        DB::statement("alter session set nls_date_format = 'dd/mm/yyyy'");
        $locales = DB::table("sl_local_m")
            ->select(
                "co_local as local",
                "de_nombre as nombre",
                "fe_registro as registro",
                "nu_stock_puntos as puntos"
            )
            ->where("co_salon", $request->get("salon"))
            ->where("co_empresa", 11)
            ->where("es_vigencia", "Vigente")
            ->get();
        return response()->json([
            "status" => true,
            "data" => compact("locales"),
            "rqid" => REQUEST_LISTA_LOCALES
        ]);
    }

    public function lista_dependientes_local(Request $request) {
        $cEmpresa = 11;
        $cSalon = $request->get("salon");
        $cLocal = $request->get("local");
        DB::statement("alter session set nls_date_format = 'dd/mm/yyyy'");
        $dependientes = DB::select("select * from table(pack_new_clife_clientes.f_dependientes_local(?,?,?))", [$cEmpresa,$cSalon,$cLocal]);
        return response()->json([
            "status" => true,
            "data" => compact("dependientes"),
            "rqid" => REQUEST_LISTA_DEPENDIENTES_LOCAL
        ]);
    }

    public function registra_dependiente_local(Request $request) {
        $cEmpresa = 11;
        $salon = $request->get("salon");
        $local = $request->get("local");
        $dni = $request->get("dni");
        $apepat = $request->get("apepat");
        $apemat = $request->get("apemat");
        $nombres = $request->get("nombres");
        $mail = $request->get("mail");
        $telefono = $request->get("telefono");
        DB::statement("call pack_new_clife_clientes.sp_registra_dependiente(?,?,?,?,?,?,?,?,?)", [$cEmpresa,$salon,$local,$dni,$apepat,$apemat,$nombres,$mail,$telefono]);
        return response()->json([
            "status" => true,
            "rqid" => REQUEST_REGISTRA_DEPENDIENTE
        ]);
    }

    public function registra_local(Request $request) {
        $cEmpresa = 11;
        $salon = $request->get("salon");
        $nombre = $request->get("nombre");
        $direccion = $request->get("direccion");
        $latitud = $request->get("latitud");
        $longitud = $request->get("longitud");
        DB::statement("call pack_new_clife_clientes.sp_registra_local(?,?,?,?,?,?)", [$cEmpresa,$salon,$nombre,$direccion,$latitud,$longitud]);
        return response()->json([
            "status" => true,
            "rqid" => REQUEST_REGISTRA_LOCAL
        ]);
    }

    public function datos_dependiente(Request $request) {
        $cEmpresa = 11;
        $salon = $request->get("salon");
        $local = $request->get("local");
        $dependiente = $request->get("dependiente");
        $datos = DB::select("select * from table(pack_new_clife_clientes.f_datos_dependiente(?,?,?,?))", [$cEmpresa,$salon,$local,$dependiente]);
        if(count($datos) > 0) {
            $datos = $datos[0];
            $datos->key = encrypt(implode(STRING_SEPARATOR, [$datos->empresa,$datos->salon,$datos->clocal,$datos->codigo]));
            return response()->json([
                "status" => true,
                "data" => compact("datos"),
                "rqid" => REQUEST_DATOS_DEPENDIENTE
            ]);
        }
        return response()->json([
            "status" => false,
            "message" => "No se encontraron dependientes con los datos ingresados",
            "rqid" => REQUEST_DATOS_DEPENDIENTE
        ]);
    }

    //

    public function auth_dependiente(Request $request) {
        $key = $request->get("key");
        list($empresa,$salon,$local,$dependiente) = explode(STRING_SEPARATOR,decrypt($key));
        $datos = DB::select("select * from table(pack_new_clife_clientes.f_datos_dependiente(?,?,?,?))", [$empresa,$salon,$local,$dependiente]);
        if(count($datos) > 0) {
            $datos = $datos[0];
            $datos->key = encrypt(implode(STRING_SEPARATOR, [$empresa, $salon, $local, $dependiente]));
            $dependiente = $datos;
            return response()->json([
                "status" => true,
                "data" => compact("dependiente", "key"),
                "rqid" => REQUEST_AUTH_DEPENDIENTE
            ]);
        }
        return response()->json([
            "status" => false,
            "message" => "No se encontraron dependientes con los datos ingresados",
            "rqid" => REQUEST_AUTH_DEPENDIENTE
        ]);
    }

    public function info_dependiente(Request $request) {
        list($empresa,$salon,$local,$dependiente) = explode(STRING_SEPARATOR,decrypt($request->get("key")));
        $datos = DB::select("select pack_new_clife_clientes.f_info_dependiente(?,?,?,?) data from dual", [$empresa,$salon,$local,$dependiente]);
        list($nombre, $puntos) = explode(STRING_SEPARATOR, $datos[0]->data);
        $puntos = (int) $puntos;
        return response()->json([
            "status" => true,
            "data" => compact("puntos", "nombre"),
            "rqid" => REQUEST_INFO_DEPENDIENTE
        ]);
    }

    public function info_reparticion_puntos_local(Request $request) {
        DB::statement("alter session set nls_date_format = 'dd/mm/yyyy'");
        $cEmpresa = 11;
        $cSalon = $request->get("salon");
        $locales = DB::table("sl_local_m")
            ->select(
                "co_local as local",
                "de_nombre as nombre",
                "fe_registro as registro",
                "nu_stock_puntos as puntos"
            )
            ->where("co_salon", $cSalon)
            ->where("co_empresa", $cEmpresa)
            ->where("es_vigencia", "Vigente")
            ->get();
        $puntaje = DB::table("sl_salon_m")
            ->where("co_salon", $cSalon)
            ->where("co_empresa", $cEmpresa)
            ->select("nu_stock_puntos as puntaje")
            ->first()
            ->puntaje;
        return response()->json([
            "status" => true,
            "data" => compact("locales", "puntaje"),
            "rqid" => REQUEST_INFO_REPARTE_PUNTOS
        ]);
    }

    public function asigna_puntos_local(Request $request) {
        $empresa = 11;
        $salon = $request->get("salon");
        $local = $request->get("local");
        $puntos = $request->get("puntos");
        DB::statement("call pack_new_clife_clientes.sp_asigna_puntos_local(?,?,?,?)", [$empresa,$salon,$local,$puntos]);
        $locales = DB::table("sl_local_m")
            ->select(
                "co_local as local",
                "de_nombre as nombre",
                "fe_registro as registro",
                "nu_stock_puntos as puntos"
            )
            ->where("co_salon", $salon)
            ->where("co_empresa", $empresa)
            ->where("es_vigencia", "Vigente")
            ->get();
        $puntaje = DB::table("sl_salon_m")
            ->where("co_salon", $salon)
            ->where("co_empresa", $empresa)
            ->select("nu_stock_puntos as puntaje")
            ->first()
            ->puntaje;
        return response()->json([
            "status" => true,
            "data" => compact("locales", "puntaje"),
            "rqid" => REQUEST_ASIGNA_PUNTAJE
        ]);
    }

    public function info_asignar_puntos_dependiente(Request $request) {
        $key = $request->get("key");
        list($empresa,$salon,$local,$dependiente) = explode(STRING_SEPARATOR,decrypt($key));
        $datos = DB::select("select pack_new_clife_clientes.f_info_dependiente(?,?,?,?) data from dual", [$empresa,$salon,$local,$dependiente]);
        list($nombre, $puntos) = explode(STRING_SEPARATOR, $datos[0]->data);
        $puntos = DB::table("sl_salon_m")
            ->where("co_salon", $salon)
            ->where("co_empresa", $empresa)
            ->select("nu_stock_puntos as puntaje")
            ->first()
            ->puntaje;
        return response()->json([
            "status" => true,
            "data" => compact("puntos", "nombre", "key"),
            "rqid" => REQUEST_INFO_ASIGNAR_PTS_DEP
        ]);
    }

    public function asignar_puntos_dependiente(Request $request) {
        $puntos = $request->get("puntos");
        $key = $request->get("key");
        $cUsuario = $request->get("codigo");
        $cDescripcion = "Por venta de productos";
        list($empresa,$salon,$local,$dependiente) = explode(STRING_SEPARATOR,decrypt($key));
        try {
            $pdo = DB::getPdo();
            $stmt = $pdo->prepare("call pack_new_clife_clientes.sp_asigna_puntos_dependiente(:p_empresa,:p_salon,:p_local,:p_dependiente,:p_puntos,:p_usuario,:p_descripcion,:o_codigo,:o_mensaje)");
                $stmt->bindParam(":p_empresa", $empresa, \PDO::PARAM_INT);
                $stmt->bindParam(":p_salon", $salon, \PDO::PARAM_STR);
                $stmt->bindParam(":p_local", $local, \PDO::PARAM_INT);
                $stmt->bindParam(":p_dependiente", $dependiente, \PDO::PARAM_INT);
                $stmt->bindParam(":p_puntos", $puntos, \PDO::PARAM_INT);
                $stmt->bindParam(":p_usuario", $cUsuario, \PDO::PARAM_INT);
                $stmt->bindParam(":p_descripcion", $cDescripcion, \PDO::PARAM_STR);
                $stmt->bindParam(":o_codigo", $p_codigo, \PDO::PARAM_INPUT_OUTPUT);
                $stmt->bindParam(":o_mensaje", $p_mensaje, \PDO::PARAM_STR|\PDO::PARAM_INPUT_OUTPUT, 4000);
            $stmt->execute();
            if($p_codigo == 1) {
                return response()->json([
                    "status" => true,
                    "rqid" => REQUEST_ASIGNA_PTS_DEP
                ]);
            }
            else return response()->json([
                "status" => false,
                "message" => $p_mensaje,
                "key" => $key,
                "rqid" => REQUEST_ASIGNA_PTS_DEP
            ]);
        }
        catch(\PDOException $e) {
            return $e;
        }
    }

    public function stock_puntos(Request $request) {
        $cEmpresa = 11;
        $salon = $request->get("salon");
        $local = $request->get("local");
        if($local == 0) {
            $puntaje = DB::table("sl_salon_m")
                ->where("co_empresa", $cEmpresa)
                ->where("co_salon", $salon)
                ->select("nu_stock_puntos as puntaje")
                ->first()
                ->puntaje;
            $dependientes = 0;
        }
        else {
            $puntaje = DB::table("sl_local_m")
                ->where("co_empresa", $cEmpresa)
                ->where("co_salon", $salon)
                ->where("co_local", $local)
                ->select("nu_stock_puntos as puntaje")
                ->first()
                ->puntaje;
            $dependientes = DB::table("sl_personal_local_m")
                ->where("co_empresa", $cEmpresa)
                ->where("co_salon", $salon)
                ->where("co_local", $local)
                ->where("es_vigencia", "Vigente")
                ->count();
        }
        return response()->json([
            "status" => true,
            "data" => compact("puntaje","dependientes"),
            "rqid" => REQUEST_STOCK_PUNTAJE
        ]);
    }

    public function premios_disponibles(Request $request) {
        $empresa = 11;
        $tipo = $request->get("tipo");
        DB::statement("alter session set nls_date_format = 'dd/mm/yyyy'");
        $premios = DB::select("select * from table(pack_new_clife_clientes.f_lista_premios(?,?))", [$empresa,$tipo]);
        return response()->json([
            "status" => true,
            "data" => compact("premios"),
            "rqid" => REQUEST_LISTA_PREMIOS
        ]);
    }

    public function descripcion_premio(Request $request) {
        $empresa = 11;
        $campania = $request->get("campania");
        $producto = $request->get("producto");
        $salon = $request->get("salon");
        $local = $request->get("local");
        $dependiente = $request->get("dependiente");
        $tipo = $request->get("tipo");
        $premio = DB::select("select * from table(pack_new_clife_clientes.f_detalle_premio(?,?,?))", [$empresa,$campania,$producto]);
        if(strcmp($tipo,"D") == 0) {
            $puntaje = (int) DB::table("sl_personal_local_m")
                ->where("co_empresa", $empresa)
                ->where("co_salon", $salon)
                ->where("co_local", $local)
                ->where("co_personal_salon", $dependiente)
                ->where("es_vigencia", "Vigente")
                ->select("nu_puntos as puntaje")
                ->first()
                ->puntaje;
        }
        else {
            $puntaje = (int) DB::table("sl_salon_m")
                ->where("co_empresa", $empresa)
                ->where("co_salon", $salon)
                ->select("nu_stock_puntos as puntaje")
                ->first()
                ->puntaje;
        }
        if(count($premio) > 0) {
            $premio = $premio[0];
            return response()->json([
                "status" => true,
                "data" => compact("premio", "puntaje"),
                "rqid" => REQUEST_DETALLE_PREMIOS
            ]);
        }
        return response()->json([
            "status" => false,
            "message" => "El premio que buscas es incorrecto",
            "rqid" => REQUEST_DETALLE_PREMIOS
        ]);
    }

    public function reclamar_premio(Request $request) {
        $empresa = 11;
        $tipo = $request->get("tipo");
        $salon = $request->get("salon");
        $local = $request->get("local");
        $dependiente = $request->get("dependiente");
        $campania = $request->get("campania");
        $producto = $request->get("producto");
        $direccion = $request->get("direccion");
        $telefono = $request->get("telefono");
        $codireccion = $request->get("codireccion");
        if(strcmp($tipo,"D") == 0) {
            $arr_to_insert = [
                "co_campania" => $campania,
                "co_empresa" => $empresa,
                "co_catalogo_producto" => $producto,
                "co_salon" => $salon,
                "co_local" => $local,
                "co_dependiente" => $dependiente,
                "de_direccion_envio" => $direccion,
                "de_telefono_contacto" => $telefono
            ];
        }
        else {
            $arr_to_insert = [
                "co_empresa" => $empresa,
                "co_catalogo_producto" => $producto,
                "co_campania" => $campania,
                "co_salon" => $salon,
                "de_direccion_envio" => $direccion,
                "de_telefono_contacto" => $telefono
            ];
        }
        $puntos = (int) DB::table("vt_campania_obsequios_d")
            ->where("co_campania", $campania)
            ->where("co_empresa", $empresa)
            ->where("co_catalogo_producto", $producto)
            ->select("nu_puntos as costo")
            ->first()
            ->costo;
        DB::table("vt_campania_obsequios_regalo")->insert($arr_to_insert);
        DB::table("vt_campania_obsequios_d")
            ->where("co_campania", $campania)
            ->where("co_empresa", $empresa)
            ->where("co_catalogo_producto", $producto)
            ->increment("nu_stock", -1);
        //genera el pinche pedido
        $dvendedor = DB::select("select sgum.co_usuario vendedor, sgum.de_alias alias
            from (select co_vendedor,fe_facturacion,co_empresa from ma_file_fisi_vent_c where co_cliente = ? and co_empresa = ? order by fe_facturacion desc) T
                join sg_usua_m sgum on T.co_vendedor = sgum.co_usuario and T.co_empresa = sgum.co_empresa_usuario
            where rownum = 1", [$salon, $empresa]);
        $dvendedor = $dvendedor[0];
        DB::statement("call pack_venta.sm_activar_empresa(?)",[$dvendedor->alias]);
        $p_fventa = DB::select("select codigo from table(ventas.f_v_fza_vta_wap(?,?))", [$dvendedor->alias,$salon]);
            $p_fventa = $p_fventa[0]->codigo;
        $p_pventa = DB::select("select codigo from table(ventas.f_v_pto_wap(?))", [$dvendedor->alias]);
            $p_pventa = $p_pventa[0]->codigo;
        //genera la cabecera del pedido
        $xdata = implode("@", [$dvendedor->alias,$salon,1000,$p_fventa,1,$codireccion,$p_pventa,4,0,1000,"-"]);
        DB::statement("call ventas_2.sp_cab_pedido(?)", [$xdata]);
        $p_cpago = 1;
        $p_comprobante = "B";
        $p_fecha = date("dmy", strtotime(date("Y-m-d") . ' + 2 days'));
        DB::statement("call ventas_1.sp_precios_wap(?,?,?,?)",[$dvendedor->alias,$p_fecha,$p_cpago,$p_comprobante]);
        //agrega el producto
        $xdata = implode("@", [$dvendedor->alias,$producto,1,"L",0,0,0]);
        DB::statement("call pack_new_clife_clientes.sp_inserta_producto(?)", [$xdata]);
        //metele el titulo gratuito
        $dpedido = DB::select("select ventas_1.fv_tot_pedido(?) \"dpedido\" from dual", [$dvendedor->alias]);
        $dpedido = explode("@", $dpedido[0]->dpedido)[3];
        DB::table("vt_pedi_t")
            ->where("co_pedido", $dpedido)
            ->where("co_empresa", $empresa)
            ->update([
                "co_tipo_afec_igv" => 21
            ]);
        //cierra el pedido
        DB::statement("call ventas_10.sp_cierre_pedido1(?,?)", [$dvendedor->alias,0]);
        //envia el mail
        if(strcmp($tipo,"D") == 0) {
            $usr = DB::select("select mcem.de_razon_social nombre,spl.de_email mail
                from sl_personal_local_m spl
                join ma_cata_enti_m mcem on spl.co_personal_salon = mcem.co_catalogo_entidad
                where spl.co_salon = ? and spl.co_local = ? and spl.co_personal_salon = ?", [$salon, $local, $dependiente]);
            $usr = $usr[0];
            $nombre = $usr->nombre;
            $demail = $usr->mail;
            $premio = DB::table("ma_cata_prod_m")
                ->where("co_catalogo_producto", $producto)
                ->select("de_nombre as producto")
                ->first();
            $premio = $premio->producto;
            \Mail::send("emails.regalo", compact("nombre","premio","telefono","direccion"), function($message) use($demail) {
                $message->from(env("MAIL_FROM_ADDRESS"), env("MAIL_FROM_NAME"));
                //$message->to($demail)->subject("Canje de premio");
                $message->to("mvelasquez@corporacionlife.com.pe")->subject("Canje de premio");
            });
        }
        else {
            $usuario = User::find($salon);
            $nombre = $usuario->de_razon_social;
            $premio = DB::table("ma_cata_prod_m")
                ->where("co_catalogo_producto", $producto)
                ->select("de_nombre as producto")
                ->first();
            $premio = $premio->producto;
            \Mail::send("emails.regalo", compact("nombre","premio","telefono","direccion"), function($message) use($usuario) {
                $message->from(env("MAIL_FROM_ADDRESS"), env("MAIL_FROM_NAME"));
                //$message->to($usuario->de_email)->subject("Canje de premio");
                $message->to("mvelasquez@corporacionlife.com.pe")->subject("Canje de premio");
            });
        }
        //tambien se debe enviar un correo a soporte comercial
        //fin
        if(strcmp($tipo,"D") == 0) {
            DB::table("sl_personal_local_m")
                ->where("co_empresa", $empresa)
                ->where("co_salon", $salon)
                ->where("co_local", $local)
                ->where("co_personal_salon", $dependiente)
                ->increment("nu_puntos", -1 * $puntos);
        }
        else {
            DB::table("sl_salon_m")
                ->where("co_empresa", $empresa)
                ->where("co_salon", $salon)
                ->increment("nu_stock_puntos", -1 * $puntos);
        }
        return response()->json([
            "status" => true,
            "rqid" => REQUEST_RECLAMA_PREMIOS
        ]);
    }

    public function direccion_local(Request $request) {
        $empresa = 11;
        $salon = $request->get("salon");
        $local = $request->get("local");
        //hay q corregir la asignacion de direcciones, siempre va a cargar la direccion pricipal de ma_dire_enti_m
        $direccion = DB::table("ma_dire_enti_m")
            ->where("co_catalogo_entidad", $salon)
            ->where("st_erased", 0)
            ->where("es_registro", "Vigente")
            ->select("de_direccion_sunat as descripcion", "co_direccion_entidad as codigo")
            ->get();
        if(count($direccion) > 0) {
            $direccion = $direccion[0];
        }
        return response()->json([
            "status" => true,
            "rqid" => REQUEST_CARGA_DIRECCION,
            "data" => compact("direccion")
        ]);
    }

    public function detalle_factura(Request $request) {
        $empresa = 11;
        $documento = $request->get("documento");
        $pedido = DB::table("ma_file_fisi_vent_c as mffvc")
            ->select(
                "mffvc.co_pedido as pedido",
                "mffvc.co_vendedor as cvendedor",
                "mffvc.im_total as importe",
                "mffvc.fe_facturacion as fecha",
                "mcem.de_razon_social as nvendedor"
            )
            ->join("ma_cata_enti_m as mcem", "mffvc.co_vendedor", "=", "mcem.co_catalogo_entidad")
            ->where("mffvc.co_empresa", $empresa)
            ->where("mffvc.co_file_fisico_venta_final", $documento)
            ->first();
        $detalle = DB::select("select * from table(pack_new_clife_clientes.f_detalle_pedido(?,?))", [$documento,$empresa]);
        return response()->json([
            "status" => true,
            "data" => compact("pedido","detalle"),
            "rqid" => REQUEST_DETALLE_FACTURA
        ]);
    }

    public function guarda_calificacion_pedido(Request $request) {
        $empresa = 11;
        $cvendedor = (int) $request->get("cvendedor");
        $cproductos = (int) $request->get("cproductos");
        $cenvio = (int) $request->get("cenvio");
        $calificacion_promedio = ($cvendedor + $cproductos + $cenvio) / 3;
        DB::table("vt_pedi_calificacion_t")->insert([
            "co_pedido" => $request->get("pedido"),
            "co_empresa" => $empresa,
            "co_cliente" => $request->get("cliente"),
            "co_vendedor" => $request->get("vendedor"),
            "de_comentarios" => $request->get("comentarios"),
            "nu_calif_vendedor" => $cvendedor,
            "nu_calif_productos" => $cproductos,
            "nu_calif_envio" => $cenvio,
            "nu_calif_general" => $calificacion_promedio,
            "fe_calif_cliente" => date("Y-m-d H:i:s"),
            "fe_registro" => date("Y-m-d H:i:s")
        ]);
        return response()->json([
            "status" => true,
            "rqid" => REQUEST_CALIFICA_PEDIDO
        ]);
    }

    public function procesar_pago_culkk(Request $request) {
        include_once dirname(__FILE__) . "/../../../vendor/rmccue/requests/library/Requests.php";
        \Requests::register_autoloader();
        include_once dirname(__FILE__) . "/../../../vendor/culqi/culqi-php/lib/culqi.php";
        header("Content-Type: application/json");
        $token = $request->get("ctoken");
        $importe = round((double) $request->get("importe"),2);
        $descripcion = $request->get("descripcion");
        $email = $request->get("email");
        $factura = $request->get("factura");
        $cliente = $request->get("cliente");
        $empresa = $request->get("empresa");
        //
        $imCulqui = round($importe * 100);
        $culqi = new \Culqi\Culqi([
            "api_key" => env("APP_CULQUI_SECRET_KEY")
        ]);
        try {
            $charge = $culqi->Charges->create([
                "amount" => $imCulqui,
                "currency_code" => "PEN",
                "email" => $email,
                "source_id" => $token,
                "description" => $descripcion,
                "metadata" => [
                    "order_id" => $factura,
                    "user_id" => $cliente,
                ]
            ]);
        }
        catch(\Exception $exception) {
            $result = json_decode($exception->getMessage());
            return response()->json([
                "status" => false,
                "message" => $result,
                "rqid" => REQUEST_PROCESA_PAGO
            ]);
        }
        //continua con el registro en BD
        //carga el vendedor
        $rRecaudador = DB::table("ba_cuen_corr_admi_clie_d")
            ->where("co_empresa", $empresa)
            ->where("co_documento", $factura)
            ->select("co_vendedor as codigo", "co_empresa as empresa")
            ->first();
        $rVendedor = DB::table("sg_usua_m")
            ->where("co_empresa_usuario", $rRecaudador->empresa)
            ->where("co_usuario", $rRecaudador->codigo)
            ->select("de_alias as alias", "co_usuario as codigo")
            ->first();
        //loguea
        $arr_pack_venta = [$rVendedor->alias];
        DB::statement("call pack_venta.sm_activar_empresa(?)", $arr_pack_venta);
        //guarda el id del cargo en la tabla temporal
        DB::table("temp_ma_empresa")->update([
            "co_cuenta_corriente" => $charge->id
        ]);
        //abre la planilla
        $xenvia = implode("@",[$rVendedor->alias,1,54,1,0,91]);
        $arr_pack_cobranza = [$xenvia];
        DB::statement("call pack_cobranza.co_gen_planilla_wap(?)", $arr_pack_cobranza);
        //registra en la planilla
        $tpCobroCulqi = 13;
        $arr_pack_wap = [$rVendedor->alias, $factura, $importe, $tpCobroCulqi];
        DB::statement("call pack_wap_co.co_det_planilla_wap(?,?,?,?)", $arr_pack_wap);
//registra el deposito
/*$coctacte = "194-1573143-0-32";
$cocomp = DB::table("ba_depo_plan_cobr_m")
    ->where("co_empresa", $empresa)
    ->where("co_planilla_cobranza", "like", "LQ%")
    ->select(
        DB::raw("nvl(max(co_comprobante),0) as comprobante")
    )
    ->first()
    ->comprobante;
$coplanilla = DB::table("ba_plan_cobr_t")
    ->select("co_planilla_cobranza as planilla")
    ->where("co_empresa", $empresa)
    ->where("co_recaudador", $rVendedor->codigo)
    ->where("co_serie", 1067)
    ->where("es_vigencia", "Vigente")
    ->first()
    ->planilla;
$cocomp = ((int) $cocomp) + 1;
$fecha = date("d/m/Y");
$ls_data = implode("@", [$rVendedor->alias, $cocomp, $coctacte, $importe, $fecha, 153, $coplanilla]);
DB::statement("call pack_cobranza2.co_det_planilla_dep(?)", [$ls_data]);*/
        //fin BD
        return response()->json([
            "status" => true,
            "data" => $charge,
            //"data" => compact("rRecaudador","rVendedor","arr_pack_venta","arr_pack_cobranza","arr_pack_wap"),
            "rqid" => REQUEST_PROCESA_PAGO
        ]);
    }

    public function consulta_cargo() {
        include_once dirname(__FILE__) . "/../../../vendor/rmccue/requests/library/Requests.php";
        \Requests::register_autoloader();
        include_once dirname(__FILE__) . "/../../../vendor/culqi/culqi-php/lib/culqi.php";
        header("Content-Type: application/json");
        $culqi = new \Culqi\Culqi([
            "api_key" => env("APP_CULQUI_SECRET_KEY")
        ]);
        $cargo = $culqi->Charges->get("chr_test_i35LUj78NpPJcJSE");
        return response()->json([
            "cargo" => $cargo
        ]);
    }

    public function info_perfil(Request $request) {
        $empresa = $request->get("empresa");
        $tipo = $request->get("tipo");
        DB::statement("alter session set nls_date_format = 'dd/mm/yyyy'");
        if(strcmp($tipo, "S") == 0) {
            $cliente = $request->get("codigo");
            $dcliente = DB::table("cl_usuarios")
                ->where("co_rucdni", $cliente)
                ->select("de_email as email","de_telefono as telefono","fe_nacimiento as fnacimiento")
                ->first();
            $dvendedor = DB::select("select
                    sgum.co_usuario vendedor,
                    sgum.de_nombre nombre,
                    nvl(sgum.de_telefono,'-') telefono
                from (select co_vendedor,fe_facturacion,co_empresa from ma_file_fisi_vent_c where co_cliente = ? and co_empresa = ? order by fe_facturacion desc) T
                    join sg_usua_m sgum on T.co_vendedor = sgum.co_usuario and T.co_empresa = sgum.co_empresa_usuario
                where rownum = 1", [$cliente, $empresa]);
            $dvendedor = $dvendedor[0];
        }
        else {
            $key = $request->get("key");
            list($kempresa,$ksalon,$klocal,$kdependiente) = explode(STRING_SEPARATOR,decrypt($key));
            $dcliente = DB::table("sl_personal_local_m")
                ->where("co_empresa", $kempresa)
                ->where("co_salon", $ksalon)
                ->where("co_local", $klocal)
                ->where("co_personal_salon", $kdependiente)
                ->where("es_vigencia", "Vigente")
                ->select("de_email as email","de_telefono as telefono","fe_cumpleanios as fnacimiento")
                ->first();
            $dvendedor = [];
        }
        return response()->json([
            "status" => true,
            "data" => [
                "cliente" => $dcliente,
                "vendedor" => $dvendedor
            ],
            "rqid" => REQUEST_INFO_PERFIL
        ]);
    }

    public function lista_logros(Request $request) {
        $privilegios = $request->get("tipo");
        $cliente = $request->get("cliente");
        $empresa = $request->get("empresa");
        $logros = DB::select("select * from table(pack_new_clife_clientes.f_logros_usuario(?,?,?))", [$cliente, $empresa, $privilegios]);
        return response()->json([
            "status" => true,
            "data" => compact("logros"),
            "rqid" => REQUEST_LISTA_LOGROS
        ]);
    }

    public function reclamar_logro(Request $request) {
        $logro = $request->get("logro");
        $cliente = $request->get("cliente");
        $empresa = $request->get("empresa");
        $tipo = $request->get("tipo");
        DB::table("cl_logros_usuario_c")
            ->where("co_logro", $logro)
            ->where("co_usuario", $cliente)
            ->where("co_empresa", $empresa)
            ->update([
                "st_reclamado" => "S"
            ]);
        //aumenta los puntos
        $puntos = DB::table("cl_catalogo_logros_c")
            ->where("co_logro", $logro)
            ->where("co_empresa", $empresa)
            ->select("nu_puntos as cantidad")
            ->first();
        if(strcmp($tipo,"D") == 0) {
            DB::table("sl_personal_local_m")
                ->where("co_personal_salon", $cliente)
                ->where("es_vigencia", "Vigente")
                ->increment("nu_puntos", $puntos->cantidad);
        }
        else {
            DB::table("sl_salon_m")
                ->where("co_salon", $cliente)
                ->increment("nu_stock_puntos", $puntos->cantidad);
        }
        return response()->json([
            "status" => true,
            "rqid" => REQUEST_RECLAMA_LOGRO
        ]);
    }

    public function cambiar_permisos_dependiente(Request $request) {
        $cEmpresa = 11;
        $salon = $request->get("salon");
        $local = $request->get("local");
        $dependiente = $request->get("dependiente");
        $forzar = strcmp($request->get("forzar"),"S") == 0;
        //aqui haz tu magia
        $contador = DB::table("sl_personal_local_m")
            ->where("co_empresa", $cEmpresa)
            ->where("co_salon", $salon)
            ->where("co_local", $local)
            ->where("es_vigencia", "Vigente")
            ->where("tp_tipo_personal", "A")
            ->count();
        if($contador == 0 || $forzar) {
            //retira privilegios al administrador vigente
            DB::table("sl_personal_local_m")
                ->where("co_empresa", $cEmpresa)
                ->where("co_salon", $salon)
                ->where("co_local", $local)
                ->where("es_vigencia", "Vigente")
                ->where("tp_tipo_personal", "A")
                ->update([
                    "tp_tipo_personal" => "D"
                ]);
            //otorga privilegios al dependiente actual
            DB::table("sl_personal_local_m")
                ->where("co_empresa", $cEmpresa)
                ->where("co_salon", $salon)
                ->where("co_local", $local)
                ->where("co_personal_salon", $dependiente)
                ->where("es_vigencia", "Vigente")
                ->update([
                    "tp_tipo_personal" => "A"
                ]);
            //devuelve los datos del dependiente actual
            $datos = DB::select("select * from table(pack_new_clife_clientes.f_datos_dependiente(?,?,?,?))", [$cEmpresa,$salon,$local,$dependiente]);
            $datos = $datos[0];
            $datos->key = encrypt(implode(STRING_SEPARATOR, [$datos->empresa,$datos->salon,$datos->clocal,$datos->codigo]));
            $result = "S";
            return response()->json([
                "status" => true,
                "data" => compact("datos", "result"),
                "rqid" => REQUEST_ACTUALIZA_PRIVILEGIOS
            ]);
        }
        $result = "N";
        return response()->json([
            "status" => true,
            "data" => compact("result"),
            "rqid" => REQUEST_ACTUALIZA_PRIVILEGIOS
        ]);
    }

    //registro desde la app

    public function validar_ruc_cliente(Request $request) {
        $empresa = 11;
        $rucdni = $request->get("rucdni");
        $cliente = DB::select("select initcap(vcm.co_cliente) \"rucdni\",
                initcap(vcm.de_razon_social) \"rzsocial\",
                initcap(nvl(mdem.de_direccion_sunat,vde.de_direccion)) \"direccion\",
                vcm.st_programa_puntos \"puntos\"
            from vt_clie_m vcm
                join ma_dire_enti_m mdem on vcm.co_cliente = mdem.co_catalogo_entidad
                join v_dire_entidad vde on mdem.co_direccion_entidad = vde.co_direccion_entidad
            where vcm.co_cliente = ?
                and vcm.co_empresa = ?", [$rucdni, $empresa]);
        if(count($cliente) > 0) {
            $cliente = $cliente[0];
            //busca el usuario
            $encontrado = DB::table("cl_usuarios")
                ->where("co_cliente", $cliente->rucdni)
                ->where("co_empresa", $empresa)
                ->count();
            return response()->json([
                "status" => true,
                "data" => compact("cliente", "encontrado"),
                "rqid" => REQUEST_RUCDNI_CLIENTE
            ]);
        }
        return response()->json([
            "status" => false,
            "message" => "No se encontró cliente",
            "rqid" => REQUEST_RUCDNI_CLIENTE
        ]);
    }

    public function cargar_info_cliente(Request $request) {
        $empresa = 11;
        $rucdni = $request->get("rucdni");
        $cliente = DB::select("select
                mcem.de_razon_social \"rzsocial\",
                mcem.de_nombre_comercial \"ncomercial\",
                nvl(mdem.de_direccion_sunat,vde.de_direccion) \"direccion\",
                nvl(vcm.de_email,'') \"email\",
                nvl(vcm.de_telefonos,'') \"telefono\",
                vcm.st_programa_puntos \"puntos\"
            from ma_cata_enti_m mcem
                join ma_dire_enti_m mdem on mcem.co_catalogo_entidad = mdem.co_catalogo_entidad
                join v_dire_entidad vde on mdem.co_direccion_entidad = vde.co_direccion_entidad
                join vt_clie_m vcm on mcem.co_catalogo_entidad = vcm.co_cliente
            where vcm.co_empresa = ?
                and mcem.co_catalogo_entidad = ?", [$empresa, $rucdni]);
        if(count($cliente) > 0) {
            $cliente = $cliente[0];
            return response()->json([
                "status" => true,
                "data" => compact("cliente"),
                "rqid" => REQUEST_DATOS_CLIENTE
            ]);
        }
        return response()->json([
            "status" => false,
            "message" => "No se encontró cliente",
            "rqid" => REQUEST_DATOS_CLIENTE
        ]);
    }

    public function guardar_info_cliente(Request $request) {
        $empresa = 11;
        $rucdni = $request->get("rucdni");
        $email = $request->get("email");
        $telefono = $request->get("telefono");
        $cumpleanios = $request->get("cumpleanios");
        $clave = $request->get("clave");
        //reconstruye fecha de cumpleaños
        $cumpleanios = explode("-", $cumpleanios);
        $cumpleaños = implode("-", array_reverse($cumpleanios)) . " 00:00:00";
        //carga datos del usuario
        $rEntidad = DB::table("ma_cata_enti_m")
            ->where("co_catalogo_entidad", $rucdni)
            ->select(
                "de_razon_social as rsocial",
                "de_nombre_comercial as ncomercial",
                "co_catalogo_entidad as codigo"
            )
            ->first();
        $rCliente = DB::table("vt_clie_m")
            ->where("co_cliente", $rucdni)
            ->where("co_empresa", $empresa)
            ->select("st_programa_puntos as puntos")
            ->first();
        //registra al usuario alv!
        DB::table("cl_usuarios")->insert([
            "co_cliente" => $rucdni,
            "co_empresa" => $empresa,
            "de_nombre_comercial" => $rEntidad->ncomercial,
            "de_razon_social" => $rEntidad->rsocial,
            "co_rucdni" => $rEntidad->codigo,
            "de_email" => $email,
            "de_telefono" => $telefono,
            "st_puntos" => $rCliente->puntos,
            "password" => bcrypt($clave),
        ]);
        //registra el salon
        DB::table("sl_salon_m")->insert([
            "co_salon" => $rEntidad->codigo,
            "co_empresa" => $empresa,
            "de_descripcion" => $rEntidad->ncomercial,
            "fe_registro" => date("Y-m-d H:i:s"),
            "st_imagen" => "N",
            "nu_stock_puntos" => 0
        ]);
        //registra un local
        DB::table("sl_local_m")->insert([
            "co_local" => 1,
            "co_salon" => $rEntidad->codigo,
            "co_empresa" => $empresa,
            "de_nombre" => $rEntidad->ncomercial
        ]);
        //carga datos del usuario
        $usuario = User::find($rucdni);
        //envia el pinche email
        $key = implode("@",[$usuario->co_empresa, $usuario->co_rucdni]);
        \Mail::send("emails.confirma", compact("usuario","key"), function($message) use($usuario) {
            $message->from(env("MAIL_FROM_ADDRESS"), env("MAIL_FROM_NAME"));
            $message->to($usuario->de_email)->subject("Activar tu cuenta CLife");
        });
        //
        return response()->json([
            "status" => true,
            "data" => compact("email"),
            "rqid" => REQUEST_GUARDAR_INFO_CLIENTE
        ]);
    }

    //

    public function prueba_mail() {
        \Mail::send("emails.prueba", [], function($message) {
            $message->from(env("MAIL_FROM_ADDRESS"), env("MAIL_FROM_NAME"));
            $message->to("mvelasquezp88@gmail.com")->subject("Prueba mailer");
        });
        return "prueba exitosa";
    }

    public function getAuthUser(Request $request) {
        $user = JWTAuth::toUser($request->token);
        return response()->json(['result' => $user]);
    }
}