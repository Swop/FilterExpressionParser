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

class ConditionalPrimaryNode extends Node
{
    /** @var Node */
    private $simpleConditionalExpression;
    /** @var ConditionalExpressionNode */
    private $conditionalExpression;

    /**
     * @param Node
     */
    public function setSimpleConditionalExpression(Node $simpleConditionalExpression)
    {
        $this->simpleConditionalExpression = $simpleConditionalExpression;
    }

    /**
     * @param ConditionalExpressionNode $conditionalExpression
     */
    public function setConditionalExpression(ConditionalExpressionNode $conditionalExpression)
    {
        $this->conditionalExpression = $conditionalExpression;
    }

    /**
     * Gets the conditionalExpression attribute
     *
     * @return \Swop\FilterExpressionParser\Node\ConditionalExpressionNode
     */
    public function getConditionalExpression()
    {
        return $this->conditionalExpression;
    }

    /**
     * Gets the simpleConditionalExpression attribute
     *
     * @return \Swop\FilterExpressionParser\Node\Node
     */
    public function getSimpleConditionalExpression()
    {
        return $this->simpleConditionalExpression;
    }
}
