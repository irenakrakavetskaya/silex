<?php

namespace App\Controller;

use App\Entity\Author;
use App\Exception\ApiProblemException;
use Hateoas\Representation\Factory\PagerfantaFactory;
use Bezhanov\Silex\Routing\Route;
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
        $requestBody = $request->request->all();

        $author = new Author();

        $author->fromArray($requestBody, $expectedParameters);
        $violations = $this->validator->validate($author);

        if ($violations->count() > 0) {
            throw new ApiProblemException(ApiProblemException::TYPE_VALIDATION_ERROR);
        }

        $this->em->persist($author);
        $this->em->flush();

        return $this->createApiResponse('The author was successfully created', Response::HTTP_CREATED, [
            'Location' => sprintf('/authors/%d', $author->getId())
        ]);
    }

    /**
     * @Route("/authors/{id}", methods={"GET"})
     */
    public function readAction(int $id)
    {
        $author = $this->findOrFail($id);

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

        $this->em->flush();

        return $this->createApiResponse($author, Response::HTTP_OK);
    }

    /**
     * @Route("/authors/remove/{id}", methods={"POST"})
     */
    public function deleteAction(Request $request, int $id)
    {
        $author = $this->findOrFail($id);

        if ($author->getBooks()->count()) {
            return $this->createApiResponse('You should remove connected books first', Response::HTTP_OK);
        }

        $this->em->remove($author);
        $this->em->flush();

        return $this->createApiResponse('The author was successfully removed', Response::HTTP_OK);
    }

    protected function getEntityClassName(): string
    {
        return Author::class;
    }
}
