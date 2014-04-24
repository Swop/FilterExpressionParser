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

class ComparisonExpressionNode extends Node
{
    /** @var Node */
    private $leftExpr;
    /** @var string */
    private $operator;
    /** @var Node */
    private $rightExpr;

    /**
     * @param Node   $leftExpr
     * @param string $operator
     * @param Node   $rightExpr
     */
    public function __construct(Node $leftExpr, $operator, Node $rightExpr)
    {
        $this->leftExpr  = $leftExpr;
        $this->operator  = $operator;
        $this->rightExpr = $rightExpr;
    }

    /**
     * Gets the leftExpr attribute
     *
     * @return \Swop\FilterExpressionParser\Node\Node
     */
    public function getLeftExpr()
    {
        return $this->leftExpr;
    }

    /**
     * Gets the operator attribute
     *
     * @return string
     */
    public function getOperator()
    {
        return $this->operator;
    }

    /**
     * Gets the rightExpr attribute
     *
     * @return \Swop\FilterExpressionParser\Node\Node
     */
    public function getRightExpr()
    {
        return $this->rightExpr;
    }
}
