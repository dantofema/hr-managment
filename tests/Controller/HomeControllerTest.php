<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class HomeControllerTest extends WebTestCase
{
    public function testHomePage(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'TailwindCSS Integration Test');
        $this->assertSelectorExists('.bg-blue-500'); // Test TailwindCSS class exists
        $this->assertSelectorExists('.bg-green-500'); // Test TailwindCSS class exists
    }

    public function testTailwindCSSIsLoaded(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/');

        $this->assertResponseIsSuccessful();
        
        // Check that the TailwindCSS stylesheet is included
        $this->assertSelectorExists('link[href*="css/app.css"]');
        
        // Check that TailwindCSS classes are present in the HTML
        $this->assertSelectorExists('.min-h-screen');
        $this->assertSelectorExists('.bg-gray-100');
        $this->assertSelectorExists('.text-2xl');
    }
}