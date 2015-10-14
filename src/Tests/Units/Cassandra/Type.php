<?php
namespace M6Web\Bundle\CassandraBundle\Tests\Units\Cassandra;

use M6Web\Bundle\CassandraBundle\Cassandra\Type as TestedClass;
use mageekguy\atoum\test;

class Type extends test
{
    /**
     * @param string               $uuid
     * @param \Datetime           $datetime
     *
     * @dataProvider validUuidDataProvider
     */
    public function testTimeuuidFromString($uuid, $datetime)
    {
        $this
            ->object($timeuuid = TestedClass::getTimeuuidFromString($uuid))
                ->isInstanceOf('\Cassandra\Timeuuid')
            ->object($timeuuid->toDateTime())
                ->isEqualTo($datetime);
    }

    /**
     * @param string               $uuid
     *
     * @dataProvider invalidUuidDataProvider
     */
    public function testInvalidTimeuuid($uuid)
    {
        $this
            ->variable($timeuuid = TestedClass::getTimeuuidFromString($uuid))
                ->isNull()
        ;
    }

    protected function validUuidDataProvider()
    {
        return [
            [
                '513a5340-6da0-11e5-815e-93ec150e89fd',
                new \DateTime('2015-10-08 11:38:22+0200')
            ],
            [
                '7c134a50-724f-11e5-b25f-c7d4e052c75e',
                new \Datetime('2015-10-14 10:42:21+0200')
            ],
            [
                '123b770f-3834-11e5-7f7f-7f7f7f7f7f7f',
                new \Datetime('2015-08-01 12:00:00+0200')
            ]
        ];
    }

    protected function invalidUuidDataProvider()
    {
        return [
            ['513a5340-6da0-11e5-815e-93ec150e89fd5'], // too long
            ['513a5340-6a-11e5-815e-93ec150e89fd5'], // too short
            ['15a33504-d60a-115e-18e5-39ce51e098df'] // bad timeuuid
        ];
    }
}