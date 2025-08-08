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
        // Check that Vue.js app container exists as main content
        $this->assertSelectorExists('#app');
        $this->assertSelectorExists('.min-h-screen'); // Test basic layout class exists
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
        $this->assertSelectorExists('.w-full'); // Vue app container class
    }

    public function testVueJSIntegration(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/');

        $this->assertResponseIsSuccessful();
        
        // Check that Vue.js JavaScript file is included
        $this->assertSelectorExists('script[src*="js/app.js"]');
        
        // Check that Vue.js app container exists
        $this->assertSelectorExists('#app');
        
        // Check that the page title reflects Vue.js integration
        $this->assertSelectorTextContains('title', 'Vue.js & TailwindCSS Test');
        
        // Verify Vue.js app container has proper classes
        $this->assertSelectorExists('#app.w-full');
    }
}