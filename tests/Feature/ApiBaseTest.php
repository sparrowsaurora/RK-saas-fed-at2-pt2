<?php

it('returns a successful response', function () {
    $response = $this->get('/api/v3/test/base');

    $response->assertStatus(200);
});
