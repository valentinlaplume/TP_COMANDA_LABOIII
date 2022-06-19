<?php
date_default_timezone_set("America/Buenos_Aires");
require_once './models/Producto.php';
require_once './models/ProductoTipo.php';
require_once './models/Area.php';
require_once './models/UsuarioAccionTipo.php';

require_once './interfaces/IApiUsable.php';

use \App\Models\Area as Area;
use \App\Models\Producto as Producto;
use \App\Models\ProductoTipo as ProductoTipo;
use \App\Models\UsuarioAccionTipo as UsuarioAccionTipo;
use Illuminate\Database\Capsule\Manager as DB;
class ProductoController implements IApiUsable
{
  public function GetAll($request, $response, $args)
  {
    $lista = Producto::all();
    $payload = json_encode(array("listaProducto" => $lista));

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }

  public function GetAllBy($request, $response, $args)
  {
    $field = $args['field'];
    $value = $args['value'];

    $lista = Producto::where($field, $value)->get();

    $payload = json_encode(array("listaProducto" => $lista));

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }

  public function GetFirstBy($request, $response, $args)
  {
    $field = $args['field'];
    $value = $args['value'];

    $obj = Producto::where($field, $value)->first();

    $payload = json_encode($obj);

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }

  private static function ValidateInputData($data){
    if($data == null) { throw new Exception("No se encontró datos de entrada"); }

    if (!isset($data['idArea'])) { throw new Exception("idArea no seteado"); }
    else if(Area::find($data['idArea']) == null) { throw new Exception("No existe idArea indicada"); }

    if (!isset($data['idProductoTipo'])) { throw new Exception("idProductoTipo no seteado"); }
    else if(ProductoTipo::find($data['idProductoTipo']) == null) { throw new Exception("No existe idProductoTipo indicado"); }

    if (!isset($data['nombre'])) { throw new Exception("Nombre no seteado"); }
    if (!isset($data['precio'])) { throw new Exception("Precio no seteado"); }
    if (!isset($data['stock'])) { throw new Exception("Stock no seteado"); }

    if (floatval($data['precio']) < 0) { throw new Exception("Precio indicado debe ser '>' o '=' a 0"); }
    if (intval($data['stock']) < 0) { throw new Exception("Stock indicado debe ser '>' o '=' a 0"); }
  }

  public function Save($request, $response, $args)
  {
    try
    {
      $idUsuarioLogeado = AutentificadorJWT::GetUsuarioLogeado($request)->id;
      $data = $request->getParsedBody();

      self::ValidateInputData($data);

      $obj = new Producto();
      $obj->idArea = $data['idArea'];
      $obj->idProductoTipo = $data['idProductoTipo'];
      $obj->nombre = $data['nombre'];
      $obj->precio = floatval($data['precio']);
      $obj->stock = intval($data['stock']);
      $obj->save();
      
      $payload = json_encode(
      array(
      "mensaje" => "Producto creado con éxito",
      "idUsuario" => $idUsuarioLogeado,
      "idUsuarioAccionTipo" => UsuarioAccionTipo::Alta,
      "idPedido" => null, 
      "idPedidoDetalle" => null, 
      "idMesa" => null, 
      "idProducto" => $obj->id, 
      "idArea" => null,
      "hora" => date('h:i:s'))
      );
      
      $response->getBody()->write($payload);
      return $response->withHeader('Content-Type', 'application/json');
    }
    catch(Exception $e){
      $response = $response->withStatus(401);
      $response->getBody()->write(json_encode(array('error' => $e->getMessage())));
      return $response->withHeader('Content-Type', 'application/json');
    }
  }

  public function Update($request, $response, $args)
  {
    try
    {
      $obj = Producto::find($args['id']);
      if($obj == null) { throw new Exception('Producto no encontrado.'); }
      $idUsuarioLogeado = AutentificadorJWT::GetUsuarioLogeado($request)->id;

      $data = $request->getParsedBody();

      if(isset($data['idArea'])) { 
        if(Area::find($data['idArea']) == null) { throw new Exception("No existe idArea indicada"); }
        $obj->idArea = $data['idArea']; 
      }

      if(isset($data['idProductoTipo'])) { 
        if(ProductoTipo::find($data['idProductoTipo']) == null) { throw new Exception("No existe idProductoTipo indicado"); }
        $obj->idProductoTipo = $data['idProductoTipo']; 
      }

      if(isset($data['nombre'])) { $obj->nombre = $data['nombre']; }

      if(isset($data['precio'])) { 
        if (floatval($data['precio']) < 0) { throw new Exception("Precio indicado debe ser '>' o '=' a 0"); }
        $obj->precio = floatval($data['precio']); 
      }

      if(isset($data['stock'])) { 
        if (intval($data['stock']) < 0) { throw new Exception("Stock indicado debe ser '>' o '=' a 0"); }
        $obj->stock = intval($data['stock']); 
      }

      $obj->save();
      $payload = json_encode(
      array(
      "mensaje" => "Producto modificado con éxito",
      "idUsuario" => $idUsuarioLogeado,
      "idUsuarioAccionTipo" => UsuarioAccionTipo::Modificacion,
      "idPedido" => null, 
      "idPedidoDetalle" => null, 
      "idMesa" => null, 
      "idProducto" => $obj->id, 
      "idArea" => null,
      "hora" => date('h:i:s'))
      );
        
      $response->getBody()->write($payload);
      return $response->withHeader('Content-Type', 'application/json');
    }
    catch(Exception $e){
      $response = $response->withStatus(401);
      $response->getBody()->write(json_encode(array('error' => $e->getMessage())));
      return $response->withHeader('Content-Type', 'application/json');
    }
  }

  public function Delete($request, $response, $args)
  {
    try
    {
      $obj = Producto::find($args['id']);
      if($obj == null) { throw new Exception('Producto no encontrado.'); }
      $idUsuarioLogeado = AutentificadorJWT::GetUsuarioLogeado($request)->id;

      $obj->delete();
      $payload = json_encode(
      array(
      "mensaje" => "Producto borrado con éxito",
      "idUsuario" => $idUsuarioLogeado,
      "idUsuarioAccionTipo" => UsuarioAccionTipo::Baja,
      "idPedido" => null, 
      "idPedidoDetalle" => null, 
      "idMesa" => null, 
      "idProducto" => $obj->id, 
      "idArea" => null,
      "hora" => date('h:i:s'))
      );
          
      $response->getBody()->write($payload);
      return $response->withHeader('Content-Type', 'application/json');
    }
    catch(Exception $e){
      $response = $response->withStatus(401);
      $response->getBody()->write(json_encode(array('error' => $e->getMessage())));
      return $response->withHeader('Content-Type', 'application/json');
    }
  }
}
