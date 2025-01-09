<?php

namespace Tests\Misery\Component\Statement;

use PHPUnit\Framework\TestCase;
use Misery\Component\Statement\StatementBuilder;
use Misery\Component\Statement\StatementInterface;
use Misery\Component\Statement\StatementCollection;
use Misery\Component\Statement\CollectionStatement;
use Misery\Component\Statement\EqualsStatement;
use Misery\Component\Statement\ContainsStatement;
use Misery\Component\Statement\InListStatement;
use Misery\Component\Statement\IsNumericsStatement;

class StatementBuilderTest extends TestCase
{
    public function testBuildFromOperator(): void
    {
        $operators = [
            'IN_LIST' => InListStatement::class,
            'IS_NUMERIC' => IsNumericsStatement::class,
            '==' => EqualsStatement::class,
            'CONTAINS' => ContainsStatement::class,
        ];

        foreach ($operators as $operator => $expectedClass) {
            $statement = StatementBuilder::buildFromOperator($operator);
            $this->assertInstanceOf($expectedClass, $statement);
        }

        $this->expectException(\Exception::class);
        StatementBuilder::buildFromOperator('INVALID_OPERATOR');
    }

    public function testBuildFromString(): void
    {
        $whenString = 'field1 == value1';
        $statement = StatementBuilder::build($whenString);
        $this->assertInstanceOf(EqualsStatement::class, $statement);

        $whenString = 'field1 CONTAINS value1';
        $statement = StatementBuilder::build($whenString);
        $this->assertInstanceOf(ContainsStatement::class, $statement);

        $whenString = 'field1 == value1 AND field2 == value2';
        $statement = StatementBuilder::build($whenString);
        $this->assertInstanceOf(CollectionStatement::class, $statement);
    }

    public function testBuildFromArray(): void
    {
        $when = [
            'operator' => 'IN_LIST',
            'field' => 'test_field',
            'state' => 'test_state',
            'context' => [
                'list' => ['value1', 'value2']
            ]
        ];

        $statement = StatementBuilder::build($when);
        $this->assertInstanceOf(InListStatement::class, $statement);
    }

    public function testBuildInvalidInput(): void
    {
        $this->expectException(\RuntimeException::class);
        StatementBuilder::build(null);
    }

    public function testFromArray(): void
    {
        $whenArray = [
            ['operator' => 'IN_LIST', 'field' => 'field1', 'state' => 'value1'],
            ['operator' => 'CONTAINS', 'field' => 'field2', 'state' => 'value3'],
        ];

        $statements = StatementBuilder::fromArray($whenArray);
        $this->assertIsArray($statements);
        $this->assertCount(2, $statements);
        $this->assertInstanceOf(InListStatement::class, $statements[0]);
        $this->assertInstanceOf(ContainsStatement::class, $statements[1]);
    }

    public function testFromExpressionWithOr(): void
    {
        $whenString = 'field1 == value1 OR field2 == value2';
        $statement = StatementBuilder::build($whenString);
        $this->assertInstanceOf(EqualsStatement::class, $statement);
        // Here you might want to add more specific assertions to check if the OR condition is properly set
    }

    public function testFromExpressionWithComplexAnd(): void
    {
        $whenString = 'field1 > 10 AND field2 < 20 AND field3 == value3';
        $statement = StatementBuilder::build($whenString);
        $this->assertInstanceOf(CollectionStatement::class, $statement);
        // Here you might want to add more specific assertions to check if all AND conditions are properly set
    }
}