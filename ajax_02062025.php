<?php

$dom = '';

function connect() {
    $conn = null;

    try{
         // On se connecte à MySQL
      $conn = new PDO('pgsql:host=localhost;port=5432;dbname=base_inondation','postgres','0151516084');
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

        case 'zoom_quartier':

            $id_quartier = $_GET['id_quartier'];
            //recuperer les données
            $sql = 'SELECT id, "area", "commune", public.ST_AsGeoJSON(geom,6) as geom, "nom_quar" as nom_quartier, "id" 
            FROM public.quartiers_cocody WHERE id = '.$id_quartier;

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




    switch($elemeid) {

      case 'zoom_commune':

          $id_commune = $_GET['id_commune'];
          //recuperer les données
          $sql = 'SELECT id, "ville_com", public.ST_AsGeoJSON(geom,6) as geom, "nom_commune" as nom_commune, "id" 
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