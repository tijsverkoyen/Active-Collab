<?php

namespace TijsVerkoyen\ActiveCollab;

require_once '../../../autoload.php';
require_once 'config.php';
require_once 'PHPUnit/Framework/TestCase.php';

use \TijsVerkoyen\ActiveCollab\ActiveCollab;

/**
 * test case.
 */
class ActiveCollabTest extends PHPUnit_Framework_TestCase
{
    /**
     * ActiveCollab instance
     *
     * @var	ActiveCollab
     */
    private $activeCollab;

    /**
     * Prepares the environment before running a test.
     */
    protected function setUp()
    {
        parent::setUp();
        $this->activeCollab = new ActiveCollab(TOKEN, API_URL);
    }

    /**
     * Cleans up the environment after running a test.
     */
    protected function tearDown()
    {
        $this->activeCollab = null;
        parent::tearDown();
    }

    /**
     * Tests ActiveCollab->getTimeOut()
     */
    public function testGetTimeOut()
    {
        $this->activeCollab->setTimeOut(5);
        $this->assertEquals(5, $this->activeCollab->getTimeOut());
    }

    /**
     * Tests ActiveCollab->getUserAgent()
     */
    public function testGetUserAgent()
    {
        $this->activeCollab->setUserAgent('testing/1.0.0');
        $this->assertEquals(
            'PHP ActiveCollab/' . ActiveCollab::VERSION . ' testing/1.0.0',
            $this->activeCollab->getUserAgent()
        );
    }

    /**
     * Tests ActiveCollab->info()
     */
    public function testInfo()
    {
        $response = $this->activeCollab->info();
        $this->assertArrayHasKey('api_version', $response);
        $this->assertArrayHasKey('system_version', $response);
        $this->assertArrayHasKey('loaded_frameworks', $response);
        $this->assertArrayHasKey('enabled_modules', $response);
        $this->assertArrayHasKey('logged_user', $response);
        $this->assertArrayHasKey('read_only', $response);
    }
}
