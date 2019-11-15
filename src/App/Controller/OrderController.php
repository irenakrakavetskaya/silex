<?php

namespace App\Controller;

use App\Entity\Book;
use App\Entity\Order;
use App\Exception\ApiProblemException;
use Bezhanov\Silex\Routing\Route;
use Hateoas\Representation\Factory\PagerfantaFactory;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class OrderController extends ResourceController
{
    /**
     * @Route("/orders", methods={"GET"}, name="list_orders")
     */
    public function indexAction(Request $request)  //perhaps delete pagination
    {
        $queryBuilder = $this->em->createQueryBuilder()->select('f')->from($this->getEntityClassName(), 'f')->addOrderBy('f.id');
        $adapter = new DoctrineORMAdapter($queryBuilder);
        $pager = new Pagerfanta($adapter);
        $pager->setCurrentPage($request->query->get('page', 1))->setMaxPerPage($request->query->get('limit', 30));
        $factory = new PagerfantaFactory();
        $collection = $factory->createRepresentation($pager, new \Hateoas\Configuration\Route('list_orders'));

        return $this->createApiResponse($collection, Response::HTTP_OK);
    }

    /**
     * @Route("/orders", methods={"POST"})
     */
    public function createAction(Request $request)
    {
        $expectedParameters = ['status', 'phone', 'address', 'timezone'];
        $requestBody = $request->request->all();
        $order = new Order();
        $order->fromArray($requestBody, $expectedParameters);
        $violations = $this->validator->validate($order);
        if ($violations->count() > 0) {
            throw new ApiProblemException(ApiProblemException::TYPE_VALIDATION_ERROR);
        }

        foreach ($requestBody['book'] as $k => $v) {
            $book = $this->em->find(Book::class, $requestBody['book'][$k]['id']);
            if($book){
                $order->addBook($book);
            }

        }

        $order->setDatetime($request->get('datetime'));

        $this->em->persist($order);
        $this->em->flush();

        return $this->createApiResponse('Successfully created', Response::HTTP_CREATED, [
            'Location' => sprintf('/orders/%d', $order->getId())
        ]);
    }

    /**
     * @Route("/orders/{id}", methods={"GET"})
     */
    public function readAction(int $id)
    {
        $book = $this->findOrFail($id);
        //$book->authors = $book->getAuthors();

        return $this->createApiResponse($book, Response::HTTP_OK);
    }

    /**
     * @Route("/orders/{id}", methods={"POST"}, requirements={"id": "\d+"})
     */
    public function updateAction(Request $request, int $id)
    {
        $expectedParameters = ['status', 'phone', 'address', 'timezone'];
        $requestBody = $request->request->all();

        $order = $this->findOrFail($id);
        $order->fromArray($requestBody, $expectedParameters);
        $violations = $this->validator->validate($order);

        if ($violations->count() > 0) {
            throw new ApiProblemException(ApiProblemException::TYPE_VALIDATION_ERROR);
        }

        foreach ($requestBody['book'] as $k => $v) {
            $book = $this->em->find(Book::class, $requestBody['book'][$k]['id']);
             if(!$order->getBooks()->contains($book)){
                 $order->addBook($book);
             }
        }

        $order->setDatetime($request->get('datetime'));
        $this->em->flush();

        return $this->createApiResponse($order, Response::HTTP_OK);
    }

    /**
     * @Route("/orders/remove/{id}", methods={"POST"})
     */
      public function deleteAction(int $id)
      {
          $order = $this->findOrFail($id);
          $this->em->remove($order);
          $this->em->flush();

          return $this->createApiResponse(null, Response::HTTP_OK);
      }

    protected function getEntityClassName(): string
    {
        return Order::class;
    }
}
