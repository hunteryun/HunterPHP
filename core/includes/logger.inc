<?php

/**
 * @file
 *
 * Logger
 */

use Katzgrau\KLogger\Logger;

/**
 * 获取Logger类
 *
 * @param string $target
 */
function logger() {
    return new Logger(HUNTER_ROOT.'/sites/logs');
}

/*
 * @usage
 *
 * 致命错误
 *   logger()->fatal($string);
 *
 * 错误
 *   logger()->error($string);
 *
 * 警告
 *   logger()->warn($string);
 *
 * 常规
 *   logger()->info($string);
 *
 * DEBUG
 *   logger()->debug($string);
 *
 * 调试
 *   logger()->trace($string);
 *
 * 日志
 *   logger()->log($string);
 */
