<?php
namespace AppBundle\Services;

use Firebase\JWT\JWT;

class JwtAuth{
    public $manager;
    //Declaramos la clave secreat
    public $key;

    public function __construct($manager){
        $this->manager = $manager;
        $this->key='holasoylaclave555666';
    }

    public function signup($email, $password, $getHash  = null){
        $user = $this->manager->getRepository('BackendBundle:User')->findOneBy(array(
          "email" => $email, 
          "password" => $password
        ));

        $signup = false;
        if(is_object($user)){
            $signup = true;
        }

        if($signup ==  true){
          // GENERAR UN TOKEN JWT (este método generará un token y lo devolverá 
          //para poder utilizarlo con cada una de las peticiones de nuestra API)

          $token = array(
              "sub"     => $user->getId(),
              "email"   => $user->getEmail(),
              "name"    => $user->getName(),
              "surname" => $user->getSurname(),
              "iat"     => time(),
              "exp"     => time() + (7 * 24 * 60 * 60)
          );

          //El método lo genaramos con un objeto que tiene jwt, método encode de jwt
          //El algoritmo de codificación que utiliza es el HS256
          $jwt = JWT::encode($token, $this->key, 'HS256');
          $decoded = JWT::decode($jwt, $this->key, array('HS256'));

          if($getHash == null){
              $data = $jwt;
          }else{
              $data = $decoded;
          }

          //$data = $jwt;
          /*$data = array(
            'status'=>'success',
            'user'=>$user
          );*/
        }else{
            $data = array(
                'status'=>'error',
                'data'=>'login failed!'
              );
        }

        //return $email." ".$password;
        return $data;
    }
    //$getIdentity parámetro opcional, con valor por defecto false
    public function checkToken($jwt, $getIdentity =  false){
        $auth = false;
        //Decodificar nuestro token. Le pasamos los datos:
        //nuestro token $jwt, el usuario que se ha logueado que es quién está
        //realizando la petición
        //la clave secreta $this->key, para poder decodificar el token
        //y un array con el método descifrado, que en este caso es HS256
        try{
          $decoded = JWT::decode($jwt, $this->key, array('HS256'));
        }catch(\UnexpectedValueException $e){
            $auth = false;
        }catch(\DomainException $e){
            $auth = false;
        }
        //Comprobamos si decode es un objeto tiene el id correctamente seteado
        //Entonces el login será correcto y $auth tendrá valor true
        //Con (isset($decoded) controlamos que no nos de el error en el navegador de 
        //undefined variable: decode, cuando se tenga un token aunque sea incorrecto.
        if(isset($decoded) && is_object($decoded) && isset($decoded->sub)){
            $auth = true;
        }else{
            $auth = false;
        }
        //Devolveremos $auth que indica si es correcto el token o no
        if($getIdentity ==  false){
            return $auth;
        }else{
            //en caso de que sí tenga algún tipo de información retornamos
            // $decoded que es el array que contiene los datos del usuario
            //logueado y dentro de ese token
            return $decoded;            
        }
        //Usaremos este método en DefaultController.
    }
}