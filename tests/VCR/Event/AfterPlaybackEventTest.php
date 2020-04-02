<?php

namespace Tests\VCR\Event;

use VCR\Event\AfterPlaybackEvent;
use VCR\Request;
use VCR\Cassette;
use VCR\Configuration;
use VCR\Storage;
use VCR\Response;
use VCR\Storage\Blackhole;

class AfterPlaybackEventTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AfterPlaybackEvent
     */
    private $event;

    protected function setUp()
    {
        $this->event = new AfterPlaybackEvent(
            new Request('GET', 'http://example.com'),
            new Response(200),
            new Cassette('test', new Configuration(), new Blackhole())
        );
    }

    public function testGetRequest()
    {
        $this->assertInstanceOf('VCR\Request', $this->event->getRequest());
    }

    public function testGetResponse()
    {
        $this->assertInstanceOf('VCR\Response', $this->event->getResponse());
    }

    public function testGetCassette()
    {
        $this->assertInstanceOf('VCR\Cassette', $this->event->getCassette());
    }
}
