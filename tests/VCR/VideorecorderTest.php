<?php

namespace Tests\VCR;

use lapistano\ProxyObject\ProxyBuilder;
use org\bovigo\vfs\vfsStream;
use Tests\TestCase;
use VCR\Configuration;
use VCR\Request;
use VCR\Response;
use VCR\Util\HttpClient;
use VCR\VCR;
use VCR\VCRFactory;
use VCR\Videorecorder;

class VideorecorderTest extends TestCase
{
    public function testCreateVideorecorder()
    {
        $this->assertInstanceOf(
            '\VCR\Videorecorder',
            new Videorecorder(new Configuration(), new HttpClient(), VCRFactory::getInstance())
        );
    }

    public function testInsertCassetteEjectExisting()
    {
        vfsStream::setup('testDir');
        $factory = VCRFactory::getInstance();
        $configuration = $factory->get('VCR\Configuration');
        $configuration->setCassettePath(vfsStream::url('testDir'));
        $configuration->enableLibraryHooks(array());
        $videorecorder = $this->getMockBuilder('\VCR\Videorecorder')
            ->setConstructorArgs(array($configuration, new HttpClient(), VCRFactory::getInstance()))
            ->setMethods(array('eject'))
            ->getMock();

        $videorecorder->expects($this->exactly(2))->method('eject');

        $videorecorder->turnOn();
        $videorecorder->insertCassette('cassette1');
        $videorecorder->insertCassette('cassette2');
        $videorecorder->turnOff();
    }

    public function testHandleRequestRecordsRequestWhenModeIsNewRecords()
    {
        $request = new Request('GET', 'http://example.com', array('User-Agent' => 'Unit-Test'));
        $response = new Response(200, array(), 'example response');
        $client = $this->getClientMock($request, $response);
        $configuration = new Configuration();
        $configuration->enableLibraryHooks(array());
        $configuration->setMode('new_episodes');

        $videorecorder = new Videorecorder($configuration, $client, VCRFactory::getInstance());
        $this->setProtectedProperty($videorecorder, 'cassette', $this->getCassetteMock($request, $response));

        $this->assertEquals($response, $videorecorder->handleRequest($request));
    }

    public function testHandleRequestThrowsExceptionWhenModeIsNone()
    {
        $this->expectException(
            'LogicException',
            "The request does not match a previously recorded request and the 'mode' is set to 'none'. "
            . "If you want to send the request anyway, make sure your 'mode' is set to 'new_episodes'."
        );

        $request = new Request('GET', 'http://example.com', array('User-Agent' => 'Unit-Test'));
        $response = new Response(200, array(), 'example response');
        $client = $this->getMockBuilder('\VCR\Util\HttpClient')->getMock();
        $configuration = new Configuration();
        $configuration->enableLibraryHooks(array());
        $configuration->setMode('none');

        $videorecorder = new Videorecorder($configuration, $client, VCRFactory::getInstance());
        $this->setProtectedProperty($videorecorder, 'cassette', $this->getCassetteMock($request, $response, 'none'));

        $videorecorder->handleRequest($request);
    }

    public function testHandleRequestRecordsRequestWhenModeIsOnceAndCassetteIsNew()
    {
        $request = new Request('GET', 'http://example.com', array('User-Agent' => 'Unit-Test'));
        $response = new Response(200, array(), 'example response');
        $client = $this->getClientMock($request, $response);
        $configuration = new Configuration();
        $configuration->enableLibraryHooks(array());
        $configuration->setMode('once');

        $videorecorder = new Videorecorder($configuration, $client, VCRFactory::getInstance());
        $this->setProtectedProperty($videorecorder, 'cassette', $this->getCassetteMock($request, $response, 'once', true));

        $this->assertEquals($response, $videorecorder->handleRequest($request));
    }

    public function testHandleRequestThrowsExceptionWhenModeIsOnceAndCassetteIsOld()
    {
        $this->expectException(
            'LogicException',
            "The request does not match a previously recorded request and the 'mode' is set to 'once'. "
            . "If you want to send the request anyway, make sure your 'mode' is set to 'new_episodes'."
        );

        $request = new Request('GET', 'http://example.com', array('User-Agent' => 'Unit-Test'));
        $response = new Response(200, array(), 'example response');
        $client = $this->getMockBuilder('\VCR\Util\HttpClient')->getMock();
        $configuration = new Configuration();
        $configuration->enableLibraryHooks(array());
        $configuration->setMode('once');

        $videorecorder = new Videorecorder($configuration, $client, VCRFactory::getInstance());
        $this->setProtectedProperty($videorecorder, 'cassette', $this->getCassetteMock($request, $response, 'once', false));

        $videorecorder->handleRequest($request);
    }

    protected function getClientMock($request, $response)
    {
        $client = $this->getMockBuilder('\VCR\Util\HttpClient')->setMethods(array('send'))->getMock();
        $client
            ->expects($this->once())
            ->method('send')
            ->with($request)
            ->will($this->returnValue($response));

        return $client;
    }

    protected function getCassetteMock($request, $response, $mode = VCR::MODE_NEW_EPISODES, $isNew = false)
    {
        $cassette = $this->getMockBuilder('\VCR\Cassette')
            ->disableOriginalConstructor()
            ->setMethods(array('record', 'playback', 'isNew'))
            ->getMock();
        $cassette
            ->expects($this->once())
            ->method('playback')
            ->with($request)
            ->will($this->returnValue(null));

        if (VCR::MODE_NEW_EPISODES === $mode || VCR::MODE_ONCE === $mode && $isNew === true) {
            $cassette
                ->expects($this->once())
                ->method('record')
                ->with($request, $response);
        }

        if ($mode == 'once') {
            $cassette
                ->expects($this->once())
                ->method('isNew')
                ->will($this->returnValue($isNew));
        }

        return $cassette;
    }
}
