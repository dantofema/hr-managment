<?php

namespace App\Tests\Employee\Infrastructure\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class DeleteEmployeeControllerTest extends WebTestCase
{
    public function testDeleteEmployeeNotFound(): void
    {
        $client = static::createClient();
        $client->request('DELETE', '/employees/non-existent-id');
        $this->assertEquals(Response::HTTP_NOT_FOUND, $client->getResponse()->getStatusCode());
    }

    // Para un test de éxito real, se debe preparar un empleado en la base de datos o mockear el repositorio.
    // Este es un ejemplo básico:
    // public function testDeleteEmployeeSuccess(): void
    // {
    //     $client = static::createClient();
    //     // Suponiendo que existe un empleado con ID 'existing-id'
    //     $client->request('DELETE', '/employees/existing-id');
    //     $this->assertEquals(Response::HTTP_NO_CONTENT, $client->getResponse()->getStatusCode());
    // }
}
