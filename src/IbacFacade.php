<?php

namespace thinkmanage\ibac;

use think\Facade;

/**
 * @see \thinkmanage\Ibac
 * @package think\facade
 * @mixin \thinkmanage\Ibac
 */
class IbacFacade extends Facade
{
    /**
     * 获取当前Facade对应类名（或者已经绑定的容器对象标识）
     * @access protected
     * @return string
     */
    protected static function getFacadeClass()
    {
        return 'thinkmanage\ibac\Ibac';
    }
}