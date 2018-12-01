<?php
declare(strict_types=1);

namespace test\Builder\Sql\Sqlite;

use PHPUnit\Framework\TestCase;
use QueryMule\Builder\Sql\Sqlite\Filter;
use QueryMule\Builder\Sql\Sqlite\Select;
use QueryMule\Query\Repository\RepositoryInterface;
use QueryMule\Query\Sql\Statement\FilterInterface;

/**
 * Class FilterTest
 * @package test\Builder\Sql\Sqlite
 */
class FilterTest extends TestCase
{
    /**
     * @var FilterInterface
     */
    private $filter;

    public function setUp()
    {
        $this->filter = new Filter();
    }

    public function tearDown()
    {
        $this->filter = null;
    }

    public function testSelectWhere()
    {
        $query = $this->filter->where('col_a',$this->filter->comparison()->equalTo('some_value'))->build();
        $this->assertEquals("WHERE `col_a` =?", $query->sql());
        $this->assertEquals(['some_value'],$query->parameters());
    }

    public function testSelectWhereWithAlias()
    {
        $query = $this->filter->where('t.col_a',$this->filter->comparison()->equalTo('some_value'))->build();
        $this->assertEquals("WHERE `t`.`col_a` =?", $query->sql());
        $this->assertEquals(['some_value'],$query->parameters());
    }

    public function testSelectWhereAndNestedWhere()
    {
        $query = $this->filter->where('col_a',$this->filter->comparison()->equalTo('some_value_a'))->nestedWhere(function(\QueryMule\Query\Sql\Statement\FilterInterface $query){
            $query->where('col_b',$this->filter->comparison()->equalTo('some_value_b'));
            $query->where('col_c',$this->filter->comparison()->equalTo('some_value_c'));
            $query->nestedWhere(function(\QueryMule\Query\Sql\Statement\FilterInterface $query){
                $query->where('col_d',$this->filter->comparison()->equalTo('some_value_d'));
            });
        })->build();

        $this->assertEquals("WHERE `col_a` =? AND ( `col_b` =? AND `col_c` =? AND ( `col_d` =? ) )", $query->sql());
        $this->assertEquals(['some_value_a','some_value_b','some_value_c','some_value_d'],$query->parameters());
    }

    public function testSelectWhereAndWhere()
    {
        $query = $this->filter->where('col_a',$this->filter->comparison()->equalTo('some_value_a'))->where('col_b',$this->filter->comparison()->equalTo('some_value_b'))->build();
        $this->assertEquals("WHERE `col_a` =? AND `col_b` =?", $query->sql());
        $this->assertEquals(['some_value_a','some_value_b'],$query->parameters());
    }

    public function testSelectWhereOrWhere()
    {
        $query = $this->filter->where('col_a',$this->filter->comparison()->equalTo('some_value_a'))->orWhere('col_b',$this->filter->comparison()->equalTo('some_value_b'))->build();
        $this->assertEquals("WHERE `col_a` =? OR `col_b` =?", $query->sql());
        $this->assertEquals(['some_value_a','some_value_b'],$query->parameters());
    }

    public function testSelectWhereIn()
    {
        $query = $this->filter->whereIn('col_a',['some_value_a','some_value_b'])->build();
        $this->assertEquals("WHERE `col_a` IN ( ?,? )", $query->sql());
        $this->assertEquals(['some_value_a','some_value_b'],$query->parameters());
    }

    public function testSelectWhereInOrIn()
    {
        $query = $this->filter->whereIn('col_a',['some_value_a','some_value_b'])->orWhereIn('col_a',['some_value_c','some_value_d'])->build();
        $this->assertEquals("WHERE `col_a` IN ( ?,? ) OR `col_a` IN ( ?,? )", $query->sql());
        $this->assertEquals(['some_value_a','some_value_b','some_value_c','some_value_d'],$query->parameters());
    }

    public function testSelectWhereNotIn()
    {
        $query = $this->filter->whereNotIn('col_a',['some_value_a','some_value_b'])->build();
        $this->assertEquals("WHERE NOT `col_a` IN ( ?,? )", $query->sql());
        $this->assertEquals(['some_value_a','some_value_b'],$query->parameters());
    }

    public function testSelectWhereNotInAndIn()
    {
        $query = $this->filter->whereNotIn('col_a',['some_value_a','some_value_b'])->whereNotIn('col_a',['some_value_c','some_value_d'])->build();
        $this->assertEquals("WHERE NOT `col_a` IN ( ?,? ) AND NOT `col_a` IN ( ?,? )", $query->sql());
        $this->assertEquals(['some_value_a','some_value_b','some_value_c','some_value_d'],$query->parameters());
    }

    public function testSelectWhereNotInOrIn()
    {
        $query = $this->filter->whereNotIn('col_a',['some_value_a','some_value_b'])->orWhereNotIn('col_a',['some_value_c','some_value_d'])->build();
        $this->assertEquals("WHERE NOT `col_a` IN ( ?,? ) OR NOT `col_a` IN ( ?,? )", $query->sql());
        $this->assertEquals(['some_value_a','some_value_b','some_value_c','some_value_d'],$query->parameters());
    }

    public function testSelectWhereNot()
    {
        $query = $this->filter->whereNot('col_a',$this->filter->comparison()->equalTo('some_value_a'))->build();
        $this->assertEquals("WHERE NOT `col_a` =?", $query->sql());
        $this->assertEquals(['some_value_a'],$query->parameters());
    }

    public function testSelectWhereNotAndNot()
    {
        $query = $this->filter->whereNot('col_a',$this->filter->comparison()->equalTo('some_value_a'))->whereNot('col_b',$this->filter->comparison()->equalTo('some_value_b'))->build();
        $this->assertEquals("WHERE NOT `col_a` =? AND NOT `col_b` =?", $query->sql());
        $this->assertEquals(['some_value_a', 'some_value_b'],$query->parameters());
    }

