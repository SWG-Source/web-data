<?php
require_once __DIR__."/utils/data.php";

// NOTE:
// this script assumes you're using Oracle 10, 11 or 12.  It's not usable for other databases.  To use this PHP script, you must have
// the "php_pdo_oci.dll" extension enabled in PHP.  If you do not have that extension running, you won't be able to use this script.

try{
    // this is the database connection string... don't change this.
    $conn = openDb();

    // this is the query you wish to run.
    $query = "select resource_name, resource_class, attributes from resource_types where depleted_timestamp >= (select last_save_time from clock)";
    
    $stmt = $conn->query($query);

    $json = array();

    while($details = $stmt->fetch(PDO::FETCH_ASSOC)){
        $attrs = explode(':',$details['ATTRIBUTES']);
        foreach($attrs as $attr){
            if(sizeof(explode(' ', $attr)) != 2) continue;
            list($attribute, $value) = explode(' ', $attr);
            $obj[$attribute] = $value;
        }
        $details['ATTRIBUTES'] = $obj;
        $json[] = $details;
		unset($obj);
		unset($attrs);
    }

    array_walk_recursive($json, function (&$item, $key) {
        $item = null === $item ? '' : $item;
    });

    echo json_encode($json, JSON_NUMERIC_CHECK);
}
catch(PDOException $e){
    echo ($e->getMessage());
}
?>