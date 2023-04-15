<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
//use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Users;

#[Route('/api', name: 'api_')]
class UsersController extends AbstractController
{
    #[Route('/users', name: 'users_index', methods: ['GET'])]
    public function index(ManagerRegistry $doctrine): Response
    {
        $users = $doctrine
        ->getRepository(Users::class)
        ->findAll();

    $data = [];

    foreach ($users as $user) {
       $data[] = [
           'id' => $user->getId(),
           'firstname' => $user->getFirstname(),
           'lastname' => $user->getLastname(),
           'email' => $user->getEmail(),
           'password' => $user->getPassword(),
       ];
    }


    return $this->json($data);
    }
    

    #[Route('/auth', name:'login', methods:["POST"])]
    public function login(Request $request, ManagerRegistry $doctrine): Response
    {
        $params = json_decode($request->getContent(), true);
        $user = $doctrine->getRepository(Users::class)->findOneBy(['email' => $params['email']]);
        if (!$user) {
            throw $this->createNotFoundException(
                'No user found for email '.$params['email']
            );
        }
        if ($user->getPassword() == $params['password']) {
            return $this->json([
                'user' => ['id' => $user->getId(), 'firstname' => $user->getFirstname(), 'lastname' => $user->getLastname(), 'email' => $user->getEmail(), 'token' => 'token'],
            ]);
        } else {
            return $this->json([
                'message' => 'Wrong password',
            ]);
        }
    } 
    

    #[Route('/users', name: 'users_new', methods: ['POST'])]
    public function new(Request $request, ManagerRegistry $doctrine): Response
    {
        $entityManager = $doctrine->getManager();

        $params = json_decode($request->getContent(), true);


        $user = new Users();
        $user->setFirstname($params['firstname']);
        $user->setLastname($params['lastname']);
        $user->setEmail($params['email']);
        $user->setPassword($params['password']);

        $entityManager->persist($user);
        $entityManager->flush();

        return $this->json([
            'message' => 'User created',
            'id' => $user->getId(),
            'firstname' => $user->getFirstname(),
            'lastname' => $user->getLastname(),
            'email' => $user->getEmail(),
            'password' => $user->getPassword(),
        ]);
    }

    #[Route('/users/{id}', name: 'users_show', methods: ['GET'])]
    public function show(ManagerRegistry $doctrine, int $id): Response
    {
        $user = $doctrine->getRepository(Users::class)->find($id);
        if (!$user) {
            throw $this->createNotFoundException(
                'No user found for id '.$id
            );
        }
        $data = [
            'id' => $user->getId(),
            'firstname' => $user->getFirstname(),
            'lastname' => $user->getLastname(),
            'email' => $user->getEmail(),
            'password' => $user->getPassword(),
        ];
        return $this->json($data);
        
    }

    #[Route('/users/{id}', name: 'users_edit', methods: ['PUT'])]
    public function edit(Request $request, ManagerRegistry $doctrine, int $id): Response
    {
        $entityManager = $doctrine->getManager();
        $user = $entityManager->getRepository(Users::class)->find($id);
        $params = json_decode($request->getContent(), true);

        if (!$user) {
            throw $this->createNotFoundException(
                'No user found for id '.$id
            );
        }

        $user->setFirstname($params['firstname']);
        $user->setLastname($params['lastname']);
        $user->setEmail($params['email']);
        $user->setPassword($params['password']);

        $entityManager->flush();
        
        $data = [
            'id' => $user->getId(),
            'firstname' => $user->getFirstname(),
            'lastname' => $user->getLastname(),
            'email' => $user->getEmail(),
            'password' => $user->getPassword(),
        ];
        return $this->json($data);
    }

    #[Route('/users/{id}', name: 'users_delete', methods: ['DELETE'])]
    public function delete(ManagerRegistry $doctrine, int $id): Response
    {
        $entityManager = $doctrine->getManager();
        $user = $entityManager->getRepository(Users::class)->find($id);

        if (!$user) {
            throw $this->createNotFoundException(
                'No user found for id '.$id
            );
        }

        $entityManager->remove($user);
        $entityManager->flush();

        return $this->json([
            'message' => 'User deleted',
            'id' => $user->getId(),
            'firstname' => $user->getFirstname(),
            'lastname' => $user->getLastname(),
            'email' => $user->getEmail(),
            'password' => $user->getPassword(),
        ]);
    }
}
