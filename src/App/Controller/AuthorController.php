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

class AuthorController extends ResourceController
{
    /**
     * @Route("/authors", methods={"GET"}, name="list_authors")
     */
    public function indexAction(Request $request)  //perhaps delete pagination
    {
        $queryBuilder = $this->em->createQueryBuilder()->select('f')->from($this->getEntityClassName(), 'f')->addOrderBy('f.id');
        $adapter = new DoctrineORMAdapter($queryBuilder);
        $pager = new Pagerfanta($adapter);
        $pager->setCurrentPage($request->query->get('page', 1))->setMaxPerPage($request->query->get('limit', 30));
        $factory = new PagerfantaFactory();
        $collection = $factory->createRepresentation($pager, new \Hateoas\Configuration\Route('list_authors'));

        return $this->createApiResponse($collection, Response::HTTP_OK);
    }

    /**
     * @Route("/authors", methods={"POST"})
     */
    public function createAction(Request $request)
    {
        $expectedParameters = ['name', 'surname'];
        $requestBody =$request->request->all();

        $author = new Author();

        $author->fromArray($requestBody, $expectedParameters);
        $violations = $this->validator->validate($author);

        if ($violations->count() > 0) {
            throw new ApiProblemException(ApiProblemException::TYPE_VALIDATION_ERROR);
        }

        if($requestBody['book']){
            foreach($requestBody['book'] as $k=>$v ){
                $book= new Book();
                $book->setTitle($requestBody['book'][$k]['title']);
                $book->setDescription($requestBody['book'][$k]['description']);
                $author->addBook($book);
                $this->em->persist($book);
            }
        }

        $this->em->persist($author);
        $this->em->flush();

        return $this->createApiResponse('Successfully created', Response::HTTP_CREATED, [
            'Location' => sprintf('/authors/%d', $author->getId())
        ]);
    }

    /**
     * @Route("/authors/{id}", methods={"GET"})
     */
    public function readAction(int $id)
    {
        $author = $this->findOrFail($id);
        //$book->authors = $book->getAuthors();

        return $this->createApiResponse($author, Response::HTTP_OK);
    }

    /**
     * @Route("/authors/{id}", methods={"POST"}, requirements={"id": "\d+"})
     */
    public function updateAction(Request $request, int $id)
    {
        $expectedParameters = ['name', 'surname'];
        $requestBody = $request->request->all();

        $author = $this->findOrFail($id);

        $author->fromArray($requestBody, $expectedParameters);
        $violations = $this->validator->validate($author);

        if ($violations->count() > 0) {
            throw new ApiProblemException(ApiProblemException::TYPE_VALIDATION_ERROR);
        }

        if($requestBody['book']){
            foreach($requestBody['book'] as $k=>$v ){
                /*$book = //$em->find('Author', $requestBody['author'][$k]['id']);//get from authorContr
                    //$author = new Author(4);
                $title = $requestBody['book'][$k]['title'] ?? null;
                $description = $requestBody['book'][$k]['description'] ?? null;
                if ($title){
                    $book->setName($title);
                }
                if ($description){
                    $book->setSurname($description);
                }
                //$author->addAuthor($book);
                $this->em->persist($book);*/
            }
        }

        $this->em->flush();

        return $this->createApiResponse($author, Response::HTTP_OK);
    }

    /**
     * @Route("/authors/remove/{id}", methods={"POST"})
     */
    public function deleteAction(int $id)
    {
        $author = $this->findOrFail($id);

        //return print_r($author->books->get('id'));
        if(!$author->getBooks()){
            $this->em->remove($author);
            $this->em->flush();

            return $this->createApiResponse(null, Response::HTTP_OK);
        }

        return $this->createApiResponse("You should remove connected books first", Response::HTTP_OK);
    }

    protected function getEntityClassName(): string
    {
        return Author::class;
    }
}
