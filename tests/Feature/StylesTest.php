<?php

it('renders the login page', function () {
    $response = $this->get('/login');
    $response->assertStatus(200);
    $response->assertSee('Gestor Barber');
});
