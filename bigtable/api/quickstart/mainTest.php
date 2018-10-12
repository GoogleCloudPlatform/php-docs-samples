<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class QuickStartTest extends TestCase
{
	public function testQuickStart(): void {
		ob_start();
		require 'main.php';
		$content = ob_get_contents();
		ob_end_clean();
        $this->assertEquals(
            "Row key: rk1\nData: value1\n",
            $content
        );
    }
}