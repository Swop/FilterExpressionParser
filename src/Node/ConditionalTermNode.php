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

class ConditionalTermNode extends Node
{
    /** @var array|Node */
    private $conditionalFactors;

    public function __construct($conditionalFactors)
    {
        $this->conditionalFactors = $conditionalFactors;
    }

    /**
     * Gets the conditionalFactors attribute
     *
     * @return array|\Swop\FilterExpressionParser\Node\Node
     */
    public function getConditionalFactors()
    {
        return $this->conditionalFactors;
    }
}
