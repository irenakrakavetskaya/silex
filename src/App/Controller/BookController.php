<?php

namespace App\Controller;

use App\Entity\Book;
use App\Entity\Author;
use App\Entity\Manufacturer;
use App\Exception\ApiProblemException;
use Bezhanov\Silex\Routing\Route;
use Hateoas\Representation\Factory\PagerfantaFactory;
use Monolog\Logger;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class BookController extends ResourceController
{
    /**
     * @Route("/books", methods={"GET"}, name="list_books")
     */
    public function indexAction(Request $request)  //perhaps delete pagination
    {
        $queryBuilder = $this->em->createQueryBuilder()->select('f')->from($this->getEntityClassName(), 'f')->addOrderBy('f.id');
        $adapter = new DoctrineORMAdapter($queryBuilder);
        $pager = new Pagerfanta($adapter);
        $pager->setCurrentPage($request->query->get('page', 1))->setMaxPerPage($request->query->get('limit', 30));
        $factory = new PagerfantaFactory();
        $collection = $factory->createRepresentation($pager, new \Hateoas\Configuration\Route('list_books'));

        return $this->createApiResponse($collection, Response::HTTP_OK);
    }

    /**
     * @Route("/books", methods={"POST"})
     */
    public function createAction(Request $request)
    {
        $expectedParameters = ['title', 'description'];
        $requestBody =$request->request->all();

        $book = new Book();

        $book->fromArray($requestBody, $expectedParameters);
        $violations = $this->validator->validate($book);

        if ($violations->count() > 0) {
            throw new ApiProblemException(ApiProblemException::TYPE_VALIDATION_ERROR);
        }

        if($requestBody['author']){
            foreach($requestBody['author'] as $k=>$v ){
                $author = new Author();
                $author->setName($requestBody['author'][$k]['name']);
                $author->setSurname($requestBody['author'][$k]['surname']);
                $book->addAuthor($author);
                $this->em->persist($author);
                //return print_r($book);
            }
        }

        $this->em->persist($book);
        $this->em->flush();

        return $this->createApiResponse('Successfully created', Response::HTTP_CREATED, [
            'Location' => sprintf('/books/%d', $book->getId())
        ]);
    }

    /**
     * @Route("/books/{id}", methods={"GET"})
     */
    public function readAction(int $id)
    {
        $book = $this->findOrFail($id);
        //$book->authors = $book->getAuthors();

        return $this->createApiResponse($book, Response::HTTP_OK);
    }

    /**
     * @Route("/books/{id}", methods={"POST"}, requirements={"id": "\d+"})
     */
    public function updateAction(Request $request, int $id)
    {
        $expectedParameters = ['title', 'description'];
        $requestBody = $request->request->all();

        //$requestBody = $this->extractRequestBody($request, $expectedParameters);
        //$requestBody['manufacturer'] = $this->em->getReference(Manufacturer::class, $requestBody['manufacturer_id']);
        //array_splice($expectedParameters, -1, 1, ['manufacturer']);

        $book = $this->findOrFail($id);

        $book->fromArray($requestBody, $expectedParameters);
        $violations = $this->validator->validate($book);

        if ($violations->count() > 0) {
            throw new ApiProblemException(ApiProblemException::TYPE_VALIDATION_ERROR);
        }

        if($requestBody['author']){
            foreach($requestBody['author'] as $k=>$v ){
               /*$author = //$em->find('Author', $requestBody['author'][$k]['id']);//get from authorContr
               //$author = new Author(4);
                $name = $requestBody['author'][$k]['name'] ?? null;
                $surname = $requestBody['author'][$k]['surname'] ?? null;
                if ($name){
                    $author->setName($name);
                }
                if ($surname){
                    $author->setSurname($surname);
                }
                //$book->addAuthor($author);
                $this->em->persist($author);*/
            }
        }

        $this->em->flush();

        return $this->createApiResponse($book, Response::HTTP_OK);
    }

    /**
     * @Route("/books/remove/{id}", methods={"POST"})
     */
    public function deleteAction(int $id)
    {
        $book = $this->findOrFail($id);
        $this->em->remove($book);
        $this->em->flush();

        return $this->createApiResponse(null, Response::HTTP_OK);
    }

    protected function getEntityClassName(): string
    {
        return Book::class;
    }
}
