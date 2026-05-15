<?php

namespace OpenCensus\Trace;

use PHPUnit\Framework\TestCase;

class TraceTest extends TestCase
{
    public function testTraceSample()
    {
        require_once __DIR__ . '/../trace-sample.php';

        trace_callable();
        $reflection = new \ReflectionProperty('\OpenCensus\Trace\Tracer', 'instance');
        $reflection->setAccessible(true);
        $handler = $reflection->getValue();
        $tracer = $handler->tracer();
        $spans = $tracer->spans();
        $this->assertEquals(2, count($spans));
        $this->assertEquals('slow_function', $spans[1]->name());
    }
}
