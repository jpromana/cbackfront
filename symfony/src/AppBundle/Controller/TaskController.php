<?php
namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;
use BackendBundle\Entity\Task;
use AppBundle\Services\Helpers;
use AppBundle\Services\JwtAuth;


class TaskController extends Controller{

    public function newAction(Request $request){
      //comprobamos si el token que nos llega es correcto
      //cargamos el servicio de helpers
      $helpers = $this->get(Helpers::class);
      $jwt_auth = $this->get(JwtAuth::class);

      $token = $request->get('authorization', null);
      $authCheck = $jwt_auth->checkToken($token);

      if($authCheck){
        //guardamos la tarea
        $identity = $jwt_auth->checkToken($token, true);
        $json = $request->get("json", null);

        if($json != null){
          //Decodificamos los datos que nos llegan por Post y lo pasamos a un objeto de PHP
          $params = json_decode($json);

          //Validación de los datos que nos llegan
          $createdAt = new \Datetime('now');
          $updatedAt = new \Datetime('now');

          $user_id = ($identity->sub != null) ? $identity->sub : null;
          $title = (isset($params->title)) ? $params->title : null;
          $description = (isset($params->description)) ? $params->description : null;
          $status = (isset($params->status)) ? $params->status : null;
          
          if($user_id != null && $title != null){
            //crear tarea.
            $em = $this->getDoctrine()->getManager();

            $user = $em->getRepository('BackendBundle:User')->findOneBy(array(
              "id" => $user_id
            ));

            //Montamos un objeto de tarea
            $task = new Task();
            $task->setUser($user);
            $task->setTitle($title);
            $task->setDescription($description);
            $task->setStatus($status);
            $task->setCreatedAt($createdAt);
            $task->setUpdatedAt($updatedAt);

            //persistimos los datos en el orm
            $em->persist($task);
            $em->flush();

            //devolvemos la información
            $data = array(
              'status' => 'success',
              'code'   => 200,
              'data'    => $task  //data se convertirá luego a json
            );
          }else{
            $data = array(
              'status' => 'error',
              'code'   => 400,
              'msg'    => 'Task not created, params failed!'
            );
              
          }
        }else{
          //error, tarea no creada
          $data = array(
            'status' => 'error',
            'code'   => 400,
            'msg'    => 'Task not created, params failed!'
          );
          
        }
        

      }else{
          $data = array(
            'status' => 'error',
            'code'   => 400,
            'msg'    => 'Authorization no valid'
          );
      }
      
      return $helpers->json($data);
    }
}