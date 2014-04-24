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

class ConditionalFactorNode extends Node
{
    /** @var bool */
    private $not = false;
    /** @var ConditionalPrimaryNode */
    private $conditionalPrimary;

    public function __construct(ConditionalPrimaryNode $conditionalPrimary)
    {
        $this->conditionalPrimary = $conditionalPrimary;
    }

    /**
     * @param bool $not
     */
    public function setNot($not)
    {
        $this->not = $not;
    }

    /**
     * Gets the conditionalPrimary attribute
     *
     * @return \Swop\FilterExpressionParser\Node\ConditionalPrimaryNode
     */
    public function getConditionalPrimary()
    {
        return $this->conditionalPrimary;
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
