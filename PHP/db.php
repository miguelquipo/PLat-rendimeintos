<?php
   // Conexión a SQL Server
$serverName = "localhost\\SQLEXPRESS";
$uid = "sa";
$pwd = "cultiverde24plat";
$databaseName = "dbrendimientos";
$connectionInfo = array(
    "UID" => $uid,
    "PWD" => $pwd,
    "Database" => $databaseName,
    "TrustServerCertificate" => true,
    "Encrypt" => false
);
$conn = sqlsrv_connect($serverName, $connectionInfo);

if (!$conn) {
    die(print_r(sqlsrv_errors(), true));
}
?>