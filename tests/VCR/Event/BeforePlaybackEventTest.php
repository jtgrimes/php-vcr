<?php

namespace Tests\VCR\Event;

use Tests\TestCase;
use VCR\Event\BeforePlaybackEvent;
use VCR\Request;
use VCR\Cassette;
use VCR\Configuration;
use VCR\Storage;

class BeforePlaybackEventTest extends TestCase
{
    /**
     * @var BeforePlaybackEvent
     */
    private $event;

    protected function setUp() : void
    {
        $this->event = new BeforePlaybackEvent(
            new Request('GET', 'http://example.com'),
            new Cassette('test', new Configuration(), new Storage\Blackhole())
        );
    }

    public function testGetRequest()
    {
        $this->assertInstanceOf('VCR\Request', $this->event->getRequest());
    }

    public function testGetCassette()
    {
        $this->assertInstanceOf('VCR\Cassette', $this->event->getCassette());
    }
}
