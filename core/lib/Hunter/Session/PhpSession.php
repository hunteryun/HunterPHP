<?php

/**
 * @file
 *
 * Session
 */

namespace Hunter\Core\Session;

class PhpSession {

    /**
     * 前缀
     */
    protected $prefix = '';

    /**
     * 过期时间
     */
    protected $expire = 0;

    /**
     * 析构函数
     */
    public function __construct($config = array()) {
        if (!empty($config['path']) && is_dir($config['path'])) {
            session_save_path($config['path']);
        }
        if (!isset($_SESSION)) {
            session_start();
        }
        $this->setPrefix($config['prefix']);
        $this->setExpire($config['expire']);
    }

    /**
     * 设置前缀
     */
    public function setPrefix($prefix) {
        $this->prefix = (string) $prefix;
        return $this;
    }

    /**
     * 设置过期时间
     */
    public function setExpire($expire) {
        $this->expire = (int) $expire;
        return $this;
    }

    /**
     * 读数据
     */
    public function get($key = '', $default = null) {
        $key = $this->prefix . $key;
        return isset($_SESSION[$key]) ? $_SESSION[$key] : $default;
    }

    /**
     * 写配置
     */
    public function set($key, $value) {
        $key = $this->prefix . $key;
        $_SESSION[$key] = $value;
        return $this;
    }

    /**
     * 删除
     */
    public function delete($key) {
        $key = $this->prefix . $key;
        $_SESSION[$key] = null;
        unset($_SESSION[$key]);
        return $this;
    }

    /**
     * 存储多个
     */
    public function setMulti(array $items) {
        foreach ($items as $key => $value) {
            $this->set($key, $value);
        }

        return $this;
    }

    /**
     * 检索多个
     *
     * return array|null
     */
    public function getMulti(array $keys) {
        foreach ($keys as $key) {
            $return[$key] = $this->get($key);
        }

        return $return;
    }

    /**
     * 删除多个
     */
    public function deleteMulti(array $keys) {
        foreach ($keys as $key) {
            $this->delete($key);
        }

        return $this;
    }

    /**
     * 是否有这个元素
     */
    public function hasKey($key) {
        $key = $this->prefix . $key;
        return isset($_SESSION[$key]) ? true : false;
    }

    /**
     * 清空配置
     */
    public function flush() {
        $_SESSION = array();
        session_destroy();
        return $this;
    }

}
