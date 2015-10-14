<?php
namespace M6Web\Bundle\CassandraBundle\Cassandra;

/**
 * Class Type provide function for manipulating cassandra type
 */
class Type
{
    // Time (in 100ns steps) between the start of the UTC and Unix epochs
    const INTERVAL = 0x01b21dd213814000;
    // length of uid without hyphen
    const UUID_LENGTH = 16;

    /**
     * Get a Cassandra\Timeuuid from string
     *
     * @param string $uuid
     *
     * @return \Cassandra\Timeuuid|null
     */
    public static function getTimeuuidFromString($uuid)
    {
        $str = preg_replace("/[^a-f0-9]/is", "", $uuid); // delete non hexadecimal

        if (strlen($str) !== (self::UUID_LENGTH * 2)) {
            return null;
        }

        $bin = pack("H*", $str);

        if (ord($bin[6]) >> 4 == 1) {
            // Restore contiguous big-endian byte order
            $time = bin2hex($bin[6].$bin[7].$bin[4].$bin[5].$bin[0].$bin[1].$bin[2].$bin[3]);

            // Clear version flag
            $time[0] = "0";

            // Do some reverse arithmetic to get a Unix timestamp
            $time = (hexdec($time) - self::INTERVAL) / 10000000;

            // in case of bad uuid time can be negative
            if ($time < 0) {
                return null;
            }

            return new \Cassandra\Timeuuid(floor($time) * 1000);
        }

        return null;
    }
}