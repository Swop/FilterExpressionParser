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

class NullComparisonExpressionNode extends Node
{
    /** @var bool */
    private $not = false;
    /** @var IdentificationVariableNode */
    private $expr;

    public function __construct(IdentificationVariableNode $expr)
    {
        $this->expr = $expr;
    }

    /**
     * @param bool $not
     */
    public function setNot($not)
    {
        $this->not = $not;
    }

    /**
     * Gets the expr attribute
     *
     * @return \Swop\FilterExpressionParser\Node\IdentificationVariableNode
     */
    public function getExpr()
    {
        return $this->expr;
    }

    /**
     * Gets the not attribute
     *
     * @return boolean
     */
    public function isNot()
    {
        return $this->not;
    }
}
