<?php
require_once './models/Producto.php';
require_once './interfaces/IApiUsable.php';

use \App\Models\Producto as Producto;
use App\Models\AuditoriaAcciones;

class ProductoController implements IApiUsable
{
    public function CargarUno($request, $response, $args)
    {
        try {
            $idUsuarioLogeado = AutentificadorJWT::GetUsuarioLogeado($request)->IdUsuario;
            $body = json_decode($response->getBody());
            $header = $request->getHeaderLine('Authorization');
            $body = $request->getParsedBody();

            $nombre = $body['nombre'];
            $precio = $body['precio'];
            $tiempo = $body['tiempoEspera'];
            $area = $body['area'];
            $tipo = $body['tipo'];
            $stock = $body['stock'];

            $producto = new Producto();
            $producto->Nombre = $nombre;
            $producto->PrecioUnidad = $precio;
            $producto->TiempoEspera = $tiempo;
            $producto->Area = $area;
            $producto->TipoProducto = $tipo;
            $producto->Stock = $stock;

            $producto->save();

            $payload = json_encode(
                array(
                    "IdUsuario" => strval($idUsuarioLogeado),
                    "IdRefUsuario" => null,
                    "IdAccion" =>  strval(AuditoriaAcciones::Alta),
                    "mensaje" => "Mesa creado con éxito",
                    "IdPedido" => null,
                    "IdPedidoDetalle" => null,
                    "IdMesa" => null,
                    "IdProducto" => $producto->IdProducto,
                    "IdArea" => null,
                    "Hora" => date('h:i:s')
                )
            );

            $response->getBody()->write($payload);

            return $response->withHeader('Content-Type', 'application/json');
        } catch (Exception $e) {
            $response = $response->withStatus(401);
            $response->getBody()->write(json_encode(array('error' => $e->getMessage())));
            return $response->withHeader('Content-Type', 'application/json');
        }
    }

    public function BorrarUno($request, $response, $args)
    {
        try {

            $idUsuarioLogeado = AutentificadorJWT::GetUsuarioLogeado($request)->IdUsuario;
            $body = json_decode($response->getBody());
            $header = $request->getHeaderLine('Authorization');
            $idRecibida = $args['IdProducto'];
            $producto = App\Models\Producto::find($idRecibida);

            if ($producto != null) {
                $producto->delete();
                $payload = json_encode(
                    array(
                        "IdUsuario" => strval($idUsuarioLogeado),
                        "IdRefUsuario" => null,
                        "IdAccion" =>  strval(AuditoriaAcciones::Baja),
                        "mensaje" => "Producto borrado con éxito",
                        "IdPedido" => null,
                        "Exito" => 1,
                        "IdPedidoDetalle" => null,
                        "IdMesa" => null,
                        "IdProducto" => $producto->IdProducto,
                        "IdArea" => null,
                        "Hora" => date('h:i:s')
                    )
                );
            } else {
                $payload = json_encode(array("mensaje" => "Error al eliminar"));
            }
            $response->getBody()->write($payload);
            return $response->withHeader('Content-Type', 'application/json');
        } catch (Exception $e) {
            $response = $response->withStatus(401);
            $response->getBody()->write(json_encode(array('error' => $e->getMessage())));
            return $response->withHeader('Content-Type', 'application/json');
        }
    }

    public function ModificarUno($request, $response, $args)
    {
        try {
            $idUsuarioLogeado = AutentificadorJWT::GetUsuarioLogeado($request)->IdUsuario;
            $body = json_decode($response->getBody());
            $header = $request->getHeaderLine('Authorization');
            $id = $args['IdProducto'];

            $producto = App\Models\Producto::where('IdProducto', '=', $id)->first();

            $body = json_decode(file_get_contents("php://input"), true);

            if ($producto != null) {
                $nombre = $body['nombre'];
                $precio = $body['precio'];
                $tiempo = $body['tiempo'];
                $area = $body['area'];
                $tipo = $body['tipo'];
                $stock = $body['stock'];

                $producto->Nombre = $nombre;
                $producto->PrecioUnidad = $precio;
                $producto->TiempoEspera = $tiempo;
                $producto->Area = $area;
                $producto->TipoProducto = $tipo;
                $producto->Stock = $stock;

                $producto->save();
                $payload = json_encode(
                    array(
                        "IdUsuario" => strval($idUsuarioLogeado),
                        "IdRefUsuario" => null,
                        "IdAccion" =>  strval(AuditoriaAcciones::Modificacion),
                        "mensaje" => "Producto modificado con éxito",
                        "IdPedido" => null,
                        "Exito" => 1,
                        "IdPedidoDetalle" => null,
                        "IdMesa" => null,
                        "IdProducto" => $producto->IdProducto,
                        "IdArea" => null,
                        "Hora" => date('h:i:s')
                    )
                );

                $response->getBody()->write($payload);
                return $response->withHeader('Content-Type', 'application/json');
            } else {
                $payload = json_encode(array("mensaje" => "Producto no encontrado."));
            }
        } catch (Exception $e) {
            $response = $response->withStatus(401);
            $response->getBody()->write(json_encode(array('error' => $e->getMessage())));
            return $response->withHeader('Content-Type', 'application/json');
        }
    }


    public function TraerTodos($request, $response, $args)
    {
        $listaProductos = App\Models\Producto::all();

        $payload = json_encode(array("listaProductos" => $listaProductos));

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function TraerUno($request, $response, $args)
    {
        $idRecibido = $args['IdProducto'];

        $listaProductos = Producto::all();
        $producto = $listaProductos->find($idRecibido);

        $payload = json_encode($producto);

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }
}
