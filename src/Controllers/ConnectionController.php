<?php

namespace App;
//use Slim\Http\Request as $request;
//use Slim\Http\Response as $response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

use \src\Controller;

class ConnectionController extends Controller{
    public function connection(Request $request,Response $response){
        return 'rasta';
    }

}