<?php
/*
 * This file licensed under the MIT license.
 *
 * (c) Sylvain Mauduit <swop@swop.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swop\FilterExpressionParser\Node;

class FilterNode extends Node
{
    /** @var Node */
    private $child;

    public function __construct(Node $child)
    {
        $this->child = $child;
    }

    /**
     * Gets the child attribute
     *
     * @return \Swop\FilterExpressionParser\Node\Node
     */
    public function getChild()
    {
        return $this->child;
    }
}
