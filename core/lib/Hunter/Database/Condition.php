<?php

/**
 * @file
 *
 * Condition
 */

namespace Hunter\Core\Database;

use Countable;

/**
 * 条件类
 */
class Condition implements Countable {

    //所有条件
    protected $conditions = array();
    
    //绑定值
    protected $arguments = array();
    
    //变更标签
    protected $changed = true;
    
    //占位符对象
    protected $queryPlaceholderIdentifier;
    
    /**
     * 析构函数
     *
     * @var string
     *   AND|OR|XOR
     */
    public function __construct($conjunction) {
        $this->conditions['#conjunction'] = $conjunction;
    }
    
    /**
     * 计算数量
     */
    public function count() {
        return count($this->conditions) - 1;
    }
    
    /**
     * 设置一个字段-值的条件
     */
    public function condition($field, $value = null, $operator = null) {
        if (!isset($operator)) {
            if (is_array($value)) {
                $operator = 'IN';
            } else {
                $operator = '=';
            }
        }
        $this->conditions[] = array(
            'field' => $field,
            'value' => $value,
            'operator' => $operator,
        );

        $this->changed = true;

        return $this;
    }
    
    /**
     * 设置一个条件
     */
    public function where($snippet, $args = array()) {
        $this->conditions[] = array(
            'field' => $snippet,
            'value' => $args,
            'operator' => null,
        );
        $this->changed = true;

        return $this;
    }
    
    /**
     * 字段值是否NULL
     */
    public function isNull($field) {
        return $this->condition($field, null, 'IS null');
    }
    
    /**
     * 字段值是否NOT null
     */
    public function isNotNull($field) {
        return $this->condition($field, null, 'IS NOT null');
    }
    
    /**
     * 引用返回条件清单
     */
    public function &conditions() {
        return $this->conditions;
    }
    
    /**
     * 获取所有的值
     */
    public function arguments() {
        if ($this->changed) {
            return null;
        }
        return $this->arguments;
    }
    
    /**
     * 组装条件
     */
    public function compile(Connection $connection, $queryPlaceholder) {
        if ($this->changed || isset($this->queryPlaceholderIdentifier) && ($this->queryPlaceholderIdentifier != $queryPlaceholder->uniqueIdentifier())) {
            $this->queryPlaceholderIdentifier = $queryPlaceholder->uniqueIdentifier();
            $condition_fragments = array();
            $arguments = array();
            $conditions = $this->conditions;
            $conjunction = $conditions['#conjunction'];
            unset($conditions['#conjunction']);
            foreach ($conditions as $condition) {
                if (empty($condition['operator'])) {
                    $condition_fragments[] = ' (' . $condition['field'] . ') ';
                    $arguments += $condition['value'];
                } else {
                    if (is_object($condition['field'])) {
                        $condition['field']->compile($connection, $queryPlaceholder);
                        $condition_fragments[] = '(' . (string) $condition['field'] . ')';
                        $arguments += $condition['field']->arguments();
                    } else {
                        $operator_defaults = array(
                            'prefix'    => '',
                            'postfix'   => '',
                            'delimiter' => '',
                            'operator'  => $condition['operator'],
                            'use_value' => true,
                        );
                        
                        $operator = $connection->mapConditionOperator($condition['operator']);
                        if (!isset($operator)) {
                            $operator = $this->mapConditionOperator($condition['operator']);
                        }
                        $operator += $operator_defaults;
                        
                        $placeholders = array();
                        if (is_object($condition['value'])) {
                            $condition['value']->compile($connection, $queryPlaceholder);
                            $placeholders[] = (string) $condition['value'];
                            $arguments += $condition['value']->arguments();
                            $operator['use_value'] = false;
                        }
                        if (!$operator['delimiter']) {
                            $condition['value'] = array($condition['value']);
                        }
                        if ($operator['use_value']) {
                            foreach ($condition['value'] as $value) {
                                $placeholder = ':db_condition_placeholder_' . $queryPlaceholder->nextPlaceholder();
                                $arguments[$placeholder] = $value;
                                $placeholders[] = $placeholder;
                            }
                        }
                        $condition_fragments[] = ' (' .$condition['field'] . ' ' . $operator['operator'] . ' ' . $operator['prefix'] . implode($operator['delimiter'], $placeholders) . $operator['postfix'] . ') ';
                    }
                }
            }

            $this->changed = false;
            $this->stringVersion = implode($conjunction, $condition_fragments);
            $this->arguments = $arguments;
        }
    }
    
    /**
     * 是否已经组装过,防止重复组装
     */
    public function compiled() {
        return !$this->changed;
    }
    
    /**
     * 魔术方法,拼装SQL
     */
    public function __toString() {
        if ($this->changed) {
            return '';
        }
        
        return $this->stringVersion;
    }
    
    /**
     * clone时保持子对象也clone
     */
    function __clone() {
        $this->changed = true;
        foreach ($this->conditions as $key => $condition) {
            if ($key !== '#conjunction') {
                if (is_object($condition['field'])) {
                    $this->conditions[$key]['field'] = clone($condition['field']);
                }
                if (is_object($condition['value'])) {
                    $this->conditions[$key]['value'] = clone($condition['value']);
                }
            }
        }
    }
    
    /**
     * 解析条件操作符
     */
    protected function mapConditionOperator($operator) {
        static $specials = array(
            'BETWEEN'       => array('delimiter' => ' AND '),
            'IN'            => array('delimiter' => ', ', 'prefix' => ' (', 'postfix' => ')'),
            'NOT IN'        => array('delimiter' => ', ', 'prefix' => ' (', 'postfix' => ')'),
            'EXISTS'        => array('prefix' => ' (', 'postfix' => ')'),
            'NOT EXISTS'    => array('prefix' => ' (', 'postfix' => ')'),
            'IS null'       => array('use_value' => false),
            'IS NOT null'   => array('use_value' => false),
            'LIKE'          => array('postfix' => " ESCAPE '\\\\'"),
            'NOT LIKE'      => array('postfix' => " ESCAPE '\\\\'"),
            '='             => array(),
            '<'             => array(),
            '>'             => array(),
            '>='            => array(),
            '<='            => array(),
        );
        if (isset($specials[$operator])) {
            $return = $specials[$operator];
        }
        else {
            $operator = strtoupper($operator);
            $return = isset($specials[$operator]) ? $specials[$operator] : array();
        }
        $return += array('operator' => $operator);

        return $return;
    }
  
}
