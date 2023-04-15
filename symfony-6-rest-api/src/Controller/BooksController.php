<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
//use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Books;
use App\Entity\Chapter;
use App\Entity\Images;
use App\Entity\Users;

#[Route('/api', name: 'api_')]

class BooksController extends AbstractController
{
    #[Route('/books', name: 'books_index', methods: ['GET'])]
    public function index(ManagerRegistry $doctrine): Response
    {
        $books = $doctrine->getRepository(Books::class)->findAll();
        
        $chapters = $doctrine->getRepository(Chapter::class)->findAll();
        $images = $doctrine->getRepository(Images::class)

        ->findAll();

    $data = [];
    foreach($books as $book) {
        $data[] = [
            'id' => $book->getId(),
            'name' => $book->getName(),
            'idUser' => $book->getIdUser(),

        ];
    }
    foreach($chapters as $chapter) {
        $data[] = [
            'id' => $chapter->getId(),
            'text' => $chapter->getText(),
            'idBook' => $chapter->getIdBook(),
            'position' => $chapter->getPosition(),
            
        ];
    }
    foreach($images as $image) {
        $data[] = [
            'id' => $image->getId(),
            'url' => $image->getUrl(),
            'idBook' => $image->getIdBook(),
            'position' => $image->getPosition(),
        ];
    }
    return $this->json($data);
}

#[Route('/books/user/{id}', name: 'books_new', methods: ['POST'])]
public function new(Request $request, ManagerRegistry $doctrine,int $id): Response
{
    $params = json_decode($request->getContent(), true);
    $entityManager = $doctrine->getManager();
    $user = $entityManager->getRepository(Users::class)->find($id);

    $book = new Books();
    $chapters = new Chapter();
    $images = new Images();
    $book->setName($params['name']);
    $book->setIdUser($user->getId());
    $entityManager->persist($book);
    $entityManager->flush();

    
    $chapters->setText($params['chapter']);
    $chapters->setIdBook($book->getId());
    $chapters->setPosition(1);

    $entityManager->persist($chapters);
    
    
    $images->setUrl($params['img']);
    $images->setIdBook($book->getId());
    $images->setPosition(1);
    $entityManager->persist($images);
    $entityManager->flush();


    return $this->json([
        'message' => 'Book created',
        'id' => $book->getId(),
    ]);
}

 #[Route('/books/user/{id}', name: 'books_show', methods: ['GET'])]
    public function show(ManagerRegistry $doctrine, int $id): Response
    {
        $books = $doctrine->getRepository(Books::class)->findBy(array('idUser'=>$id));
        $data = array();

        foreach($books as $book) {
            
            $chapters = $doctrine->getRepository(Chapter::class)->findBy(array('idBook'=>$book->getId()));
            $images = $doctrine->getRepository(Images::class)->findBy(array('idBook'=>$book->getId()));
            $chap = array();
            foreach($chapters as $chapter) {
                $chap[] = [
                    'id' => $chapter->getId(),
                    'text' => $chapter->getText(),
                    'idBook' => $chapter->getIdBook(),
                    'position' => $chapter->getPosition(),
                ];
            }

            $img = array();
            foreach($images as $image) {
                $img[] = [
                    'id' => $image->getId(),
                    'url' => $image->getUrl(),
                    'idBook' => $image->getIdBook(),
                    'position' => $image->getPosition(),
                ];
            }

            $data[] = [
                'id' => $book->getId(),
                'name' => $book->getName(),
                'idUser' => $book->getIdUser(),
                'chapters' => $chap,
                'images' => $img,
            ];
        }
        return $this->json($data);
    }

    #[Route('/books/chapters/{id}', name: 'books_edit', methods: ['POST'])]
    public function edit(Request $request, ManagerRegistry $doctrine, int $id): Response
    {
        $params = json_decode($request->getContent(), true);
        $entityManager = $doctrine->getManager();
        $book = $entityManager->getRepository(Books::class)->find($id);
        $chapters = new Chapter();
        $images = new Images();
        
        if($book){

            $chapters->setText($params['chapter']);
            $chapters->setPosition($params['position']);
            $chapters->setIdBook($book->getId());
            
            
            $images->setUrl($params['img']);
            $images->setPosition($params['position']);
            $images->setIdBook($book->getId());
            $entityManager->persist($images);
            $entityManager->persist($chapters);
            $entityManager->flush();
        }
        
        $book = $entityManager->getRepository(Books::class)->find($id);
        $next = [
            'text'=> $params['chapter'],
            'url'=> $params['img'],

        ];
        return $this->json([
            'next' => $next,
        ]);
    }
    #[Route('/books/{id}', name: 'books_indexOne', methods: ['GET'])]
    public function indexOne(ManagerRegistry $doctrine, int $id): Response
    {
        $book = $doctrine->getRepository(Books::class)->find($id);
        $chapters = $doctrine->getRepository(Chapter::class)->findBy(array('idBook'=>$id));
        $images = $doctrine->getRepository(Images::class)->findBy(array('idBook'=>$id));
        $chap = array();
        foreach($chapters as $chapter) {
            $chap[] = [
                'id' => $chapter->getId(),
                'text' => $chapter->getText(),
                'idBook' => $chapter->getIdBook(),
                'position' => $chapter->getPosition(),
            ];
        }

        $img = array();
        foreach($images as $image) {
            $img[] = [
                'id' => $image->getId(),
                'url' => $image->getUrl(),
                'idBook' => $image->getIdBook(),
                'position' => $image->getPosition(),
            ];
        }

        $data = [
            'id' => $book->getId(),
            'name' => $book->getName(),
            'idUser' => $book->getIdUser(),
            'chapters' => $chap,
            'images' => $img,
        ];
        return $this->json($data);
    }

}
