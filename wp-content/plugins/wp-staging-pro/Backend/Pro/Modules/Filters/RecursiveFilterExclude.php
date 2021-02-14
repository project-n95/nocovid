<?php

namespace WPStaging\Backend\Pro\Modules\Filters;

use WPStaging\Backend\Pro\Modules\Jobs\Copiers\Copier;
use WPStaging\Core\Iterators\RecursiveFilterExclude as BaseRecursiveFilterExclude;

/**
 * Class RecursiveFilterExclude
 *
 * @todo What's the difference between this and \WPStaging\Core\Iterators\RecursiveFilterExclude?
 * @see \WPStaging\Core\Iterators\RecursiveFilterExclude Maybe unify them.
 *
 * @package WPStaging\Backend\Pro\Modules\Filters
 */
class RecursiveFilterExclude extends BaseRecursiveFilterExclude
{
    public function accept()
    {
        $result = parent::accept();
        if (!$result) {
            return false;
        }

	    // Exclude tmp and backup plugins like 'plugins/wpstg-tmp-woocommerce' and 'plugins/wpstg-bak-woocommerce'
        $pattern = sprintf('#^(%s|%s)+#', Copier::PREFIX_TEMP, Copier::PREFIX_BACKUP);
        if (preg_match($pattern, $this->getInnerIterator()->getSubPathname())) {
            return false;
        }

        return true;
    }
}
