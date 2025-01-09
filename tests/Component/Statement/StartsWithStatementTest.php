<?php

namespace Tests\Misery\Component\Statement;

use Misery\Component\Action\SetValueAction;
use PHPUnit\Framework\TestCase;
use Misery\Component\Statement\StartsWithStatement;
use Misery\Component\Statement\Field;

class StartsWithStatementTest extends TestCase
{
    private StartsWithStatement $statement;

    protected function setUp(): void
    {
        $this->statement = StartsWithStatement::prepare(new SetValueAction());
    }

    public function testWhenFieldReturnsTrue(): void
    {
        $field = new Field('test_field', 'Hello');
        $item = ['test_field' => 'Hello World'];

        $result = $this->invokeWhenField($field, $item);

        $this->assertTrue($result);
    }

    public function testWhenFieldReturnsFalseForNonMatchingString(): void
    {
        $field = new Field('test_field', 'World');
        $item = ['test_field' => 'Hello World'];

        $result = $this->invokeWhenField($field, $item);

        $this->assertFalse($result);
    }

    public function testWhenFieldReturnsFalseForNonExistentField(): void
    {
        $field = new Field('non_existent_field', 'Hello');
        $item = ['test_field' => 'Hello World'];

        $result = $this->invokeWhenField($field, $item);

        $this->assertFalse($result);
    }

    public function testWhenFieldReturnsFalseForNonStringValue(): void
    {
        $field = new Field('test_field', '1');
        $item = ['test_field' => 123];

        $result = $this->invokeWhenField($field, $item);

        $this->assertFalse($result);
    }

    public function testWhenFieldWithEmptyString(): void
    {
        $field = new Field('test_field', '');
        $item = ['test_field' => 'Hello World'];

        $result = $this->invokeWhenField($field, $item);

        $this->assertTrue($result);
    }

    public function testConstantName(): void
    {
        $this->assertEquals('STARTS_WITH', StartsWithStatement::NAME);
    }

    /**
     * Helper method to invoke the private whenField method
     */
    private function invokeWhenField(Field $field, array $item): bool
    {
        $reflection = new \ReflectionClass(StartsWithStatement::class);
        $method = $reflection->getMethod('whenField');
        $method->setAccessible(true);

        return $method->invoke($this->statement, $field, $item);
    }
}