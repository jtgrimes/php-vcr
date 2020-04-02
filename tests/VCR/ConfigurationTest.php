<?php

namespace Tests\VCR;

use Tests\TestCase;
use VCR\Configuration;

class ConfigurationTest extends TestCase
{
    /**
     * @var Configuration
     */
    private $config;

    public function setUp() : void
    {
        $this->config = new Configuration;
    }

    public function testSetCassettePathThrowsErrorOnInvalidPath()
    {
        $this->expectException(
            'VCR\VCRException',
            "Cassette path 'invalid_path' is not a directory. Please either "
            . 'create it or set a different cassette path using '
            . "\\VCR\\VCR::configure()->setCassettePath('directory')."
        );
        $this->config->setCassettePath('invalid_path');
    }

    public function testGetLibraryHooks()
    {
        $this->assertEquals(
            array(
                'VCR\LibraryHooks\StreamWrapperHook',
                'VCR\LibraryHooks\CurlHook',
            ),
            $this->config->getLibraryHooks()
        );
    }

    public function testEnableLibraryHooks()
    {
        $this->config->enableLibraryHooks(array('stream_wrapper'));
        $this->assertEquals(
            array(
                'VCR\LibraryHooks\StreamWrapperHook',
            ),
            $this->config->getLibraryHooks()
        );
    }

    public function testEnableSingleLibraryHook()
    {
        $this->config->enableLibraryHooks('stream_wrapper');
        $this->assertEquals(
            array(
                'VCR\LibraryHooks\StreamWrapperHook',
            ),
            $this->config->getLibraryHooks()
        );
    }

    public function testEnableLibraryHooksFailsWithWrongHookName()
    {
        $this->expectException('InvalidArgumentException', "Library hooks don't exist: non_existing");
        $this->config->enableLibraryHooks(array('non_existing'));
    }

    public function testEnableRequestMatchers()
    {
        $this->config->enableRequestMatchers(array('body', 'headers'));
        $this->assertEquals(
            array(
                array('VCR\RequestMatcher', 'matchHeaders'),
                array('VCR\RequestMatcher', 'matchBody'),
            ),
            $this->config->getRequestMatchers()
        );
    }

    public function testEnableRequestMatchersFailsWithNoExistingName()
    {
        $this->expectException('InvalidArgumentException', "Request matchers don't exist: wrong, name");
        $this->config->enableRequestMatchers(array('wrong', 'name'));
    }

    public function testAddRequestMatcherFailsWithNoName()
    {
        $this->expectException('VCR\VCRException', "A request matchers name must be at least one character long. Found ''");
        $expected = function ($first, $second) {
            return true;
        };
        $this->config->addRequestMatcher('', $expected);
    }

    public function testAddRequestMatcherFailsWithWrongCallback()
    {
        $this->expectException('VCR\VCRException', "Request matcher 'example' is not callable.");
        $this->config->addRequestMatcher('example', array());
    }

    public function testAddRequestMatchers()
    {
        $expected = function () {
            return true;
        };
        $this->config->addRequestMatcher('new_matcher', $expected);
        $this->assertContains($expected, $this->config->getRequestMatchers());
    }

    /**
     * @dataProvider availableStorageProvider
     */
    public function testSetStorage($name, $className)
    {
        $this->config->setStorage($name);
        $this->assertEquals($className, $this->config->getStorage(), "$name should be class $className.");
    }

    public function availableStorageProvider()
    {
        return array(
            array('json', 'VCR\Storage\Json'),
            array('yaml', 'VCR\Storage\Yaml'),
        );
    }

    public function testSetStorageInvalidName()
    {
        $this->expectException('VCR\VCRException', "Storage 'Does not exist' not available.");
        $this->config->setStorage('Does not exist');
    }

    public function testGetStorage()
    {
        $class = $this->config->getStorage();
        $this->assertContains('Iterator', class_implements($class));
        $this->assertContains('Traversable', class_implements($class));
        $this->assertContains('VCR\Storage\AbstractStorage', class_parents($class));
    }

    public function testWhitelist()
    {
        $expected = array('Tux', 'Gnu');

        $this->config->setWhiteList($expected);

        $this->assertEquals($expected, $this->config->getWhiteList());
    }

    public function testBlacklist()
    {
        $expected = array('Tux', 'Gnu');

        $this->config->setBlackList($expected);

        $this->assertEquals($expected, $this->config->getBlackList());
    }

    public function testSetModeInvalidName()
    {
        $this->expectException('VCR\VCRException', "Mode 'invalid' does not exist.");
        $this->config->setMode('invalid');
    }
}
