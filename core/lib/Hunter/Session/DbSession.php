<?php

/**
 * @file
 *
 * Session
 */

namespace Hunter\Core\Session;

use SessionHandlerInterface;

/**
 * session数据库接管

    CREATE TABLE `{sessions}` (
        `sid`  VARCHAR(128) NOT NULL,
        `timestamp` INT(11) NOT NULL DEFAULT '0',
        `session` LONGTEXT NULL,
        PRIMARY KEY (`sid`),
        INDEX `timestamp` (`timestamp`)
    )
    COMMENT='sessions'
    COLLATE='utf8_general_ci'
    ENGINE=InnoDB;

 */
class DbSession extends PhpSession implements SessionHandlerInterface {

    /**
     * 析构函数
     */
    public function __construct($config = array()) {
        session_set_save_handler(
            array($this, 'open'),
            array($this, 'close'),
            array($this, 'read'),
            array($this, 'write'),
            array($this, 'destroy'),
            array($this, 'gc')
        );
        register_shutdown_function('session_write_close');
        parent::__construct($config);
    }

    //{@implements}
    public function open($savePath, $sessionName) {
        return true;
    }

    //{@implements}
    public function close() {
        return true;
    }

    //{@implements}
    public function read($sid) {
        $sess = db_query("select * from {sessions} where sid=:sid", array(':sid'=>$sid))->fetchObject();
        if ($sess) {
            if ($this->expire && $sess->timestamp < time()) {
                return false;
            }
            return $sess->session;
        }
        return false;
    }

    //{@implements}
    public function write($sid, $value) {
        if ($value === '') {
            return true;
        }
        $fields = array(
            'sid'       => $sid,
            'session'   => $value,
            'timestamp' => time() + $this->expire,
        );
        db_merge('sessions')
            ->key(array('sid' => $sid))
            ->fields($fields)
            ->execute();
        return true;
    }

    //{@implements}
    public function destroy($sid) {
        db_delete('sessions')
            ->condition('sid', $sid)
            ->execute();
        return true;
    }

    //{@implements}
    public function gc($lifetime) {
        db_delete('sessions')
            ->condition('timestamp', time() - $lifetime, '<')
            ->execute();
        return true;
    }

}
