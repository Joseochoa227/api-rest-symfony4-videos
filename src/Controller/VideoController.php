<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Constraints\Email;
use Knp\Component\Pager\PaginatorInterface;

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
        //datos por defecto
        $data = [
            'status' => 'error',
            'code'   =>  400,
            'message'=> 'El video no ha podido crearse'
        ];
        
        //Recoger el token
        $token = $request->headers->get('Authorization');
        //Comprobar si es correcto
        $authCheck = $jwt_auth->checkToken($token);

        if($authCheck){
            //Recoger datos por POST
            $json = $request->get('json', null);
            $params = json_decode($json);

             //Recoger el objeto de usuario identificado
             $identity = $jwt_auth->checkToken($token, true);

            //Comprobar y validar datos
            if(!empty($json)){
                $user_id      = ($identity->sub != null) ? $identity->sub : null;
                $title        = (!empty($params->title)) ? $params->title : null;
                $description  = (!empty($params->description)) ? $params->description : null;
                $url          =(!empty($params->url)) ? $params->url : null;

                if(!empty($user_id) && !empty($title)){
                    //Guardar el nuevo video favorito en la base de datos
                    $em = $this->getDoctrine()->getManager();
                    $user = $this->getDoctrine()->getRepository(User::class)->findOneBy([
                        'id'    => $user_id
                    ]);
                    // crear y guardar objeto

                    $video = new Video();
                    $video->setUser($user);
                    $video->setTitle($title);
                    $video->setDescription($description);
                    $video->setUrl($url);
                    $video->setStatus('Normal');
                    
                    $createdAt = new \Datetime('now');
                    $updatedAt = new \Datetime('now');

                    $video->setCreatedAt($createdAt);
                    $video->setUpdatedAt($updatedAt);

                    //Guardar en BD

                    $em->persist($video);
                    $em->flush();
                    $data = [
                        'status' => 'success',
                        'code'   =>  200,
                        'message'=> 'El video se ha guardado',
                        'video'  => $video
                    ];
                }
            }
        }
        //Devolver respuesta
       
        return $this->resjson($data);
    }

    public function videos(Request $request, JwtAuth $jwt_auth, PaginatorInterface $paginator){
        //Recoger la cabecera de autenticacion
        $token = $request->headers->get('Authorization');
        //Comprobar el token 
        $authCheck = $jwt_auth->checkToken($token);
        //Si es valido
        if($authCheck){
            //Conseguir la identidad del usuario
            $identity = $jwt_auth->checkToken($token, true);    
            $em = $this->getDoctrine()->getManager();

            //Hacer una consulta para paginar
            $dql = "SELECT v FROM App\Entity\Video v WHERE v.user = {$identity->sub} ORDER BY v.id DESC";
            $query = $em->createQuery($dql);
            //Recoger el parametro page de la URL
            $page   = $request->query->getInt('page', 1);
            $items_per_page = 5;

            //Invocar paginacion

            $pagination = $paginator->paginate($query, $page, $items_per_page);
            $total = $pagination->getTotalItemCount();

            //Preparar array de datos para devolver
            $data = [
                'status' => 'success',
                'code'   =>  200,
                'total_items_count'  => $total,
                'page_actual'   => $page,
                'items_per_page'    => $items_per_page,
                'total_pages'       => ceil($total/ $items_per_page),
                'videos'            => $pagination,
                'user_id'           => $identity->sub
            ];
        } else{
            //Si falla devolver error
            $data = [
                'status' => 'error',
                'code'   =>  400,
                'message'=> 'No se ha podido cargar la lista de videos'
            ];
        }
        
        return $this->resjson($data);
    }

    public function video(Request $request, JwtAuth $jwt_auth, $id = null){
        $data = [
            'status' => 'error',
            'code'   =>  400,
            'message'=> 'No se ha podido encontrar el video'
        ];
    }
}
