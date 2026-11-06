<?php

test('the application redirects the root page to PensionGuard', function () {
    $response = $this->get('/');

    $response->assertRedirect('/pension-guard');
});