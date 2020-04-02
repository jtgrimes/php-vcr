<?php

namespace Tests\VCR\Util;

use Tests\TestCase;
use VCR\Response;
use VCR\Request;
use VCR\Util\HttpClient;

class HttpClientTest extends TestCase
{
    public function testCreateHttpClient()
    {
        $this->assertInstanceOf('\VCR\Util\HttpClient', new HttpClient());
    }

    public function testCreateHttpClientWithMock()
    {
        $this->assertInstanceOf('\VCR\Util\HttpClient', new HttpClient());
    }
}
