<?php

/**
 * @file
 *
 * Pager
 */

namespace Hunter\Core\Database;

class Pager {
    
    //Query
    protected $query;
    
    //Connection
    protected $connection;
    
    //自定义总数SQL
    protected $customCountQuery;
    
    //分页信息
    protected $pager = array(
        'page'  => 1,   //当前页
        'size'  => 12,  //每页数
        'total' => 0,   //总数
        'pages' => 1,   //总页数
    );
    
    //Pager是否初始化过
    protected $hasInitialize = false;
    
    public function __construct($query, $connection) {
        $this->query = $query;
        $this->connection = $connection;
    }
    
    //每页条数
    public function size($size) {
        $this->pager['size'] = (int) max(1, $size);
        $this->hasInitialize = false;
        return $this;
    }
    
    //当前页
    public function page($page) {
        $this->pager['page'] = max(1, $page);
        $this->hasInitialize = false;
        return $this;
    }
    
    //设置自定义总数SQL
    public function setCountQuery(Query $query) {
        $this->customCountQuery = $query;
        return $this;
    }
    
    //获取总数SQL
    public function getCountQuery() {
        if ($this->customCountQuery) {
            return $this->customCountQuery;
        }
        else {
            return $this->query->countQuery();
        }
    }
    
    //计算分页
    public function pagerInitialize() {
        if ($this->hasInitialize) {
            return $this;
        }
        $total = $this->getCountQuery()->execute()->fetchField();
        $this->pager['total'] = (int) $total;
        $this->pager['pages'] = ceil($total / $this->pager['size']);
        $this->pager['more']  = $this->pager['page'] < $this->pager['pages'];
        $this->pager['page']  = max(1, min($this->pager['page'], $this->pager['pages']));
        $this->hasInitialize = true;
        
        return $this;
    }

    //读取分页信息
    public function fetchPager() {
        $this->pagerInitialize();
        return $this->pager;
    }
    
    //执行
    public function execute() {
        if (!$this->preExecute($this)) {
            return null;
        }
        $this->pagerInitialize();
        $this->range(($this->pager['page'] - 1) * $this->pager['size'], $this->pager['size']);
        return $this->query->execute();
    }

    //魔术方法指向Query
    public function __call($method, $args) {
        $return = call_user_func_array(array($this->query, $method), $args);
        if ($return instanceof Query) {
            return $this;
        } else {
            return $return;
        }
    }
    
    //魔术方法
    public function __toString() {
        return (string) $this->getCountQuery();
    }
    
}
