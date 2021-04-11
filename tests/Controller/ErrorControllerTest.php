<?php

namespace App\Tests\Controller;

use App\Controller\ErrorController;
use Exception;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ErrorControllerTest extends WebTestCase
{
    public function testShouldPrintErrorInJson()
    {
        $errorController = new ErrorController();

        $exception = new Exception('test');
        $response = $errorController->show($exception);

        $this->assertEquals(json_encode(['errorMessage' => 'test'], JSON_PRETTY_PRINT), $response->getContent());
    }
}
