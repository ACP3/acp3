<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Core\NestedSet\Operation;

class Edit extends AbstractOperation
{
    /**
     * Methode zum Bearbeiten eines Knotens.
     *
     * @param int   $resultId
     * @param int   $parentId
     * @param int   $blockId
     * @param array $updateValues
     *
     * @return bool
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    public function execute(int $resultId, int $parentId, int $blockId, array $updateValues)
    {
        $callback = function () use ($resultId, $parentId, $blockId, $updateValues) {
            $nodes = $this->nestedSetRepository->fetchNodeWithSiblings($resultId);

            // Überprüfen, ob Seite ein Root-Element ist und ob dies auch so bleiben soll
            if ($this->nodeIsRootItemAndNoChangeNeed($parentId, $blockId, $nodes[0])) {
                $bool = $this->db->getConnection()->update(
                    $this->nestedSetRepository->getTableName(),
                    $updateValues,
                    ['id' => $resultId]
                );
            } else {
                $currentParent = $this->nestedSetRepository->fetchParentNode(
                    $nodes[0]['left_id'],
                    $nodes[0]['right_id']
                );

                // Überprüfung, falls Seite kein Root-Element ist und auch keine Veränderung vorgenommen werden soll...
                if (!empty($currentParent) && $currentParent == $parentId) {
                    $bool = $this->db->getConnection()->update(
                        $this->nestedSetRepository->getTableName(),
                        $updateValues,
                        ['id' => $resultId]
                    );
                } else { // ...ansonsten den Baum bearbeiten...
                    // Neues Elternelement
                    $newParent = $this->nestedSetRepository->fetchNodeById($parentId);

                    if (empty($newParent)) {
                        list($rootId, $diff) = $this->nodeBecomesRootNode($resultId, $blockId, $nodes);
                    } else {
                        list($diff, $rootId) = $this->moveNodeToNewParent($newParent, $nodes);
                    }

                    $bool = $this->adjustNodeSiblings($blockId, $nodes, $diff, $rootId);

                    $this->db->getConnection()->update(
                        $this->nestedSetRepository->getTableName(),
                        $updateValues,
                        ['id' => $resultId]
                    );
                }
            }

            return $bool;
        };

        return $this->db->executeTransactionalQuery($callback);
    }

    /**
     * @param int   $parentId
     * @param int   $blockId
     * @param array $item
     *
     * @return bool
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    protected function nodeIsRootItemAndNoChangeNeed(int $parentId, int $blockId, array $item)
    {
        return empty($parentId) &&
        ($this->isBlockAware() === false || ($this->isBlockAware() === true && $blockId == $item[$this->getBlockColumnName()])) &&
        $this->nestedSetRepository->nodeIsRootItem($item['left_id'], $item['right_id']) === true;
    }

    /**
     * @param int   $id
     * @param int   $blockId
     * @param array $nodes
     *
     * @return array
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    protected function nodeBecomesRootNode(int $id, int $blockId, array $nodes)
    {
        $itemDiff = $this->calcDiffBetweenNodes($nodes[0]['left_id'], $nodes[0]['right_id']);
        if ($this->isBlockAware() === true) {
            if ($nodes[0][$this->getBlockColumnName()] != $blockId) {
                $diff = $this->nodeBecomesRootNodeInNewBlock($blockId, $nodes, $itemDiff);
            } else {
                $diff = $this->nodeBecomesRootNodeInSameBlock($nodes, $itemDiff);
            }
        } else {
            $maxId = $this->nestedSetRepository->fetchMaximumRightId();
            $diff = $maxId - $nodes[0]['right_id'];

            $this->adjustParentNodesAfterSeparation($itemDiff, $nodes[0]['left_id'], $nodes[0]['right_id']);
            $this->adjustFollowingNodesAfterSeparation($itemDiff, $nodes[0]['right_id']);
        }

        return [$id, $diff];
    }

    /**
     * @param int   $blockId
     * @param array $nodes
     * @param int   $itemDiff
     *
     * @return int
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    protected function nodeBecomesRootNodeInNewBlock(int $blockId, array $nodes, int $itemDiff)
    {
        $newBlockLeftId = $this->nestedSetRepository->fetchMinimumLeftIdByBlockId($blockId);

        // Falls die Knoten in einen leeren Block verschoben werden sollen,
        // die right_id des letzten Elementes verwenden
        if (empty($newBlockLeftId) || \is_null($newBlockLeftId) === true) {
            $newBlockLeftId = $this->nestedSetRepository->fetchMaximumRightId();
            $newBlockLeftId += 1;
        }

        if ($blockId > $nodes[0][$this->getBlockColumnName()]) {
            $newBlockLeftId -= $itemDiff;
        }

        $this->adjustParentNodesAfterSeparation($itemDiff, $nodes[0]['left_id'], $nodes[0]['right_id']);
        $this->adjustFollowingNodesAfterSeparation($itemDiff, $nodes[0]['right_id']);
        $this->adjustFollowingNodesAfterInsert($itemDiff, $newBlockLeftId);

        return $newBlockLeftId - $nodes[0]['left_id'];
    }

    /**
     * @param array $nodes
     * @param int   $itemDiff
     *
     * @return int
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    protected function nodeBecomesRootNodeInSameBlock(array $nodes, int $itemDiff)
    {
        $maxId = $this->nestedSetRepository->fetchMaximumRightIdByBlockId($nodes[0][$this->getBlockColumnName()]);

        $this->adjustParentNodesAfterSeparation($itemDiff, $nodes[0]['left_id'], $nodes[0]['right_id']);

        $this->db->getConnection()->executeUpdate(
            "UPDATE {$this->nestedSetRepository->getTableName()} SET left_id = left_id - ?, right_id = right_id - ? WHERE left_id > ? AND {$this->getBlockColumnName()} = ?",
            [$itemDiff, $itemDiff, $nodes[0]['right_id'], $nodes[0][$this->getBlockColumnName()]]
        );

        return $maxId - $nodes[0]['right_id'];
    }

    /**
     * @param int   $blockId
     * @param array $nodes
     * @param int   $diff
     * @param int   $rootId
     *
     * @return int|bool
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    private function adjustNodeSiblings(int $blockId, array $nodes, int $diff, int $rootId)
    {
        $bool = false;

        foreach ($nodes as $node) {
            $node['left_id'] += $diff;
            $node['right_id'] += $diff;

            $parentId = $this->nestedSetRepository->fetchParentNode(
                $node['left_id'],
                $node['right_id']
            );
            if ($this->isBlockAware()) {
                $bool = $this->db->getConnection()->executeUpdate(
                    "UPDATE {$this->nestedSetRepository->getTableName()} SET {$this->getBlockColumnName()} = ?, root_id = ?, parent_id = ?, left_id = ?, right_id = ? WHERE id = ?",
                    [
                        $blockId,
                        $rootId,
                        $parentId,
                        $node['left_id'],
                        $node['right_id'],
                        $node['id'],
                    ]
                );
            } else {
                $bool = $this->db->getConnection()->executeUpdate(
                    "UPDATE {$this->nestedSetRepository->getTableName()} SET root_id = ?, parent_id = ?, left_id = ?, right_id = ? WHERE id = ?",
                    [
                        $rootId,
                        $parentId,
                        $node['left_id'],
                        $node['right_id'],
                        $node['id'],
                    ]
                );
            }
            if ($bool === false) {
                break;
            }
        }

        return $bool;
    }

    /**
     * @param int $leftId
     * @param int $rightId
     *
     * @return int
     */
    protected function calcDiffBetweenNodes(int $leftId, int $rightId)
    {
        return $rightId - $leftId + 1;
    }

    /**
     * @param array $newParent
     * @param array $nodes
     *
     * @return array
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    protected function moveNodeToNewParent(array $newParent, array $nodes)
    {
        $itemDiff = $this->calcDiffBetweenNodes($nodes[0]['left_id'], $nodes[0]['right_id']);

        // Teilbaum nach unten...
        if ($newParent['left_id'] > $nodes[0]['left_id']) {
            $newParent['left_id'] -= $itemDiff;
            $newParent['right_id'] -= $itemDiff;
        }

        $diff = $newParent['left_id'] - $nodes[0]['left_id'] + 1;
        $rootId = $newParent['root_id'];

        $this->adjustParentNodesAfterSeparation($itemDiff, $nodes[0]['left_id'], $nodes[0]['right_id']);
        $this->adjustFollowingNodesAfterSeparation($itemDiff, $nodes[0]['right_id']);
        $this->adjustParentNodesAfterInsert($itemDiff, $newParent['left_id'], $newParent['right_id']);
        $this->adjustFollowingNodesAfterInsert($itemDiff, $newParent['left_id'] + 1);

        return [$diff, $rootId];
    }
}
