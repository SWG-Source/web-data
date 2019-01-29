<?php

function openDb(){
    $settings = json_decode(file_get_contents(__DIR__."/../settings.json"), false);

    // don't change this.
    $tns = "(DESCRIPTION =
            (ADDRESS_LIST =
                (ADDRESS = (PROTOCOL = TCP)(HOST = ".$settings->serverIp.")(PORT = ".$settings->dbPort."))
            )
            (CONNECT_DATA =
                (SERVICE_NAME = ".$settings->dbSid.")
            )
            )";
    return new PDO("oci:dbname=".$tns,$settings->dbUser,$settings->dbPass);
}

?>