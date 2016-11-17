<?php

namespace Hunter\Core\Utility;

class Timer {

    protected static $timers = array();

    public static function start($name) {
        self::$timers[$name]['start'] = microtime(true);
        self::$timers[$name]['count'] = isset(self::$timers[$name]['count']) ? ++self::$timers[$name]['count'] : 1;
    }

    public static function read($name) {
        if (isset(self::$timers[$name]['start'])) {
            $stop = microtime(true);
            $diff = round(($stop - self::$timers[$name]['start']) * 1000, 2);
            if (isset(self::$timers[$name]['time'])) {
                $diff += self::$timers[$name]['time'];
            }
            return $diff;
        }

        return self::$timers[$name]['time'];
    }

    public static function stop($name) {
        if (isset(self::$timers[$name]['start'])) {
            $stop = microtime(true);
            $diff = round(($stop - self::$timers[$name]['start']) * 1000, 2);
            if (isset(self::$timers[$name]['time'])) {
                self::$timers[$name]['time'] += $diff;
            } else {
                self::$timers[$name]['time'] = $diff;
            }
            unset(self::$timers[$name]['start']);
        }

        return self::$timers[$name];
    }

}
