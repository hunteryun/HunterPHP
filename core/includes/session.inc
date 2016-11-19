<?php

/**
 * @file
 *
 * Session
 */

use Opis\Session\Session;

function session() {
    $session = new Session(null, array('name' => 'Hunter'));
    return $session;
}

/*
 * @usage
 *
 * 存储一个配置
 *   session()->set($key, $value);
 *
 * 检索一个配置
 *   session()->get($key, $default = null);
 *
 * 删除一个配置
 *   session()->delete($key);
 *
 * 存储多个配置
 *   session()->setMulti($items);
 *
 * 检索多个配置
 *   session()->getMulti($keys);
 *
 * 删除多个配置
 *   session()->deleteMulti($keys);
 *
 * 作废配置中的所有元素
 *   session()->flush();
 *
 * 是否有这个元素
 *   session()->hasKey($key);
 *
 */
