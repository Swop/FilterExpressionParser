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

class BetweenExpressionNode extends Node
{
    /** @var bool */
    private $not = false;
    /** @var IdentificationVariableNode */
    private $expr1;
    /** @var Node */
    private $expr2;
    /** @var Node */
    private $expr3;

    function __construct(IdentificationVariableNode $expr1, Node $expr2, Node $expr3)
    {
        $this->expr1 = $expr1;
        $this->expr2 = $expr2;
        $this->expr3 = $expr3;
    }

    /**
     * @param bool $not
     */
    public function setNot($not)
    {
        $this->not = $not;
    }

    /**
     * Gets the expr1 attribute
     *
     * @return \Swop\FilterExpressionParser\Node\IdentificationVariableNode
     */
    public function getExpr1()
    {
        return $this->expr1;
    }

    /**
     * Gets the expr2 attribute
     *
     * @return \Swop\FilterExpressionParser\Node\Node
     */
    public function getExpr2()
    {
        return $this->expr2;
    }

    /**
     * Gets the expr3 attribute
     *
     * @return \Swop\FilterExpressionParser\Node\Node
     */
    public function getExpr3()
    {
        return $this->expr3;
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
