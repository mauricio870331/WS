<?php

require_once "Modelo/Conexion.php";

class FacturacionAPI {

    public function API() {
        header('Content-Type: application/JSON');
        $method = $_SERVER['REQUEST_METHOD'];
        switch ($method) {
            case 'GET'://consulta
                if ($_GET['tabla'] != "") {
                    $this->getDatos();
                } else {
                    $this->getLogin();
                }
                break;
            case 'POST'://inserta
                $this->saveDatos();
                break;
            case 'PUT'://actualiza
                $this->updatePeople();
                break;
            case 'DELETE'://elimina
                $this->deletePeople();
                break;
            default://metodo NO soportado
                echo 'METODO NO SOPORTADO';
                break;
        }
    }

    /**
     * Respuesta al cliente
     * @param int $code Codigo de respuesta HTTP
     * @param String $status indica el estado de la respuesta puede ser "success" o "error"
     * @param String $message Descripcion de lo ocurrido
     */
    function response($code = 200, $status = "", $message = "") {
        http_response_code($code);
        if (!empty($status) && !empty($message)) {
            $response = array("status" => $status, "message" => $message);
            echo json_encode($response);
        }
    }

    /**
     * función que segun el valor de "action" e "id":
     *  - mostrara una array con todos los registros de personas
     *  - mostrara un solo registro 
     *  - mostrara un array vacio
     */
    function getDatos() {
        $conexion = new Conexion("codigo_pymesapp");

        switch ($_GET['tabla']) {
            case 'usuarios_app':
                if (isset($_GET['id']) && !empty($_GET['id'])) {//muestra 1 solo registro si es que existiera ID                 
                    $response = $conexion->findById("people", "id", $_GET['id']);
                    echo json_encode($response);
                } else { //muestra todos los registros                   
                    $response = $conexion->findAll("people");
                    echo json_encode($response, true);
                }
                break;
            case 'SP':
                $response = $conexion->findAllSP($_GET["SP"]);
                echo json_encode($response, true);
                break;
            case 'ciudades':
                if (isset($_GET['campo']) && !empty($_GET['campo'])) {
                    $response = $conexion->findById("ciudades", $_GET['campo'], $_GET['valor'], $_GET['consulta']);
                    echo json_encode($response, true);
                } else { //muestra todos los registros                   
                    $response = $conexion->findAll("ciudades");
                    echo json_encode($response, true);
                }
                break;
            default:
                $this->response(400);
                break;
        }
        $conexion->desconectar();
    }

    function getLogin() {
        $conexion = new Conexion("codigo_pymesapp");
        $response = $conexion->login($_GET['id_usuario'], $_GET['password']);
        echo json_encode($response);
        $conexion->desconectar();
    }

    /**
      2  * metodo para guardar un nuevo registro de persona en la base de datos
      3 */
    function saveDatos() {
        $conexion = new Conexion("codigo_pymesapp");
        $obj = json_decode(file_get_contents('php://input'));
        $objArr = (array) $obj;
        if (empty($objArr)) {
            $this->response(422, "error", "Nothing to add. Check json");
        } else {
            switch ($_GET['tabla']) {
                case "SP":
                    switch ($_GET['SP']) {
                        case "insertarUsuarioApp":
                            $sql = "call insertarUsuarioApp('" . $obj->id_usuario . "'," . $obj->id_tipo_identificacion . ",'" . $obj->id_empresa . "'," . $obj->id_rol . ","
                                    . "'" . $obj->password . "','" . $obj->nombres . "','" . $obj->apellidos . "'," . $obj->estado_usuario . ",'" . $obj->transacion . "')";
                            $mensaje = $conexion->execQuery($sql);
                            $this->response(200, "success", 'usuario creado');
                            break;

                        default:
                            break;
                    }

                    break;

                default:
                    break;
            }
        }
        $conexion->desconectar();
    }

    /**
     * Actualiza un recurso
     */
    function updatePeople() {
        if (isset($_GET['action']) && isset($_GET['id'])) {
            if ($_GET['action'] == 'peoples') {
                $obj = json_decode(file_get_contents('php://input'));
                $objArr = (array) $obj;
                if (empty($objArr)) {
                    $this->response(422, "error", "Nothing to add. Check json");
                } else if (isset($obj->name)) {
                    $db = new PeopleDB();
                    $db->update($_GET['id'], $obj->name);
                    $this->response(200, "success", "Record updated");
                } else {
                    $this->response(422, "error", "The property is not defined");
                }
                exit;
            }
        }
        $this->response(400);
    }

    /**
     * elimina persona
     */
    function deletePeople() {
        if (isset($_GET['action']) && isset($_GET['id'])) {
            if ($_GET['action'] == 'peoples') {
                $conexion = new Conexion("dbtest");
                if ($conexion->checkID("people", "id", $_GET['id'])) {
                    if ($conexion->execQuery("delete from people where id = " . $_GET['id']) > 0) {
                        $this->response(200, "success", "Record Delete");
                    } else {
                        $this->response(200, "success", "No hay registros para eliminar");
                    }
                } else {
                    $this->response(200, "success", "El id " . $_GET['id'] . " no existe");
                }
                exit;
            }
        }
        $this->response(400);
    }

}

//end class

    