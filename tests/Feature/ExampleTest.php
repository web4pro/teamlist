<?php

namespace Tests\Feature;

use Tests\TestCase;
class ExampleTest extends TestCase
{
    public function testGuestsAreRedirectedFromTheApp()
    {
        $response = $this->get('/');

        $response->assertRedirect(route('login'));
    }
}
