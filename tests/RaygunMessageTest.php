<?php

namespace Raygun4php\Tests;

use Exception;
use PHPUnit\Framework\TestCase;
use Raygun4php\RaygunMessage;
use EnricoStahn\JsonAssert\Assert as JsonAssert;

class RaygunMessageTest extends TestCase
{
    use JsonAssert;

    /**
     * json schema used to validate message json.
     *
     * @var string
     */
    protected $jsonSchema;

    protected function setUp(): void
    {
        $this->jsonSchema = file_get_contents('./tests/misc/RaygunSchema.json');
    }


    public function testDefaultConstructorGeneratesValid8601()
    {
        $msg = new RaygunMessage();

        $matches = preg_match('/^(\d{4})-(\d{2})-(\d{2})T(\d{2}):(\d{2}):(\d{2})Z$/', $msg->OccurredOn);

        $this->assertEquals(1, $matches);
    }

    public function testUnixTimestampResultsInCorrect8601()
    {
        $msg = new RaygunMessage(0);

        $this->assertEquals($msg->OccurredOn, '1970-01-01T00:00:00Z');
    }

    public function testBuildMessageWithException()
    {
        $msg = new RaygunMessage();

        $msg->build(new \Exception('test'));

        $this->assertEquals($msg->Details->Error->Message, 'Exception: test');
    }

    public function testBuildMessageWithNestedException()
    {
        $msg = new RaygunMessage();

        $msg->build(new Exception('outer', 0, new Exception('inner')));

        $this->assertEquals($msg->Details->Error->Message, 'Exception: outer');
        $this->assertEquals($msg->Details->Error->InnerError->Message, 'Exception: inner');
    }

    public function testMessageToJsonSchema()
    {
        $msg = new RaygunMessage();
        $msg->build(new Exception('Test'));

        $msgJson = $msg->toJson();
        $this->assertJsonMatchesSchemaString($this->jsonSchema, json_decode($msgJson));
    }
}
