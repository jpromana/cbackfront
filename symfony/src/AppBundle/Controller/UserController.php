<?php
namespace AppBundle\Controller;

//use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
//use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\Constraints as Assert;
use BackendBundle\Entity\User;
use AppBundle\Services\Helpers;
use AppBundle\Services\JwtAuth;

//En este método:
//Decodificar el Json que nos va a llegar por POST
//Comprobar que las propiedades de ese Json son correctas y no están vacías
//Validamos los datos y seguidamente guardar un nuevo usuario en la BD
class UserController extends Controller{

  public function newAction(Request $request){
    //cargamos servicio Helpers para poder convertir a 
    //json colecciones de objetos
    $helpers = $this->get(Helpers::class);
    
    $json = $request->get("json", null);
    //decodificamos este json, para ello usamos json_decode de php
    //y lo convierte en un objeto de php
    $params = json_decode($json);

    $data = array(
      'status' => 'error',
      'code'   => 400,
      'msg'    => 'User not created!'
    );

    if($json != null){
      $craetedAt = new \Datetime("now");
      $role = 'user';
      
      //usamos operador ternario ? :
      $email = (isset($params->email)) ? $params->email : null;
      $name = (isset($params->name)) ? $params->name : null;
      $surname = (isset($params->surname)) ? $params->surname : null;
      $password = (isset($params->password)) ? $params->password : null;

      $emailConstraint = new Assert\Email();
      $emailConstraint->message = "This email is not valid !!";
      $validate_email = $this->get("validator")->validate($email, $emailConstraint);

      if($email != null && count($validate_email) == 0 && $password != null && $name != null && $surname != null){
        //Hacemos una instancia de la clase user, para llegar a guardar en la BD el nuevo registro
        $user = new User();
        $user->setCreatedAt($craetedAt);
        $user->setRole($role);
        $user->setEmail($email);
        $user->setName($name);
        $user->setSurname($surname);

        //Ciframos la password que nos llega por Post
        $pwd = hash('sha256', $password);
        $user->setPassword($pwd);
        
        //hacemos un persist para que los datos persistan en el orm.doctrine y se quede ahi en la bandeja
        //de salida para luego guardar la información y posteriormente hacer un flush para guardar los datos
        //que hay persistido en base de datos, siempre que este usuario no exista en la base de datos
        //Cargamos el entity manager
        $em = $this->getDoctrine()->getManager();
        $isset_user = $em->getRepository('BackendBundle:User')->findBy(array(
          "email" => $email
        ));
        
        if(count($isset_user) == 0){
          $em->persist($user);
          $em->flush(); //para guardarlo en la BD

          $data = array(
            'status' => 'success',
            'code'   => 200,
            'msg'    => 'New user created!!',
            'user'   => $user
          );

        }else{
          $data = array(
            'status' => 'error',
            'code'   => 400,
            'msg'    => 'User not created, duplicated!'
          );                
        }
    }


    }

    return $helpers->json($data);

  }

/* METODO EDIT */
  public function editAction(Request $request){
    /*lo primero a hacer es comprobar que el token que nos va a
      llegar por POST es correcto, para ello cargamos una variable
      jwtAuth y llamamos al servicio de autenticación*/
    $helpers = $this->get(Helpers::class);
    $jwt_auth = $this->get(JwtAuth::class);

    /*ahora conseguimos el token, que nos viene por POST*/
    $token = $request->get("authorization", null);
    /*haremos una llamada al método de jwt_auth llamado checkToken.
      Sólo le mandamos el token para comprobar si es correcto o no*/
    $authCheck = $jwt_auth->checkToken($token);

    if($authCheck){
      //Entity manager
      $em = $this->getDoctrine()->getManager();

      //Conseguir los datos del usuario identificado con el token
      //guardamos la identidad del usuario logueado en la variable identity
      $identity = $jwt_auth->checkToken($token, true);

      //Conseguir el objeto a actualizar
      //obtenemos datos del usuario
      $user = $em->getRepository('BackendBundle:User')->findOneBy(array(
        'id' => $identity->sub
      ));

      //Recoger datos por Post
      $json = $request->get("json", null);
      //decodificamos este json, para ello usamos json_decode de php
      //y lo convierte en un objeto de php
      $params = json_decode($json);

      //Array de error por defecto
      $data = array(
        'status' => 'error',
        'code'   => 400,
        'msg'    => 'User not updated!'
      );
  
      if($json != null){
        //$craetedAt = new \Datetime("now"); //Este campo no lo actualizaremos.
        $role = 'user';
        
        //usamos operador ternario ? :
        $email = (isset($params->email)) ? $params->email : null;
        $name = (isset($params->name)) ? $params->name : null;
        $surname = (isset($params->surname)) ? $params->surname : null;
        $password = (isset($params->password)) ? $params->password : null;
  
        $emailConstraint = new Assert\Email();
        $emailConstraint->message = "This email is not valid !!";
        $validate_email = $this->get("validator")->validate($email, $emailConstraint);
  
        if($email != null && count($validate_email) == 0 && $name != null && $surname != null){
          //Objeto de la BD. Actualizaremos los datos con un set.
          //$user->setCreatedAt($craetedAt); //este campo no lo actualizaremos
          $user->setRole($role);
          $user->setEmail($email);
          $user->setName($name);
          $user->setSurname($surname);

          //Si la contraseña nos viene con datos
          if($password != null){
            //Ciframos la password que nos llega por Post, pero sólo cuando la contraseña haya cambiado
            $pwd = hash('sha256', $password);
            $user->setPassword($pwd);
          }

          //hacemos un persist para que los datos persistan en el orm.doctrine y se quede ahi en la bandeja
          //de salida para luego guardar la información y posteriormente hacer un flush para guardar los datos
          //que hay persistido en base de datos, siempre que este usuario no exista en la base de datos
  
          $isset_user = $em->getRepository('BackendBundle:User')->findBy(array(
            "email" => $email
          ));
          
          if(count($isset_user) == 0 || $identity->email == $email){
            //Cargamos el entity manager
            $em->persist($user);
            $em->flush(); //para guardarlo en la BD
  
            $data = array(
              'status' => 'success',
              'code'   => 200,
              'msg'    => 'New user updated!!',
              'user'   => $user
            );
  
          }else{
            $data = array(
              'status' => 'error',
              'code'   => 400,
              'msg'    => 'User not updated, duplicated!'
            );                
         }
       }
     }
    }else{
      //si el token no es correcto, el usuario no está logueado
      $data = array(
        'status' => 'error',
        'code'   => 400,
        'msg'    => 'Authorization not valid !'
      );                
  
    }
    
    return $helpers->json($data);

  }

}