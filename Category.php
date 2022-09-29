<?php

namespace app\admin\controller\cnework;

use app\common\controller\Backend;

/**
 * 薪资类别
 *
 * @icon fa fa-circle-o
 */
class Category extends Backend
{

    /**
     * Category模型对象
     * @var \app\admin\model\cnework\Category
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\cnework\Category;

    }
}
