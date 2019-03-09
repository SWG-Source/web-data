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
				p.character_full_name,
				(select
                                        (select regexp_substr(pl2.value, '[^:]+', 1, 2) from property_lists pl2 where pl2.list_id = 5 and regexp_substr(pl2.value, '[^:]+', 1, 1) = regexp_substr(pl.value, '[^:]+', 1, 2))
                                from
                                        property_lists pl
                                where
                                        pl.list_id = 7
                                and
                                        regexp_substr(pl.value, '[^:]+', 1, 3) = p.character_object) as guild,
				CASE
						WHEN tano.pvp_faction = 370444368 THEN 'Rebel'
						WHEN tano.pvp_faction = -615855020 THEN 'Imperial'
						ELSE 'No Faction'
				END as faction,
				CASE
						WHEN po.skill_template = 'force_sensitive_1a' THEN 'Jedi'
						WHEN po.skill_template = 'commando_1a' THEN 'Commando'
						WHEN po.skill_template = 'bounty_hunter_1a' THEN 'Bounty Hunter'
						WHEN po.skill_template = 'officer_1a' THEN 'Officer'
						WHEN po.skill_template = 'medic_1a' THEN 'Medic'
						WHEN po.skill_template = 'spy_1a' THEN 'Spy'
						WHEN po.skill_template = 'smuggler_1a' THEN 'Smuggler'
						WHEN po.skill_template = 'entertainer_1a' THEN 'Entertainer'
						ELSE 'Trader'
				END as class,
				po.current_gcw_points as points_for_week,
				CASE
						WHEN po.current_gcw_rating < 0 THEN 'Not Ranked'
						WHEN po.current_gcw_rating < 5000 THEN 'Private'
						WHEN po.current_gcw_rating < 10000 AND tano.pvp_faction = 370444368 THEN 'Trooper'
						WHEN po.current_gcw_rating < 10000 THEN 'Lance Corporal'
						WHEN po.current_gcw_rating < 15000 AND tano.pvp_faction = 370444368 THEN 'High Trooper'
						WHEN po.current_gcw_rating < 15000 THEN 'Corporal'
						WHEN po.current_gcw_rating < 20000 THEN 'Sergeant'
						WHEN po.current_gcw_rating < 25000 AND tano.pvp_faction = 370444368 THEN 'Senior Sergeant'
						WHEN po.current_gcw_rating < 25000 THEN 'Master Sergeant'
						WHEN po.current_gcw_rating < 30000 THEN 'Sergeant Major'
						WHEN po.current_gcw_rating < 35000 THEN 'Lieutenant'
						WHEN po.current_gcw_rating < 40000 THEN 'Captain'
						WHEN po.current_gcw_rating < 45000 THEN 'Major'
						WHEN po.current_gcw_rating < 50000 AND tano.pvp_faction = 370444368 THEN 'Commander'
						WHEN po.current_gcw_rating < 50000 THEN 'Lt. Colonel'
						WHEN po.current_gcw_rating < 55000 THEN 'Colonel'
						WHEN po.current_gcw_rating >= 55000 THEN 'General'
				END as current_rank,
				po.current_pvp_kills as kills_for_week,
				po.lifetime_gcw_points as lifetime_gcw_points,
				po.lifetime_pvp_kills as lifetime_pvp_kills,
				CASE
						WHEN po.max_gcw_imperial_rating < 0 THEN 'Not Ranked'
						WHEN po.max_gcw_imperial_rating < 5000 THEN 'Private'
						WHEN po.max_gcw_imperial_rating < 10000 THEN 'Lance Corporal'
						WHEN po.max_gcw_imperial_rating < 15000 THEN 'Corporal'
						WHEN po.max_gcw_imperial_rating < 20000 THEN 'Sergeant'
						WHEN po.max_gcw_imperial_rating < 25000 THEN 'Master Sergeant'
						WHEN po.max_gcw_imperial_rating < 30000 THEN 'Sergeant Major'
						WHEN po.max_gcw_imperial_rating < 35000 THEN 'Lieutenant'
						WHEN po.max_gcw_imperial_rating < 40000 THEN 'Captain'
						WHEN po.max_gcw_imperial_rating < 45000 THEN 'Major'
						WHEN po.max_gcw_imperial_rating < 50000 THEN 'Lt. Colonel'
						WHEN po.max_gcw_imperial_rating < 55000 THEN 'Colonel'
						WHEN po.max_gcw_imperial_rating >= THEN 'General'
				END as highest_imperial_rank,
				CASE
						WHEN po.current_gcw_rating < 0 THEN 'Not Ranked'
						WHEN po.current_gcw_rating < 5000 THEN 'Private'
						WHEN po.current_gcw_rating < 10000 THEN 'Trooper'
						WHEN po.current_gcw_rating < 15000 THEN 'High Trooper'
						WHEN po.current_gcw_rating < 20000 THEN 'Sergeant'
						WHEN po.current_gcw_rating < 25000 THEN 'Senior Sergeant'
						WHEN po.current_gcw_rating < 30000 THEN 'Sergeant Major'
						WHEN po.current_gcw_rating < 35000 THEN 'Lieutenant'
						WHEN po.current_gcw_rating < 40000 THEN 'Captain'
						WHEN po.current_gcw_rating < 45000 THEN 'Major'
						WHEN po.current_gcw_rating < 50000 THEN 'Commander'
						WHEN po.current_gcw_rating < 55000 THEN 'Colonel'
						WHEN po.current_gcw_rating >= 55000 THEN 'General'
				END as highest_rebel_rank
		from
				player_objects po
		join
				objects o ON (o.object_id = po.object_id)
		join
				players p ON (o.contained_by = p.character_object)
		join
				tangible_objects tano ON (tano.object_id = p.character_object)
		where
				po.current_gcw_points > 0
		or
				po.current_pvp_kills > 0";
    
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
