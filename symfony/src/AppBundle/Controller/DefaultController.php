<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\Constraints as Assert;
use AppBundle\Services\Helpers;
use AppBundle\Services\JwtAuth;

class DefaultController extends Controller
{

    public function indexAction(Request $request)
    {
        return $this->render('default/index.html.twig', [
            'base_dir' => realpath($this->getParameter('kernel.project_dir')).DIRECTORY_SEPARATOR,
        ]);
    }
   
    //será una nueva ruta en nuestra aplicación.
    public function loginAction(Request $request){

        $helpers = $this->get(Helpers::class);

        //Recibir json por POST
        $json = $request->get('json', null);

        //array a devolver por defecto
        $data = array(
          'status' => 'error',
          'data' => 'Send json via post!!'
        );

        //si el json es distinto de null hacemos el login
        if($json != null){
            //convertimos un json a un array de PHP 
            $params = json_decode($json);

            $email = (isset($params->email)) ? $params->email : null;
            $password = (isset($params->password)) ? $params->password : null;
            $getHash = (isset($params->getHash)) ? $params->getHash : null;

            //creamos una instancia de validación de email
            $emailConstraint = new Assert\Email();
            $emailConstraint->message = "This is email is not valid!!";
            $validate_email = $this->get("validator")->validate($email, $emailConstraint);

            //Cifrar la contraseña con hash
            //$pwd = hash('sha256', $password);

            if($email != null && count($validate_email) == 0 && $password != null){

                $jwt_auth = $this->get(JwtAuth::class);

                if($getHash == null || $getHash == false){
                  $signup = $jwt_auth->signup($email, $password);
                }else{
                  $signup = $jwt_auth->signup($email, $password, true);
                }

            return $this->json($signup);

        }else{
        //sino si el json es null retorna un error
                $data = array(
                    'status' => 'error',
                    'data' => 'Email o password incorrecto'
                  );
            }
        }
        
        return $helpers->json($data);
        
    }


    public function pruebasAction(Request $request){
        $helpers = $this->get(Helpers::class);
        $jwt_auth = $this->get(JwtAuth::class);
        $token = $request->get("authorization", null);
        
        if($token && $jwt_auth->checkToken($token) == true){
            $em = $this->getDoctrine()->getManager();
            $userRepo = $em->getRepository('BackendBundle:User');
            $users = $userRepo->findAll();
    
            return $helpers->json(array(
                     'status' => 'success',
                     'users' => $users
                    ));
        }else{
            return $helpers->json(array(
                'status' => 'error',
                'code' => 400,
                'data' => 'Authorization no valid'
               ));
        }
 
        /*
        $helpers = $this->get(Helpers::class);
        $jwt_auth = $this->get(JwtAuth::class);
        $token = $request->get("authorization", null);

        if($token && $jwt_auth->checkToken($token) == true){
          $em = $this->getDoctrine()->getManager();
          $userRepo = $em->getRepository('BackendBundle:User');
          $users = $userRepo->findAll(); 
          
          return $helpers->json(array(
            'status' => 'success',
            'users' => $users
          ));
        }else{
          return $helpers->json(array(
            'status' => 'error',
            'code' => 400,
            'users' => 'Authorization no valid'
          ));
        } */         
    }
}