    public function testSelectWhereBetween()
    {
        $query = $this->filter->whereBetween('col_a',1,2)->build();
        $this->assertEquals("WHERE `col_a` BETWEEN ? AND ?", $query->sql());
        $this->assertEquals([1,2],$query->parameters());
    }

    public function testSelectWhereBetweenOrBetween()
    {
        $query = $this->filter->whereBetween('col_a',1,2)->orWhereBetween('col_b',3,4)->build();
        $this->assertEquals("WHERE `col_a` BETWEEN ? AND ? OR `col_b` BETWEEN ? AND ?", $query->sql());
        $this->assertEquals([1,2,3,4],$query->parameters());
    }

    public function testSelectWhereBetweenAndBetween()
    {
        $query = $this->filter->whereBetween('col_a',1,2)->whereBetween('col_b',3,4)->build();
        $this->assertEquals("WHERE `col_a` BETWEEN ? AND ? AND `col_b` BETWEEN ? AND ?", $query->sql());
        $this->assertEquals([1,2,3,4],$query->parameters());
    }

    public function testSelectWhereNotBetween()
    {
        $query = $this->filter->whereNotBetween('col_a',1,2)->build();
        $this->assertEquals("WHERE NOT `col_a` BETWEEN ? AND ?", $query->sql());
        $this->assertEquals([1,2],$query->parameters());
    }

    public function testSelectWhereNotBetweenOrNotBetween()
    {
        $query = $this->filter->whereNotBetween('col_a',1,2)->orWhereNotBetween('col_b',3,4)->build();
        $this->assertEquals("WHERE NOT `col_a` BETWEEN ? AND ? OR NOT `col_b` BETWEEN ? AND ?", $query->sql());
        $this->assertEquals([1,2,3,4],$query->parameters());
    }

    public function testSelectWhereNotBetweenAndNotBetween()
    {
        $query = $this->filter->whereNotBetween('col_a',1,2)->whereNotBetween('col_b',3,4)->build();
        $this->assertEquals("WHERE NOT `col_a` BETWEEN ? AND ? AND NOT `col_b` BETWEEN ? AND ?", $query->sql());
        $this->assertEquals([1,2,3,4],$query->parameters());
    }

    public function testSelectWhereExists()
    {
        $table = $this->createMock(RepositoryInterface::class);
        $table->expects($this->any())->method('getName')->will($this->returnValue('some_table_name'));

        $select = new Select();

        $query = $this->filter->whereExists($select->cols()->from($table)->build())->build();
        $this->assertEquals("WHERE EXISTS ( SELECT * FROM some_table_name )", $query->sql());
        $this->assertEquals([],$query->parameters());
    }

    public function testSelectWhereNotExists()
    {
        $table = $this->createMock(RepositoryInterface::class);
        $table->expects($this->any())->method('getName')->will($this->returnValue('some_table_name'));

        $select = new Select();

        $query = $this->filter->whereNotExists($select->cols()->from($table)->build())->build();
        $this->assertEquals("WHERE NOT EXISTS ( SELECT * FROM some_table_name )", $query->sql());
        $this->assertEquals([],$query->parameters());
    }

    public function testSelectWhereExistsOrExists()
    {
        $table = $this->createMock(RepositoryInterface::class);
        $table->expects($this->any())->method('getName')->will($this->returnValue('some_table_name'));

        $select = new Select();
        $select2 = new Select();

        $query = $this->filter->whereExists($select->cols()->from($table)->build())->orWhereExists($select2->cols()->from($table)->build())->build();
        $this->assertEquals("WHERE EXISTS ( SELECT * FROM some_table_name ) OR EXISTS ( SELECT * FROM some_table_name )", $query->sql());
        $this->assertEquals([],$query->parameters());
    }

    public function testSelectWhereNotExistsOrNotExists()
    {
        $table = $this->createMock(RepositoryInterface::class);
        $table->expects($this->any())->method('getName')->will($this->returnValue('some_table_name'));

        $select = new Select();
        $select2 = new Select();

        $query = $this->filter->whereNotExists($select->cols()->from($table)->build())->orWhereNotExists($select2->cols()->from($table)->build())->build();
        $this->assertEquals("WHERE NOT EXISTS ( SELECT * FROM some_table_name ) OR NOT EXISTS ( SELECT * FROM some_table_name )", $query->sql());
        $this->assertEquals([],$query->parameters());
    }

    public function testSelectWhereExistsAndExists()
    {
        $table = $this->createMock(RepositoryInterface::class);
        $table->expects($this->any())->method('getName')->will($this->returnValue('some_table_name'));

        $select = new Select();
        $select2 = new Select();

        $query = $this->filter->whereExists($select->cols()->from($table)->build())->whereExists($select2->cols()->from($table)->build())->build();
        $this->assertEquals("WHERE EXISTS ( SELECT * FROM some_table_name ) AND EXISTS ( SELECT * FROM some_table_name )", $query->sql());
        $this->assertEquals([],$query->parameters());
    }

    public function testSelectWhereNotExistsAndNotExists()
    {
        $table = $this->createMock(RepositoryInterface::class);
        $table->expects($this->any())->method('getName')->will($this->returnValue('some_table_name'));

        $select = new Select();
        $select2 = new Select();

        $query = $this->filter->whereNotExists($select->cols()->from($table)->build())->whereNotExists($select2->cols()->from($table)->build())->build();
        $this->assertEquals("WHERE NOT EXISTS ( SELECT * FROM some_table_name ) AND NOT EXISTS ( SELECT * FROM some_table_name )", $query->sql());
        $this->assertEquals([],$query->parameters());
    }
}