<?php

$dom = '';

function connect() {
    $conn = null;

    try{
         // On se connecte à MySQL
      $conn = new PDO('pgsql:host=localhost;port=5432;dbname=base_inondation','postgres','postgres');
    } 
    // S'il existe un problème de connection, on obtient le message d'erreur
    catch(PDOException $ex) {
      echo 'Connection error : ' . $ex->getMessage(); 
    }
    return $conn;
  }

if(isset($_GET['elemid'])) {

    $elemeid = $_GET['elemid'];

    $conn = connect();


    switch($elemeid) {

        case 'zoom_commune':

            $id_commune = $_GET['id_commune'];
            //recuperer les données
         $sql = 'SELECT id, public.ST_AsGeoJSON(ST_SetSRID(geom, 3857),4326) as geom, commune as nom_commune
        FROM public.communes_abidjan WHERE id = '.$id_commune;

 


          //Executer la requete
              $result = $conn->query($sql);

              $geojson = array(
                'type' => 'FeatureCollection',
              'features' => array()
            );
              //Parcourir la table des résultats de la requête
            while($row = $result->fetch(PDO::FETCH_ASSOC)){
                  //Recuperer tous les attributs de la ligne en cours
                  $properties = $row;
                  //Supprimer les attributs de geometry
              unset($properties['geom']);
              //Constituer notre json
              $feature = array(
                      'type' => 'Feature',
                'geometry' => json_decode($row['geom'], true),
                'properties' => $properties
              );
              //Inserer dans la variable json $geojson
                  array_push($geojson['features'], $feature);
          
            }

            $dom = json_encode($geojson, JSON_NUMERIC_CHECK);

            // Termine le traitement de la requête
            $result->closeCursor(); 

        break;


    }
}

echo $dom;

?>