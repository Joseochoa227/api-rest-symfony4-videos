<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Constraints\Email;


use App\Entity\User;
use App\Entity\Video;
use App\Services\JwtAuth;

class UserController extends AbstractController
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
        $user_repo = $this->getDoctrine()->getRepository(User::class);
        $video_repo = $this->getDoctrine()->getRepository(Video::class);

        $users = $user_repo->findAll();
        $user = $user_repo->find(1);

        $videos = $video_repo->findAll();

        $data = [
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/UserController.php'
        ];
    /*     foreach($users as $user){
            echo"<h1>{$user->getName()} {$user->getSurname()}</h1>"; 
            foreach($user->getVideos() as $video){
                echo "<p>{$video->getTitle()} - {$video->getUser()->getEmail()}</p>";
            }
        }   */

        // die();

        return $this->resjson($videos);
    }

    public function create(Request $request){
        //Recoger los datos por POST
        $json = $request->get('json', null);
        //Decodificar el json 
        $params = json_decode($json);

        //Respuesta por defecto
        $data = [
            'status' => 'error',
            'code'   =>  200,
            'message'=> 'El usuario no se ha creado',
            'json'   => $params
        ];
        //Comprobar y validar datos
        if($json != null){
            $name       =  (!empty($params->name)) ? $params->name : null;
            $surname    = (!empty($params->surname)) ? $params->surname : null;
            $email      =(!empty($params->email)) ? $params->email : null;
            $password   =(!empty($params->password)) ? $params->password : null;

            $validator = Validation::createValidator();
            $validate_email = $validator->validate($email, [
                new Email()
            ]);
            if(!empty($email) && count($validate_email) == 0 && !empty($password) && !empty($name) && !empty($surname)){
                //Si la validacion es correcta, Crear el objeto del usuario
                $user = new User();
                $user->setName($name);
                $user->setSurname($surname);
                $user->setEmail($email);
                $user->setRole('ROLE_USER');
                $user->setCreatedAt(new \DateTime('now'));

                //Cifrar la contraseña
                $pwd = hash('sha256', $password);
                $user->setPassword($pwd);

                //Comprobar si el usuario existe (Duplicados)
                $doctrine = $this->getDoctrine();
                $em = $doctrine->getManager();

                $user_repo = $doctrine->getRepository(User::class);
                $isset_user = $user_repo->findBy(array(
                    'email' => $email
                ));
                if(count($isset_user)== 0){
                    //Guardar usuario

                    $em->persist($user);
                    $em->flush();
                    $data = [
                        'status' => 'success',
                        'code'   =>  200,
                        'message'=> 'El usuario se ha creado correctamente',
                        'user'   => $user
                    ];
                }else{
                    $data = [
                        'status' => 'error',
                        'code'   =>  400,
                        'message'=> 'El usuario ya existe'
                    ];
                }
                //Si no existe, guardarlo en la base de datos
            }
        }
        //Hacer respuesta en JSON
        return $this->resjson($data);
    }

    public function login(Request $request, JwtAuth $jwt_auth){
        //Recibir los datos por post
        $json = $request->get('json', null);
        $params = json_decode($json);
       
        //Array por defecto para devolver
        $data = [
            'status'    =>  'error',
            'code'      =>  400,
            'message'   =>  'El usuario no se ha podido identificar'
        ];
        //Comprobar y validar datos
        if($json != null){
            $email =  (!empty($params->email)) ? $params->email : null;
            $password =  (!empty($params->password)) ? $params->password : null;
            $gettoken =  (!empty($params->gettoken)) ? $params->gettoken : null;

            $validator = Validation::createValidator();
            $validate_email = $validator->validate($email,[
                new Email()
            ]);

            if(!empty($email) && !empty($password) && count($validate_email) == 0){
                //Cifrar la contraseña
                $pwd = hash('sha256', $password);
                //Si todo es valido, llamaremos a un servicio para identificar al usuario (jwt, token o objeto)
                if($gettoken){
                    $signup = $jwt_auth->signup($email, $pwd, $gettoken);
                }else{
                    $signup = $jwt_auth->signup($email, $pwd);
                }
                return new JsonResponse($signup);
            }
        }

       
//Si nos devuelve todo bien el servicio, respuesta
        return $this->resjson($data);
    }
}
