<?php

namespace App\Controller;

use App\Entity\Profile;
use App\Exception\ApiProblemException;
use Bezhanov\Silex\Routing\Route;
use Lcobucci\JWT\Parser;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ProfileController extends ResourceController
{
    /**
     * @var Parser
     */
    private $jwt;

    public function setJwtParser(Parser $jwt)
    {
        $this->jwt = $jwt;
    }

    /**
     * @Route("/profile", methods={"POST"})
     */
    public function updateAction(Request $request): Response
    {
        $expectedParameters = ['old_password', 'new_password'];
        $requestBody = $this->extractRequestBody($request, $expectedParameters);

        list($bearer, $token) = explode(' ', $request->headers->get('Authorization'));

        $token = $this->jwt->parse($token);
        $profile = $this->findOrFail($token->getClaim('uid'));

        if (!password_verify($requestBody['old_password'], $profile->getPassword())) {
            throw new ApiProblemException(ApiProblemException::TYPE_INVALID_PASSWORD);
        }
        $newPassword = !empty($requestBody['new_password']) ? password_hash($requestBody['new_password'], PASSWORD_DEFAULT) : '';
        $profile->setPassword($newPassword);
        $violations = $this->validator->validate($profile);

        if ($violations->count() > 0) {
            throw new ApiProblemException(ApiProblemException::TYPE_VALIDATION_ERROR);
        }

        $this->em->flush();

        return $this->createApiResponse('The password was updated successfully', Response::HTTP_OK);
    }


    protected function getEntityClassName(): string
    {
        return Profile::class;
    }
}
