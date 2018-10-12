<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class HelloWorldTest extends TestCase
{
	public function testHelloWorld(): void {
		ob_start();
		require 'main.php';
		$content = ob_get_contents();
		ob_end_clean();
		$array = explode("\n", $content);
		
		print_r( $array );
		$this->assertContains("Writing some greetings to the table.",$array);
		$this->assertContains("Getting a single greeting by row key.",$array);
		$this->assertContains("Hello World!",$array);
		$this->assertContains("Scanning for all greetings:",$array);
		$this->assertContains("Hello World!",$array);
		$this->assertContains("Hello Cloud Bigtable!",$array);
		$this->assertContains("Hello PHP!",$array);
		$this->assertContains("Deleting the bigtable-php-table table.",$array);
    }
}
