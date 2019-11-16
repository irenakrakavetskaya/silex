<?php

namespace App\Tests;

use Silex\WebTestCase;
use App\Tests\Service\JwtServiceTest;

class PostControllerTest extends WebTestCase
{
    const AUTHOR = [
        //'id' => 1,
        'name' => 'Adam',
        'surname' => 'Smith'
    ];

    public $data = [];

    public function createApplication()
    {
        $app = require __DIR__ . '/../src/app.php';

        return $app;
    }

    public function testAuthorsCreate()
    {
        $client = $this->createClient();
        $client->request(
            'POST',
            '/authors',
            self::AUTHOR,
            [],
            self::setHeaders()
        );

        $this->assertEquals(201, $client->getResponse()->getStatusCode());
    }

    public function testAuthorsIndex()
    {
        $client = $this->createClient();
        $client->request('GET', '/authors');
        $this->assertTrue($client->getResponse()->isOk());

        $data['id'] = json_decode($client->getResponse()->getContent(), true)['_embedded']['items'][0]['id'];
        return $data;
    }

    public function testAuthorsRead()
    {
        $client = $this->createClient();
        $data = $this->testAuthorsIndex();
        $client->request('GET', '/authors/' . $data['id']);
        $this->assertTrue($client->getResponse()->isOk());

        $name = json_decode($client->getResponse()->getContent(), true)['name'];
        $this->assertEquals(self::AUTHOR['name'], $name);
    }

    public static function setHeaders()
    {
        $token = (new JwtServiceTest)->getJwtToken();
        return [

            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $token
        ];
    }
}