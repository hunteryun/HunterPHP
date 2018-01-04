<?php

/**
 * @file
 *
 */

namespace Hunter\Core\Session;

/**
 * Session
 *
 * @see http://cn2.php.net/manual/zh/function.session-set-save-handler.php
 */
interface SessionHandlerInterface {
    /**
     * 开始session
     */
    public function open();

    /**
     * 结束session
     */
    public function close();

    /**
     * 读session
     */
    public function read($sid);

    /**
     * 写session
     */
    public function write($sid, $value);

    /**
     * 销毁session
     */
    public function destroy($sid);

    /**
     * session回收
     */
    public function gc($lifetime);

}
