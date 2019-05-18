<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Constraints\Email;


use App\Entity\User;
use App\Entity\Video;
use App\Services\JwtAuth;
class VideoController extends AbstractController
{
    private function resjson($data){
        //Conseguir el servicio de serializacion (Serializar datos)
        $json = $this->get('serializer')->serialize($data, 'json');
        //Response con HTTP fundation
        $response = new Response();
        //Asginar contenido a la respuesta
        $response->setContent($json);
        //Indicar formato de respuesta
        $response->headers->set('Content-Type', 'application/json');
        //Devolver la respuesta
        return $response;
    }
    public function index()
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/VideoController.php',
        ]);
    }

    public function create(Request $request, JwtAuth $jwt_auth){
        //Recoger el token

        //Comprobar si es correcto

        //Recoger datos por POST

        //Recoger el objeto de usuario identificado

        //Comprobar y validar datos

        //Guardar el nuevo video favorito en la base de datos

        //Devolver respuesta
        $data = [
            'status' => 'error',
            'code'   =>  400,
            'message'=> 'El video no ha podido crearse'
        ];
        return $this->resjson($data);
    }
}
