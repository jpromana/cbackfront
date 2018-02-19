<?php
namespace AppBundle\Controller;

//use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
//use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\Constraints as Assert;
use BackendBundle\Entity\User;
use AppBundle\Services\Helpers;
//use AppBundle\Services\JwtAuth;

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

}