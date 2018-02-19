<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\Constraints as Assert;
use BackendBundle\Entity\User;
use AppBundle\Services\Helpers;
use AppBundle\Services\JwtAuth;


class UserController extends Controller
{

    public function newAction(Request $request){
        $helpers = $this->get(Helpers::class);
        $json = $request->get('json', null);
        $params = json_decode($json);

        $data = array(
            'status' => 'error',
            'code'   => 400,
            'msg'    => 'User not created!!'
        );

        if($json != null){
            $createdAt = new \Datetime("now");
            $role = 'user';

            $email = (isset($params->email)) ? $params->email : null;
            $name = (isset($params->name)) ? $params->name : null;
            $surname = (isset($params->surname)) ? $params->surname : null;
            $password = (isset($params->password)) ? $params->password : null;

            $emailConstraint = new Assert\Email();
            $emailConstraint->message = "This email is not valid !!";
            $validate_email = $this->get("validator")->validate($email, $emailConstraint);
            
            if($email != null && count($validate_email) == 0 && $password != null && $name != null && $surname != null){
                $user = new User();
                $user->setCreatedAt($createdAt);
                $user->setRole($role);
                $user->setEmail($email);
                $user->setName($name);
                $user->setSurname($surname);

                //Cifrar la password
                $pwd = hash('sha256', $password);
                $user->setPassword($pwd);

                $em = $this->getDoctrine()->getManager();
                $isset_user = $em->getRepository('BackendBundle:User')->findBy(array(
                  "email" => $email
                ));
                  if(count($isset_user) == 0){
                      $em->persist($user);
                      $em->flush();

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
                        'msg'    => 'User not created, duplicated!!'
                    );                      
                  }

            }
        }
        
        return $helpers->json($data);

    }

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
            
            //Conseguir los datos del usuario identificado via token
            $identity = $jwt_auth->checkToken($token, true);

            //Conseguir el objeto a actualizar
            $user  = $em->getRepository('BackendBundle:User')->findOneBy(array(
              'id' => $identity->sub
            ));

            //Recoger datos post
            $json = $request->get('json', null);
            $params = json_decode($json);
    
            //Array de error por defecto
            $data = array(
                'status' => 'error',
                'code'   => 400,
                'msg'    => 'User not updated!!'
            );
    
            if($json != null){
                //este campo lo dejamos tal como está
                //$createdAt = new \Datetime("now");
                $role = 'user';
    
                $email = (isset($params->email)) ? $params->email : null;
                $name = (isset($params->name)) ? $params->name : null;
                $surname = (isset($params->surname)) ? $params->surname : null;
                $password = (isset($params->password)) ? $params->password : null;
    
                $emailConstraint = new Assert\Email();
                $emailConstraint->message = "This email is not valid !!";
                $validate_email = $this->get("validator")->validate($email, $emailConstraint);
                
                if($email != null && count($validate_email) == 0 && $name != null && $surname != null){
                    /*$user = new User();*/
                    //No se crea nuevo usuario sino que trabajamos con el obtenido de la base datos
                    //que es el que vamos a actulizar finalmente, seteando la información de nuevo.
                    
                    //$user->setCreatedAt($createdAt); Este campo se mantiene.
                    $user->setRole($role);
                    $user->setEmail($email);
                    $user->setName($name);
                    $user->setSurname($surname);
                    $em = $this->getDoctrine()->getManager();

                    //Cifrar la password
                    //en editAction sólo se hará cuando la password sea diferente.
                    if ($password != null){
                      $pwd = hash('sha256', $password);
                      $user->setPassword($pwd);
                    }

                    $isset_user = $em->getRepository('BackendBundle:User')->findBy(array(
                      "email" => $email
                    ));
                    //tanto si existe o si identity email sea igual a $email
                    //vamos a guardar los datos y persistir la información
                    if(count($isset_user) == 0 || $identity->email == $email){
                          $em->persist($user);
                          $em->flush();
    
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
                            'msg'    => 'User not updated, duplicated!!'
                        );                      
                      }
    
                }
            }            
        }else{
            /*En caso de que la autenticación no se produzca correctamente
            porque el token no sea correcto, retornamos un $data dando el error*/
            $data = array(
                'status' => 'error',
                'code'   => 400,
                'msg'    => 'Authorization no valid!!'
            );
        }

        return $helpers->json($data);
    }
}