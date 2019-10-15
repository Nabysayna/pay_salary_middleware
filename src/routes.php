<?php

//use Slim\Http\Request;
//use Slim\Http\Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use src\ConnectionController;

// Routes

$app->get('/[{name}]', function (Request $request, Response $response, array $args) {
    // Sample log message
    $this->logger->info("Slim-Skeleton '/' route");
    // Render index view
    return $this->renderer->render($response, 'index.phtml', $args);
});
$app->group('/connection',function(){
    $this->post('/connection',function(Request $request, Response $response){
        header("Access-Control-Allow-Origin:*");
        header("Access-Control-Allow-Headers: Content-Type");
       $data=$request->getParsedBody();
        $param=json_decode($data['param']);
        $bdd=new pdo("mysql:host=localhost;dbname=pay_salary_database","root","");
        $req=$bdd->prepare("SELECT * FROM users WHERE login=:l AND password=:p");
        $req->execute(array("l"=>$param->login,"p"=>sha1($param->password)));
        $us=$req->fetch();
        if($us){
            $req2=$bdd->prepare("DELETE FROM token WHERE id_user=:id");
            $req2->execute(array("id"=>$us["id"]));
            \date_default_timezone_set('UTC');
            $date=new \DateTime();
            $req3=$bdd->prepare("INSERT INTO token(id_user,token) VALUES(:id,:token)");
            $token=\sha1($us["id"].strval(mktime()).$us["id"]);
            $req3->execute(array("id"=>$us["id"],"token"=>$token));
            return $response->WithJson(array("status"=>"1","token"=>$token,'id'=>$us['id']));
        }else{
            return $response->WithJson(array("status"=>"0"));
        }
        
    });
    $this->post('/deconnexion',function(Request $request, Response $response){
        header("Access-Control-Allow-Origin:*");
        header("Access-Control-Allow-Headers: Content-Type");
        $data=$request->getParsedBody();
        $param=json_decode($data['param']);
        $bdd=new pdo("mysql:host=localhost;dbname=pay_salary_database","root","");
        $req3=$bdd->prepare("DELETE FROM token WHERE id_user=:id");
        $tontou=$req3->execute(array("id"=>$param->id));
        if($tontou){
            return $response->WithJson(array("status"=>"1","id"=>$param->id));
        }else{
            return $response->WithJson(array("status"=>"0"));
        }
    });
    $this->post('/verifUser',function(Request $request, Response $response){
        header("Access-Control-Allow-Origin:*");
        header("Access-Control-Allow-Headers: Content-Type");
        $data=$request->getParsedBody();
        $param=json_decode($data['param']);
        $bdd=new pdo("mysql:host=localhost;dbname=pay_salary_database","root","");
        $req=$bdd->prepare("SELECT * FROM token WHERE token=:t AND id_user=:id");
        $req->execute(array("t"=>$param->token,"id"=>intval($param->id)));
        $t=$req->fetch();
        if($t){
            return $response->WithJson(array("status"=>"1"));
        }else{
            return $response->WithJson(array("status"=>"0"));
        }

    });
});
$app->group('/accueil',function(){
    $this->post('/insertToBd',function(Request $request, Response $response){
        header("Access-Control-Allow-Origin:*");
        header("Access-Control-Allow-Headers: Content-Type");
        $data=$request->getParsedBody();
        $param=json_decode($data['param']);
        $bdd=new pdo("mysql:host=localhost;dbname=pay_salary_database","root","");
        $req1=$bdd->prepare("SELECT * FROM token WHERE id_user=:id AND token=:t");
        $req1->execute(array("id"=>$param->id,"t"=>$param->token));
        $use=$req1->fetch();
        if($use){
            $tab=json_decode($param->info);
            $nb=count($tab);
            \date_default_timezone_set('UTC');
            $date=new \DateTime();
            for($i=0;$i<$nb;$i++){
                $req=$bdd->prepare("INSERT INTO salaire(id_user,infosup,etat) VALUES(:id,:info,:etat)");
                $req->execute(array("id"=>$param->id,"info"=>json_encode($tab[$i]),"etat"=>0));
            }
            return $response->WithJson(array("status"=>1));
        }else{
            return $response->WithJson(array("status"=>0));
        }

    });
    $this->post('/getKiosque',function(Request $request, Response $response){
        header("Access-Control-Allow-Origin:*");
        header("Access-Control-Allow-Headers: Content-Type");
        $data=$request->getParsedBody();
         $bdd=new pdo("mysql:host=localhost;dbname=db778194042","root","");
       $param=json_decode($data['param']);
       /* return $response->WithJson(array("status"=>$param));
        $bdd=new pdo("mysql:host=localhost;dbname=db778194042","root","");
        $req1=$bdd->prepare("SELECT * FROM token WHERE id_user=:id AND token=:t");
        $req1->execute(array("id"=>$param->id,"t"=>$param->token));
        $use=$req1->fetch();
        return $response->WithJson(array("status"=>$use));
        if($use){*/
            $numKiosque = '%'.$param->numero.'%';
            $req=$bdd->prepare("SELECT * FROM `salaire` WHERE `infosup` LIKE :numeroKiosque AND `dateEnregistrement` BETWEEN :dateDebut and DATE_ADD(:dateFin, INTERVAL 2 DAY)" );
            $req->execute(array(":numeroKiosque"=>$numKiosque,":dateDebut"=> $param->dateDebut,"dateFin"=>$param->dateFin));
            $tontou =$req->fetchAll();
            return $response->WithJson(array("status"=>1,"message"=>$tontou));
        /*}else{
            return $response->WithJson(array("status"=>0));
        }*/

    });
    $this->post('/getEmploye',function(Request $request, Response $response){
        header("Access-Control-Allow-Origin:*");
        header("Access-Control-Allow-Headers: Content-Type");
        $data=$request->getParsedBody();
       // $param=json_decode($data['param']);
        $bdd=new pdo("mysql:host=localhost;dbname=pay_salary_database","root","");
        $req1=$bdd->prepare("SELECT * FROM salaire WHERE etat=:etat");
        $req1->execute(array("etat"=>0));
        $nb=0;
        $data="";
        while($sal=$req1->fetch()){
            $nb++;
            \date_default_timezone_set('UTC');
            $date=new \DateTime();
            $d=$date->format('Y-m-d\TH:i:s.u');
            $info=json_decode($sal["infosup"]);
            $data.=$sal["id"]."/".$info->USSD."/".$info->SALAIRE_PERCU."#";
           // $update=$bdd->prepare("UPDATE salaire SET etat=1,datePayment=:dateP WHERE id=:id");
           // $update->execute(array("id"=>$sal['id'],"dateP"=>$d));
            
        }
        if($nb>0){
            return $response->WithJson(array("status"=>1,"info"=>$data));
        }else{
            return $response->WithJson(array("status"=>0,"info"=>""));
        }
       
    });
    $this->post('/getEmployes',function(Request $request, Response $response){
        header("Access-Control-Allow-Origin:*");
        header("Access-Control-Allow-Headers: Content-Type");
        $bdd=new pdo("mysql:host=localhost;dbname=pay_salary_database","root","");
        $req=$bdd->prepare("SELECT * FROM employes");
        $req->execute();
        $empl=[];
        while($tup=$req->fetch()){
            $empl[]=array("id"=>$tup['id_user'],"prenom"=>$tup['prenom'],"nom"=>$tup['nom'],"telephone"=>$tup['telephone']);
        }
        return $response->WithJson(array('rep'=>$empl));

    });
    $this->post('/getAbsent',function(Request $request, Response $response){
        header("Access-Control-Allow-Origin:*");
        header("Access-Control-Allow-Headers: Content-Type");
        $bdd=new pdo("mysql:host=localhost;dbname=pay_salary_database","root","");
        $req=$bdd->prepare("SELECT * FROM absence");
        $req->execute();
        $rep=[];
        while($tup=$req->fetch()){
            $rep[]=array("id"=>$tup["id"],"prenom"=>$tup["prenom"],"nom"=>$tup["nom"],"date"=>$tup["DateS"]);
        }
        return $response->WithJson(array("rep"=>$rep));

    });
    $this->post('/facture',function(Request $request, Response $response){
        header("Access-Control-Allow-Origin:*");
        header("Access-Control-Allow-Headers: Content-Type");
       /* $data=$request->getParsedBody();
        $data=json_decode($data['param']);
        $data=json_decode($data->data);
        $bdd=new pdo("mysql:host=localhost;dbname=mbirmiprod","root","");
        foreach($data as $d){
          $req=$bdd->prepare("INSERT INTO facture(police,datee) VALUES(:police,:dat)");
          $req->execute(array("police"=>$d->REFERENCE,"dat"=>$d->DATE_OPERATION));
        }

        return $response->WithJson(array("rep"=>$data));*/
       /* $bdd=new pdo("mysql:host=localhost;dbname=mbirmiprod","root","");
        $req=$bdd->prepare("SELECT * FROM facture");
        $req->execute();
        while($rep=$req->fetch()){
           // if(!strpos($rep['datee'],'"')){
                $req2=$bdd->prepare("UPDATE facture SET datee=:d WHERE id=:id");
                $date=explode('/',$rep['datee']);
               $jour="";
                $moi="";
                if(intval($date[1])<=9){
                  $jour='0'.$date[1];
                }else{
                  $jour=$date[1];
                }
                if(intval($date[0])<=9){
                    $moi='0'.$date[0];
                }else{
                    $moi=$date[0];
                }
                $req2->execute(array("d"=>$date[2].'-'.$moi.'-'.$jour,"id"=>$rep['id']));
          //  }
        }*/
        $bdd=new pdo("mysql:host=localhost;dbname=mbirmiprod","root","");
        $req=$bdd->prepare("SELECT * FROM facture");
        $req->execute();
        $tontou=[];
        while($tup=$req->fetch()){
            $tontou[]=array("police"=>$tup['police'],"date"=>$tup['datee']);
        }
       // $data=$request->getParsedBody();
       $data="param=".json_encode($tontou);
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "http://51.38.234.197/backendprod/horsSentiersBattus/scripts/findfacture.php",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_POST => true,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $data  
        ));

        $reponse = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);
        return $response->WithJson(array("rep"=>count(json_decode($reponse)->rep)));
        
    });
    $this->post('/updateEmploye',function(Request $request, Response $response){
        header("Access-Control-Allow-Origin:*");
        header("Access-Control-Allow-Headers: Content-Type");
        $data=$request->getParsedBody();
        $id=$data['id'];
        $token=$data['token'];
        $date=new \DateTime();
        $d=$date->format('Y-m-d\TH:i:s.u');
        $bdd=new pdo("mysql:host=localhost;dbname=pay_salary_database","root","");
        $req1=$bdd->prepare("UPDATE salaire SET etat=1,datePayment=:dateP WHERE id=:id");
        $tontou=$req1->execute(array("dateP"=>$d,"id"=>intval($id)));
        return $response->WithJson(array("rep"=>$tontou,"id"=>$id));
    });
    $this->post('/getListe',function(Request $request, Response $response){
        header("Access-Control-Allow-Origin:*");
        header("Access-Control-Allow-Headers: Content-Type");
        $data=$request->getParsedBody();
        $bdd=new pdo("mysql:host=localhost;dbname=pay_salary_database","root","");
        $req1=$bdd->prepare("SELECT * FROM salaire");
        $req1->execute();
        $ligne=[];
        while($rep=$req1->fetch()){
            $ligne[]  = array('id' => $rep['id'],'infoSalaries' => $rep['infosup'],'dateEnregistrement' => $rep['dateEnregistrement'],'etat' => $rep['etat']);
        }
        return $response->WithJson(array("code"=>1,"message"=>$ligne));

    });

    $this->post('/validerEnregistrementAbsence',function(Request $request, Response $response){
        header("Access-Control-Allow-Origin:*");
        header("Access-Control-Allow-Headers: Content-Type");
        $data=$request->getParsedBody();
        $data=json_decode($data['param']);
        try{
            $bdd=new pdo("mysql:host=localhost;dbname=pay_salary_database","root","");
            $req=$bdd->prepare("INSERT INTO absence(id_user,prenom,nom,DateS) VALUES(:id,:prenom,:nom,:dates)");
            $tontou=$req->execute(array("id"=>$data->id_user,"prenom"=>$data->prenom,"nom"=>$data->nom,"dates"=>$data->date));
            return $response->WithJson(array("rep"=>$tontou));
        }catch(Exception $e){
            return $response->WithJson(array("rep"=>0));

        }

    });
    $this->post('/supprimerAbsence',function(Request $request, Response $response){
        header("Access-Control-Allow-Origin:*");
        header("Access-Control-Allow-Headers: Content-Type");
        $data=$request->getParsedBody();
        $data=json_decode($data['param']);
        try{
            $bdd=new pdo("mysql:host=localhost;dbname=pay_salary_database","root","");
            $req=$bdd->prepare("DELETE FROM absence WHERE id=:id");
            $tontou=$req->execute(array("id"=>$data->id_user));
            return $response->WithJson(array("rep"=>$tontou));
        }catch(Exception $e){
            return $response->WithJson(array("rep"=>false));
        }

    });

});
