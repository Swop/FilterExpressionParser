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

class LikeExpressionNode extends Node
{
    /** @var bool */
    private $not = false;
    /** @var IdentificationVariableNode */
    private $stringExpr;
    /** @var Node */
    private $stringPattern;

    function __construct(IdentificationVariableNode $stringExpr, Node $stringPattern)
    {
        $this->stringExpr    = $stringExpr;
        $this->stringPattern = $stringPattern;
    }

    /**
     * @param bool $not
     */
    public function setNot($not)
    {
        $this->not = $not;
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

    /**
     * Gets the stringExpr attribute
     *
     * @return \Swop\FilterExpressionParser\Node\IdentificationVariableNode
     */
    public function getStringExpr()
    {
        return $this->stringExpr;
    }

    /**
     * Gets the stringPattern attribute
     *
     * @return \Swop\FilterExpressionParser\Node\Node
     */
    public function getStringPattern()
    {
        return $this->stringPattern;
    }
}
