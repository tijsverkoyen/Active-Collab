<?php

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
     * Check if an item is a DateTimeValue
     *
     * @param $item
     */
    private function isDateTimeValue($item)
    {
        $this->assertArrayHasKey('class', $item);
        $this->assertEquals($item['class'], 'DateTimeValue');
        $this->assertArrayHasKey('timestamp', $item);
        $this->assertInternalType('int', $item['timestamp']);
        $this->assertArrayHasKey('mysql', $item);
        $this->assertArrayHasKey('formatted', $item);
        $this->assertArrayHasKey('formatted_gmt', $item);
        $this->assertArrayHasKey('formatted_time', $item);
        $this->assertArrayHasKey('formatted_time_gmt', $item);
        $this->assertArrayHasKey('formatted_date', $item);
        $this->assertArrayHasKey('formatted_date_gmt', $item);
    }

    /**
     * Check if an item is a project
     *
     * @param array $item
     */
    private function isProject($item)
    {
        $this->assertArrayHasKey('name', $item);
        $this->assertArrayHasKey('overview', $item);
        $this->assertArrayHasKey('category_id', $item);
        $this->assertInternalType('int', $item['category_id']);
        $this->assertArrayHasKey('company_id', $item);
        $this->assertInternalType('int', $item['company_id']);
        $this->assertArrayHasKey('leader_id', $item);
        $this->assertInternalType('int', $item['leader_id']);
        $this->assertArrayHasKey('budget', $item);
        $this->assertArrayHasKey('label_id', $item);
        $this->assertInternalType('int', $item['label_id']);

        // extra non documented fields
        $this->assertArrayHasKey('permalink', $item);
        $this->assertArrayHasKey('verbose_type', $item);
        $this->assertArrayHasKey('verbose_type_lowercase', $item);
        $this->assertArrayHasKey('urls', $item);
        $this->assertArrayHasKey('permissions', $item);
        $this->assertArrayHasKey('created_on', $item);
        $this->isDateTimeValue($item['created_on']);
        $this->assertArrayHasKey('created_by_id', $item);
        $this->assertArrayHasKey('updated_on', $item);
        $this->assertArrayHasKey('updated_by_id', $item);
        $this->assertArrayHasKey('state', $item);
        $this->assertArrayHasKey('is_archived', $item);
        $this->assertArrayHasKey('is_trashed', $item);
        $this->assertArrayHasKey('completed_on', $item);
        $this->assertArrayHasKey('completed_by_id', $item);
        $this->assertArrayHasKey('is_completed', $item);
        $this->assertArrayHasKey('avatar', $item);
        $this->assertArrayHasKey('overview_formatted', $item);
        $this->assertArrayHasKey('currency_code', $item);
        $this->assertArrayHasKey('based_on', $item);
        $this->assertArrayHasKey('status_verbose', $item);
        $this->assertArrayHasKey('progress', $item);
    }

    /**
     * Check if an item is a milestone
     *
     * @param array $item
     */
    private function isMilestone($item)
    {
        $this->assertArrayHasKey('name', $item);
        $this->assertArrayHasKey('body', $item);
        $this->assertArrayHasKey('start_on', $item);
        $this->assertArrayHasKey('due_on', $item);
        $this->assertArrayHasKey('priority', $item);
        $this->assertInternalType('int', $item['priority']);
        $this->assertArrayHasKey('assignee_id', $item);
        $this->assertInternalType('int', $item['assignee_id']);
    }

    /**
     * Check if an item is a label
     *
     * @param array $item
     */
    private function isLabel($item)
    {
        $this->assertArrayHasKey('id', $item);
        $this->assertArrayHasKey('name', $item);
        $this->assertArrayHasKey('fg_color', $item);
        $this->assertArrayHasKey('bg_color', $item);
    }

    /**
     * Check if an item is a role
     *
     * @param array $item
     */
    private function isRole($item)
    {
        $this->assertArrayHasKey('id', $item);
        $this->assertArrayHasKey('name', $item);
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
     * Tests ActiveCollab->projectsMilestones()
     */
    public function testProjectsMilestones()
    {
        $response = $this->activeCollab->projectsMilestones('api-example');
        foreach ($response as $row) {
            $this->isMilestone($row);
        }
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

    /**
     * Tests ActiveCollab->infoLabelsProject()
     */
    public function testInfoLabelsProject()
    {
        $response = $this->activeCollab->infoLabelsProject();
        foreach ($response as $row) {
            $this->isLabel($row);
        }
    }

    /**
     * Tests ActiveCollab->infoLabelsAssignment()
     */
    public function testInfoLabelsAssignment()
    {
        $response = $this->activeCollab->infoLabelsAssignment();
        foreach ($response as $row) {
            $this->isLabel($row);
        }
    }

    /**
     * Tests ActiveCollab->infoRoles()
     */
    public function testInfoRoles()
    {
        $response = $this->activeCollab->infoRoles();
        foreach ($response as $row) {
            $this->isRole($row);
        }
    }

    /**
     * Tests ActiveCollab->projects
     */
    public function testProjects()
    {
        $response = $this->activeCollab->projects();
        foreach ($response as $row) {
            $this->isProject($row);
        }
    }
}
