<?php
require_once __DIR__ . '/../config.php';

$dom = '';

function connect() {
    $conn = null;

    try{
         // On se connecte à MySQL
      $conn = new PDO(DB_DSN, DB_USER, DB_PASS);
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
///Ajout du case quartier
   case 'get_quartiers':
            if (isset($_GET['id_commune'])) {
                $id_commune = intval($_GET['id_commune']);
                $sql = 'SELECT id, "nom_quartier" as nom_quartier FROM public.quartier WHERE id_commune = :id_commune ORDER BY "nom_quartier"';
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':id_commune', $id_commune, PDO::PARAM_INT);
                $stmt->execute();
                $quartiers = $stmt->fetchAll(PDO::FETCH_ASSOC);
                echo json_encode($quartiers);
                exit();
            }
            break;
            
        case 'zoom_quartier':
            if (isset($_GET['id_quartier'])) {
                $id_quartier = intval($_GET['id_quartier']);
                $sql = 'SELECT id, public.ST_AsGeoJSON(ST_SetSRID(geom, 4326)) as geom, "nom_quartier" FROM public.quartier WHERE id = :id_quartier';
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':id_quartier', $id_quartier, PDO::PARAM_INT);
                $stmt->execute();
                
                $geojson = array('type' => 'FeatureCollection', 'features' => array());
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $properties = $row;
                    unset($properties['geom']);
                    $feature = array(
                        'type' => 'Feature',
                        'geometry' => json_decode($row['geom'], true),
                        'properties' => $properties
                    );
                    array_push($geojson['features'], $feature);
                }
                $dom = json_encode($geojson, JSON_NUMERIC_CHECK);
            }
            break;
    }

    }


echo $dom;

?>