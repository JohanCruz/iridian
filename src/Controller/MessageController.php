<?php
 
namespace App\Controller; 
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Message;
use App\Entity\User;
use \Datetime;
 
/**
 * @Route("/api", name="api_")
 */
class MessageController extends AbstractController
{
    /**
     * @Route("/message", name="message_index", methods={"GET"})
     */
    public function index(): Response
    {
        $products = $this->getDoctrine()
            ->getRepository(Message::class)
            ->findAll();
 
        $data = [];
 
        foreach ($products as $product) {
           $data[] = [
               'id' => $product->getId(),
               'message' => $product->getMessage(),
               'date' => $product->getDate(),
           ];
        }
 
 
        return $this->json($data);
    }
 
    /**
     * @Route("/message", name="message_new", methods={"POST"})
     */
    public function new(Request $request): Response
    {
        $entityManager = $this->getDoctrine()->getManager();

        $email = $request->request->get('email');
        $user = $entityManager->getRepository(User::class)->findBy(array('email' => $email));
        
        $user = $user ? $user[0]: $user = null;

        $day_now = new DateTime();
        $day_now = $day_now->format('d/m/Y');
        
        $messages = $user ? $entityManager->getRepository(Message::class)->findBy(array('user' => $user->getId())) : null;
        
        $last_message = null;

        if ($messages) {
            $day_message = null;
            if (count($messages) > 1){
                $last_message = $messages[count($messages)-1]->getMessage();
                $last_message .= " ";
                $day_message = strval($messages[count($messages)-1]->getDate()->format('d-m-Y'));
                $last_message .= $day_message;
            } elseif (count($messages) == 1){
                $last_message = strval($messages[0]->getMessage());
                $last_message .= " ";
                $day_message = strval($messages[0]->getDate()->format('d-m-Y'));
                $last_message .= $day_message;                
            }
            if ($last_message){ 
                $day_now = new DateTime();
                $day_now = $day_now->format('d-m-Y');

                if ($day_now == $day_message){
                    return $this->json('Disabled for today to post another message the last one was:'.$last_message); 
                }             
            }
        }

        $message_input = $request->request->get('message'); 
        if(!$message_input){
            return $this->json('Message not found');
        }

        $email_input = $request->request->get('email'); 
        if(!$email_input){
            return $this->json('Email not found');
        }

        $first_name_input = $request->request->get('firstName');
        if(!$first_name_input){
            return $this->json('First name not found');
        }

        $last_name_input = $request->request->get('lastName');
        if(!$last_name_input){
            return $this->json('Last name not found');
        }

        $phone_input = $request->request->get('phone');
        if(!$phone_input){
            return $this->json('Phone not found');
        }

        $area_input = $request->request->get('contactArea');
        if(!$area_input){
            return $this->json('Contact area not found');
        }
        
        $message = new Message();

        $message->setMessage($message_input);
        
        $fecha = date_create();        
        $message->setDate($fecha);        

        if($user){
            $user->addMessage($message);
        } else{
            $user = new User(); 
        }

        $user->addMessage($message);

        $user->setFirstName($first_name_input);
        $user->setLastName($last_name_input);
        $user->setEmail($email_input);
        $user->setPhone($phone_input);
        $user->setContactArea($area_input);
        
        $entityManager->persist($user);
        $entityManager->persist($message);
        $entityManager->flush();
 
        return $this->json('Created new message successfully with id ' . $message->getId());
    }
 
    /**
     * @Route("/message/{id}", name="message_show", methods={"GET"})
     */
    public function show(int $id): Response
    {
        $message = $this->getDoctrine()
            ->getRepository(Message::class)
            ->find($id);
 
        if (!$message) {
 
            return $this->json('No message found for id' . $id, 404);
        }
 
        $data =  [
            'id' => $message->getId(),
            'message' => $message->getMessage(),
            'date' => $message->getDate(),
        ];
         
        return $this->json($data);
    }
 
    /**
     * @Route("/message/{id}", name="message_edit", methods={"PUT"})
     */
    public function edit(Request $request, int $id): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $message = $entityManager->getRepository(Message::class)->find($id);
 
        if (!$message) {
            return $this->json('No message found for id' . $id, 404);
        }
 
        $message->setName($request->request->get('message'));
        $message->setDate($request->request->get('date'));
        $entityManager->flush();
 
        $data =  [
            'id' => $message->getId(),
            'message' => $message->getMessage(),
            'date' => $message->getDate(),
        ];
         
        return $this->json($data);
    }
 
    /**
     * @Route("/message/{id}", name="message_delete", methods={"DELETE"})
     */
    public function delete(int $id): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $message = $entityManager->getRepository(Message::class)->find($id);
 
        if (!$message) {
            return $this->json('No message found for id' . $id, 404);
        }
 
        $entityManager->remove($message);
        $entityManager->flush();
 
        return $this->json('Deleted a message successfully with id ' . $id);
    }
 
 
}
