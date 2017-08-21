<?php

include_once './Modelo/Conexion.php';

$conexion = new Conexion("codigo_test");

//$rs = $conexion->showProcedures("SHOW PROCEDURE STATUS");
//$rs = $conexion->showProcedures("show create procedure");
$rs = $conexion->showProcedures("select * from facturas");


echo "<pre>";
print_r($rs);
echo "</pre>";

