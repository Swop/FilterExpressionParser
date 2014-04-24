<?php
/*
 * This file licensed under the MIT license.
 *
 * (c) Sylvain Mauduit <swop@swop.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swop\FilterExpressionParser\Bridge\Doctrine;

use Doctrine\ORM\QueryBuilder;
use Swop\FilterExpressionParser\Node\Node;

class QueryBuilderPatcher
{
    /** @var QueryExpressionBuilder */
    private $queryExpressionBuilder;

    public function __construct(QueryExpressionBuilder $queryExpressionBuilder)
    {
        $this->queryExpressionBuilder = $queryExpressionBuilder;
    }

    public function patch(QueryBuilder $qb, Node $filterAST, QueryContext $qc)
    {
        $queryExpression = $this->queryExpressionBuilder->buildQueryExpression($filterAST, $qc);

        $qb->andWhere($queryExpression);
        $qb->setParameters($qc->getParameters());

        return $qb;
    }
}
