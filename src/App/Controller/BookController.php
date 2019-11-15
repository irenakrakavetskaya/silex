<?php

namespace App\Controller;

use App\Entity\Book;
use App\Entity\Author;
use App\Exception\ApiProblemException;
//use Bezhanov\Silex\Routing\Route;
use Hateoas\Representation\Factory\PagerfantaFactory;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class BookController extends ResourceController
{
    /**
     * @Route("/books", methods={"GET"}, name="list_books")
     */
    public function indexAction(Request $request) //?title={title}
    {
        $title = $request->get('title');
        if($title) {
            $queryBuilder = $this->em->createQueryBuilder()
                ->select('b')
                ->from($this->getEntityClassName(), 'b')
                ->where('b.title like :title')
                ->setParameter('title', '%' . $title . '%')
                ->addOrderBy('b.id');
        } else {
            $queryBuilder = $this->em->createQueryBuilder()->select('b')->from($this->getEntityClassName(), 'b')->addOrderBy('b.id');
        }
            $adapter = new DoctrineORMAdapter($queryBuilder);
            $pager = new Pagerfanta($adapter);
            $pager->setCurrentPage($request->query->get('page', 1))->setMaxPerPage($request->query->get('limit', 30));
            $factory = new PagerfantaFactory();
            $collection = $factory->createRepresentation($pager, new \Hateoas\Configuration\Route('list_authors'));

            return $this->createApiResponse($collection, Response::HTTP_OK);
    }

    /**
     * @Route("/books", methods={"POST"})
     */
    public function createAction(Request $request)
    {
        $expectedParameters = ['title', 'description'];
        $requestBody = $request->request->all();

        $book = new Book();

        $book->fromArray($requestBody, $expectedParameters);
        $violations = $this->validator->validate($book);

        if ($violations->count() > 0) {
            throw new ApiProblemException(ApiProblemException::TYPE_VALIDATION_ERROR);
        }

        if ($requestBody['author']) {
            foreach ($requestBody['author'] as $k => $v) {
                $author = $this->em->find(Author::class, $requestBody['author'][$k]['id']);
                if($author){
                    $book->addAuthor($author);
                }
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

        $book = $this->findOrFail($id);
        $book->fromArray($requestBody, $expectedParameters);
        $violations = $this->validator->validate($book);

        if ($violations->count() > 0) {
            throw new ApiProblemException(ApiProblemException::TYPE_VALIDATION_ERROR);
        }

        foreach ($requestBody['author'] as $k => $v) {
            $author = $this->em->find(Author::class, $requestBody['author'][$k]['id']);
            if (!$book->getAuthors()->contains($author)) {
                $book->addAuthor($author);
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

        if ($book->getOrders()->count()) {
            return $this->createApiResponse('You should remove connected orders first', Response::HTTP_OK);
        }

        $this->em->remove($book);
        $this->em->flush();

        return $this->createApiResponse(null, Response::HTTP_OK);
    }

    protected function getEntityClassName(): string
    {
        return Book::class;
    }
}
