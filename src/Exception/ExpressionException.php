<?php
/*
 * This file licensed under the MIT license.
 *
 * (c) Sylvain Mauduit <swop@swop.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swop\FilterExpressionParser\Exception;

class ExpressionException extends \Exception
{
    /**
     * @param string $expression
     *
     * @return ExpressionException
     */
    public static function expressionError($expression)
    {
        return new self($expression);
    }

    /**
     * @param string          $message
     * @param \Exception|null $previous
     *
     * @return ExpressionException
     */
    public static function syntaxError($message, $previous = null)
    {
        return new self('[Syntax Error] ' . $message, 0, $previous);
    }

    /**
     * @param string          $message
     * @param \Exception|null $previous
     *
     * @return ExpressionException
     */
    public static function semanticalError($message, $previous = null)
    {
        return new self('[Semantical Error] ' . $message, 0, $previous);
    }
}
