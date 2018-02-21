<?php
namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;
use BackendBundle\Entity\Task;
use AppBundle\Services\Helpers;
use AppBundle\Services\JwtAuth;


class TaskController extends Controller{
    //desde la URL nos llega el id, es el que le añadimos como parámetro al método
    //Se utilizará para comprobar si es una modificación/edición. Por defecto, va a ser null.
    public function newAction(Request $request, $id = null){
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
            //Validamos el parámetro $id, para saber si es una inserción o edición. Si es null
            //será una inserción
            if($id == null){
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
              //realizamos la búsqueda.
              $task = $em->getRepository('BackendBundle:Task')->findOneBy(array(
                "id" => $id
              ));
              //comprobamos si existe la identidad del usuario logueado
              //si el id del usuario logueado es igual al id del usuario dueño de esta tarea
              //podremos editarla.
              if(isset($identity->sub) && $identity->sub == $task->getUser()->getId
                ()){
                //tendremos una tarea con todos los setters disponibles.
                $task->setTitle($title);
                $task->setDescription($description);
                $task->setStatus($status);
                $task->setUpdatedAt($updatedAt);  

                //persistimos los datos en el orm
                $em->persist($task);
                $em->flush();

                //devolvemos la información
                $data = array(
                  'status' => 'success',
                  'code'   => 200,
                  'data'    => $task
                );
                
              }else{
                //no coincide usuario logueado con el usuario de la tarea, no se tiene permisos
                //para editarla.
                $data = array(
                  'status' => 'error',
                  'code'   => 400,
                  'msg'    => 'Task updated error, you not owner!'
                );
                                  
              }
            }

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

    public function tasksAction(Request $request){
      //comprobamos si el token que nos llega es correcto
      //cargamos el servicio de helpers
      $helpers = $this->get(Helpers::class);
      $jwt_auth = $this->get(JwtAuth::class);

      $token = $request->get('authorization', null);
      $authCheck = $jwt_auth->checkToken($token);

      if($authCheck){
        //guardamos la tarea
        $identity = $jwt_auth->checkToken($token, true);

        $em = $this->getDoctrine()->getManager();

        $dql = "SELECT t FROM BackendBundle:Task t WHERE t.user = {$identity->sub} ORDER BY t.id DESC";
        $query = $em->createQuery($dql);
        //usamos el paginador y le pasamos esta consulta.
        //recogemos los parámetros get de la url        
        $page = $request->query->getInt('page', 1);
        $paginator = $this->get('knp_paginator');
        $items_per_page = 10;

        $pagination = $paginator->paginate($query, $page, $items_per_page);
        $total_items_count = $pagination->getTotalItemCount();

        //Ya tenemos toda la información recopilada, ahora la devolvemos en el array
        $data = array(
          'status' => 'success',
          'code'   => 200,
          'total_items_count'   => $total_items_count,
          'page_actual'         => $page,
          'items_per_page'      => $items_per_page,
          'total_pages'         => ceil($total_items_count / $items_per_page), //calcula el número de páginas y lo redondea con ceil
          'data'                => $pagination //muestra los datos de 10 en 10
        );
        
      }else{
        $data = array(
          'status' => 'error',
          'code'   => 400,
          'msg'    => 'Authorization no valid'
        );      

      }
      return $helpers->json($data);
    }
    public function taskAction(Request $request, $id = null){
      //comprobamos si el token que nos llega es correcto
      //cargamos el servicio de helpers
      $helpers = $this->get(Helpers::class);
      $jwt_auth = $this->get(JwtAuth::class);

      $token = $request->get('authorization', null);
      $authCheck = $jwt_auth->checkToken($token);

      if($authCheck){
        //si la auntenticación es correcta
        $identity = $jwt_auth->checkToken($token, true);
        
        $em = $this->getDoctrine()->getManager();

        $task = $em->getRepository('BackendBundle:Task')->findOneBy(array(
          "id" => $id
        ));


        if($task && is_object($task) && $identity->sub == $task->getUser()->getId()){
          $data = array(
            'status' => 'success',
            'code'   => 200,
            'data'    => $task
          );  
        }else{
          $data = array(
            'status' => 'error',
            'code'   => 404,
            'msg'    => 'Task not found'
          );  
        }
        
      }else{
        $data = array(
          'status' => 'error',
          'code'   => 400,
          'msg'    => 'Authorization no valid'
        );              
      }
      //codificamos el array a un json
      return $helpers->json($data);
    }

    public function searchAction(Request $request, $search = null){
      //comprobamos si el token que nos llega es correcto
      //cargamos el servicio de helpers
      $helpers = $this->get(Helpers::class);
      $jwt_auth = $this->get(JwtAuth::class);

      $token = $request->get('authorization', null);
      $authCheck = $jwt_auth->checkToken($token);

      if($authCheck){
        //si la auntenticación es correcta
        $identity = $jwt_auth->checkToken($token, true);
        
        //para hacer la consulta
        $em = $this->getDoctrine()->getManager();

        //Filtro, variable que nos llegará por post
        $filter = $request->get('filter', null);
        if(empty($filter)){
          $filter = null;
        }elseif($filter == 1){
          $filter = 'new';
        }elseif($filter == 2){
          $filter = 'todo';
        }else{
          $filter = 'finished';
        }

        //Orden
        $order = $request->get('order', null);
        if(empty($order) || $order == 2){
          $order = 'DESC';
        }else{
          $order = 'ASC';
        }

        //Hacemos la búsqueda en sí
        if($search != null){
          $dql = "SELECT t FROM BackendBundle:Task t "
                 ."WHERE t.user = $identity->sub AND "
                 ."(t.title LIKE :search OR t.description LIKE :search) ";

        }else{
          $dql = "SELECT t FROM BackendBundle:Task t "
                ."WHERE t.user = $identity->sub";
          
        }
        
        //añadimos filtro
        if($filter != null){
          $dql .= " AND t.status = :filter";
        }

        // Set order
        $dql .= " ORDER BY t.id $order";
        
        //created query
        $query = $em->createQuery($dql);
        
        //Set parameter filter
        if($filter != null){
          $query->setParameter('filter', "$filter");
        }

        //parámetro de búsqueda
        if(!empty($search)){
          $query->setParameter('search', "%$search%");
        }
        
        $tasks = $query->getResult();

        //retornamos el array con los datos
        $data = array(
          'status' => 'success',
          'code'   => 200,
          'data'    => $tasks
        );
        
      }else{
        $data = array(
          'status' => 'error',
          'code'   => 400,
          'msg'    => 'Authorization no valid'
        );
      }
      return $helpers->json($data);
    }

    
    public function removeAction(Request $request, $id = null){
      //comprobamos si el token que nos llega es correcto
      //cargamos el servicio de helpers
      $helpers = $this->get(Helpers::class);
      $jwt_auth = $this->get(JwtAuth::class);

      //obtener y comprobar el token  
      $token = $request->get('authorization', null);
      $authCheck = $jwt_auth->checkToken($token);

      if($authCheck){
        //si la auntenticación es correcta
        $identity = $jwt_auth->checkToken($token, true);
        
        $em = $this->getDoctrine()->getManager();

        //busca la tarea en base al id que recibe por la url
        $task = $em->getRepository('BackendBundle:Task')->findOneBy(array(
          "id" => $id
        ));

        if($task && is_object($task) && $identity->sub == $task->getUser()->getId()){
          //recibimos la tarea y la eliminamos
          $em->remove($task); //de esta manera se está persistiendo la eliminación de ese objeto
          
          //ahora efectuamos este cambio en la base de datos
          $em->flush();          

          $data = array(
            'status' => 'success',
            'code'   => 200,
            'data'    => $task
          );  
        }else{
          $data = array(
            'status' => 'error',
            'code'   => 404,
            'msg'    => 'Task not found'
          );  
        }
        
      }else{
        $data = array(
          'status' => 'error',
          'code'   => 400,
          'msg'    => 'Authorization no valid'
        );              
      }
      //codificamos el array a un json
      return $helpers->json($data);
    }



  }