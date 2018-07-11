<?php
/**
 * Created by PhpStorm.
 * User: yuyuyu
 * Date: 7/11/18
 * Time: 1:18 PM
 */

namespace Google\Cloud\Samples\Jobs;


use Symfony\Component\Console\Tester\CommandTester;

class LocationSearchSampleTest extends \PHPUnit_Framework_TestCase
{
    private $commandTester;

    public function setUp()
    {
        if (!getenv('GOOGLE_APPLICATION_CREDENTIALS')) {
            return $this->markTestSkipped("Set the GOOGLE_APPLICATION_CREDENTIALS environment variable.");
        }

        $application = require __DIR__ . '/../cjd_sample.php';
        $this->commandTester = new CommandTester($application->get('location-search-sample'));
    }

    public function testLocationSearchSample()
    {
        $this->commandTester->execute([], ['interactive' => false]);
        $this->expectOutputRegex('/appliedJobLocationFilters.*matchingJobs.*'
            . 'appliedJobLocationFilters.*matchingJobs.*'
            . 'appliedJobLocationFilters.*matchingJobs.*'
            . 'appliedJobLocationFilters.*matchingJobs.*'
            . 'appliedJobLocationFilters.*matchingJobs.*/s');
    }
}
