<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Core\NestedSet\Operation;

class Sort extends AbstractOperation
{
    /**
     * @return bool
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    public function execute(int $resultId, string $mode)
    {
        if ($this->nestedSetRepository->nodeExists($resultId) === true) {
            $nodes = $this->nestedSetRepository->fetchNodeWithSiblings($resultId);

            if ($mode === 'up' &&
                $this->nestedSetRepository->nextNodeExists(
                    $nodes[0]['left_id'] - 1,
                    $this->getBlockId($nodes[0])
                ) === true
            ) {
                return $this->sortUp($nodes);
            } elseif ($mode === 'down' &&
                $this->nestedSetRepository->previousNodeExists(
                    $nodes[0]['right_id'] + 1,
                    $this->getBlockId($nodes[0])
                ) === true
            ) {
                return $this->sortDown($nodes);
            }
        }

        return false;
    }

    /**
     * @return bool
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    protected function sortUp(array $nodes)
    {
        $prevNodes = $this->nestedSetRepository->fetchPrevNodeWithSiblings($nodes[0]['left_id'] - 1);

        list($diffLeft, $diffRight) = $this->calcDiffBetweenNodes($nodes[0], $prevNodes[0]);

        return $this->updateNodesDown($diffRight, $prevNodes) && $this->moveNodesUp($diffLeft, $nodes);
    }

    /**
     * @return bool
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    protected function sortDown(array $nodes)
    {
        $nextNodes = $this->nestedSetRepository->fetchNextNodeWithSiblings($nodes[0]['right_id'] + 1);

        list($diffLeft, $diffRight) = $this->calcDiffBetweenNodes($nextNodes[0], $nodes[0]);

        return $this->moveNodesUp($diffLeft, $nextNodes) && $this->updateNodesDown($diffRight, $nodes);
    }

    /**
     * @return array
     */
    protected function fetchAffectedNodesForReorder(array $nodes)
    {
        $rtn = [];
        foreach ($nodes as $node) {
            $rtn[] = $node['id'];
        }

        return $rtn;
    }

    /**
     * @return int
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    protected function updateNodesDown(int $diff, array $nodes)
    {
        return $this->db->getConnection()->executeUpdate(
            "UPDATE {$this->nestedSetRepository->getTableName()} SET left_id = left_id + ?, right_id = right_id + ? WHERE id IN(?)",
            [$diff, $diff, $this->fetchAffectedNodesForReorder($nodes)],
            [\PDO::PARAM_INT, \PDO::PARAM_INT, \Doctrine\DBAL\Connection::PARAM_INT_ARRAY]
        );
    }

    /**
     * @return int
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    protected function moveNodesUp(int $diff, array $nodes)
    {
        return $this->db->getConnection()->executeUpdate(
            "UPDATE {$this->nestedSetRepository->getTableName()} SET left_id = left_id - ?, right_id = right_id - ? WHERE id IN(?)",
            [$diff, $diff, $this->fetchAffectedNodesForReorder($nodes)],
            [\PDO::PARAM_INT, \PDO::PARAM_INT, \Doctrine\DBAL\Connection::PARAM_INT_ARRAY]
        );
    }

    /**
     * @return array
     */
    protected function calcDiffBetweenNodes(array $node, array $elem)
    {
        return [
            $node['left_id'] - $elem['left_id'],
            $node['right_id'] - $elem['right_id'],
        ];
    }

    /**
     * @return string
     */
    protected function getBlockId(array $node)
    {
        return $this->isBlockAware === true ? $node[$this->nestedSetRepository::BLOCK_COLUMN_NAME] : 0;
    }
}