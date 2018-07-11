<?php
/**
 * Created by PhpStorm.
 * User: yuyuyu
 * Date: 7/10/18
 * Time: 8:56 PM
 */

namespace Google\Cloud\Samples\Jobs;


use Symfony\Component\Console\Tester\CommandTester;

class GeneralSearchSampleTest extends \PHPUnit_Framework_TestCase
{
    private $commandTester;

    public function setUp()
    {
        if (!getenv('GOOGLE_APPLICATION_CREDENTIALS')) {
            return $this->markTestSkipped("Set the GOOGLE_APPLICATION_CREDENTIALS environment variable.");
        }

        $application = require __DIR__ . '/../cjd_sample.php';
        $this->commandTester = new CommandTester($application->get('general-search-sample'));
    }

    public function testGeneralSearchSample()
    {
        $this->commandTester->execute([], ['interactive' => false]);
        $this->expectOutputRegex('/matchingJobs.*matchingJobs.*matchingJobs.*matchingJobs.*'
            . 'matchingJobs.*matchingJobs.*matchingJobs.*/s');
    }
}
