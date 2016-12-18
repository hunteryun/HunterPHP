<?php

namespace Hunter\Core\Category;

class tree
{
    /**
     * 生成树型结构所需要的2维数组.
     *
     * @var array
     */
    public $arr = array();

    /**
     * 生成树型结构所需修饰符号，可以换成图片.
     *
     * @var array
     */
    public $icon = array('│', '├', '└');

    /**
     */
    public $ret = '';

    /**
     * 构造函数，初始化类.
     *
     * @param array
     */
    public function init($arr = array())
    {
        $this->arr = $arr;

        return $this;
    }

    /**
     * 得到父级数组.
     *
     * @param int
     *
     * @return array
     */
    public function get_parent($myid)
    {
        $newarr = array();
        if (!isset($this->arr[$myid])) {
            return false;
        }
        $pid = $this->arr[$myid]['parentid'];
        $pid = $this->arr[$pid]['parentid'];
        if (is_array($this->arr)) {
            foreach ($this->arr as $id => $a) {
                if ($a['parentid'] == $pid) {
                    $newarr[$id] = $a;
                }
            }
        }

        return $newarr;
    }

    /**
     * 得到子级数组.
     *
     * @param int
     *
     * @return array
     */
    public function get_child($myid)
    {
        $a = $newarr = array();
        if (is_array($this->arr)) {
            foreach ($this->arr as $id => $a) {
                if ($a->parentid == $myid) {
                    $newarr[$id] = $a;
                }
            }
        }

        return $newarr ? $newarr : false;
    }

    /**
     * 得到当前位置数组.
     *
     * @param int
     *
     * @return array
     */
    public function get_pos($myid, &$newarr)
    {
        $a = array();
        if (!isset($this->arr[$myid])) {
            return false;
        }
        $newarr[] = $this->arr[$myid];
        $pid = $this->arr[$myid]['parentid'];
        if (isset($this->arr[$pid])) {
            $this->get_pos($pid, $newarr);
        }
        if (is_array($newarr)) {
            krsort($newarr);
            foreach ($newarr as $v) {
                $a[$v['id']] = $v;
            }
        }

        return $a;
    }

    /**
     * 得到树型结构.
     *
     * @param int ID，表示获得这个ID下的所有子级
     * @param string 生成树型结构的基本代码，例如："<option value=\$id \$selected>\$spacer\$name</option>"
     * @param int 被选中的ID，比如在做树型下拉框的时候需要用到
     *
     * @return string
     */
    public function get_tree($myid, $str, $sid = 0, $adds = '', $str_group = '')
    {
        $number = 1;
        $child = $this->get_child($myid);
        if (is_array($child)) {
            $total = count($child);
            foreach ($child as $a) {
                if ($a->parentid == $myid) {
                    $id = $a->cid;
                    $j = $k = '';
                    if ($number == $total) {
                        $j .= $this->icon[2];
                    } else {
                        $j .= $this->icon[1];
                        $k = $adds ? $this->icon[0] : '';
                    }
                    $spacer = $adds ? $adds.$j : '';
                    $selected = $id == $sid ? 'selected' : '';
                    $a->parentid == 0 && $str_group ? eval("\$nstr = \"$str_group\";") : eval("\$nstr = \"$str\";");
                    $this->ret .= $nstr;
                    $this->get_tree($id, $str, $sid, $adds.$k.'&nbsp;&nbsp;&nbsp;', $str_group);
                    ++$number;
                }
            }
        }

        return $this->ret;
    }
    /**
     * 同上一方法类似,但允许多选.
     */
    public function get_tree_multi($myid, $str, $sid = 0, $adds = '')
    {
        $number = 1;
        $child = $this->get_child($myid);
        if (is_array($child)) {
            $total = count($child);
            foreach ($child as $id => $a) {
                $j = $k = '';
                if ($number == $total) {
                    $j .= $this->icon[2];
                } else {
                    $j .= $this->icon[1];
                    $k = $adds ? $this->icon[0] : '';
                }
                $spacer = $adds ? $adds.$j : '';

                $selected = $this->have($sid, $id) ? 'selected' : '';
                @extract($a);
                eval("\$nstr = \"$str\";");
                $this->ret .= $nstr;
                $this->get_tree_multi($id, $str, $sid, $adds.$k.'&nbsp;');
                ++$number;
            }
        }

        return $this->ret;
    }

    private function have($list, $item)
    {
        return strpos(',,'.$list.',', ','.$item.',');
    }
}
