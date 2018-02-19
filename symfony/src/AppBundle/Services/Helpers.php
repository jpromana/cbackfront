<?php
namespace AppBundle\Services; /*definimos el namespace para que el 
                              framework sepa dónde está ubicada esta clase*/

class Helpers{
    /*Define variable*/
    public $manager;

    /*Definimos el constructor.Le pasamos el parámetro manager*/
    public function __construct($manager){
        $this->manager = $manager;

    }

    public function json($data){
        $normalizers = array(new \Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer());
        $encoders = array("json" => new \Symfony\Component\Serializer\Encoder\JsonEncoder());
        
        $serializer = new \Symfony\Component\Serializer\Serializer($normalizers, $encoders);
        $json = $serializer->serialize($data, 'json');

        $response = new \Symfony\Component\HttpFoundation\Response();/*ESto hace una respuesta http*/
        $response->setContent($json);
        $response->headers->set('Content-Type', 'application/json');

        return $response;

    }
}