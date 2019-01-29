<?php
require_once __DIR__."/utils/data.php";

// NOTE:
// this script assumes you're using Oracle 10, 11 or 12.  It's not usable for other databases.  To use this PHP script, you must have
// the "php_pdo_oci.dll" extension enabled in PHP.  If you do not have that extension running, you won't be able to use this script.

try{
    // this is the database connection string... don't change this.
    $conn = openDb();

    // this is the query you wish to run.
    $query = "select
                        oo.object_name as bidder,
                        ma.item_name as item,
                        mab.bid as current_bid
                from
                        market_auctions ma
                join
                        objects o ON (o.object_id = ma.item_id)
                left join
                        market_auction_bids mab ON (mab.item_id = ma.item_id)
                left join
                        objects oo ON (oo.object_id = mab.bidder_id)
                where
                        active = 1";
    
    $stmt = $conn->query($query);

    $json = array();

    while($details = $stmt->fetch(PDO::FETCH_ASSOC)){
        $json[] = $details;
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