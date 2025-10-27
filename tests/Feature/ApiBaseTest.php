<?php

it('returns a successful response', function () {
    $response = $this->get('/api/v3');

    $response->assertStatus(200);
});
