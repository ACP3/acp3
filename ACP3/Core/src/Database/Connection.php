<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Core\Database;

use ACP3\Core\Environment\ApplicationMode;
use Doctrine\DBAL;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Cache\QueryCacheProfile;
use Doctrine\DBAL\Exception as DBALException;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Result;
use Doctrine\DBAL\Types\Type;
use Psr\Log\LoggerInterface;

/**
 * @psalm-type WrapperParameterType = string|Type|ParameterType|ArrayParameterType
 * @psalm-type WrapperParameterTypeArray = array<int<0, max>, WrapperParameterType>|array<string, WrapperParameterType>
 */
class Connection
{
    private ?DBAL\Connection $connection = null;

    /**
     * @param array<string, mixed> $connectionParams
     */
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly ApplicationMode $appMode,
        private readonly array $connectionParams,
        private readonly string $tablePrefix)
    {
    }

    /**
     * @throws DBALException
     */
    public function getConnection(): DBAL\Connection
    {
        if ($this->connection === null) {
            $this->connection = $this->connect();
        }

        return $this->connection;
    }

    /**
     * @throws DBALException
     */
    public function beginTransaction(): void
    {
        $this->getConnection()->beginTransaction();
    }

    /**
     * @throws DBALException
     */
    public function commit(): void
    {
        $this->getConnection()->commit();
    }

    /**
     * @throws DBALException
     */
    public function rollback(): void
    {
        $this->getConnection()->rollBack();
    }

    public function getDatabase(): string
    {
        return $this->connectionParams['dbname'];
    }

    public function getPrefix(): string
    {
        return $this->tablePrefix;
    }

    public function getPrefixedTableName(string $tableName): string
    {
        return $this->tablePrefix . $tableName;
    }

    /**
     * @param list<mixed>|array<string, mixed> $params
     *
     * @psalm-param WrapperParameterTypeArray $types
     *
     * @return array<array<string, mixed>>
     *
     * @throws DBALException
     */
    public function fetchAll(
        string $statement,
        array $params = [],
        array $types = [],
        bool $cache = false,
        int $lifetime = 0,
        ?string $cacheKey = null,
    ): array {
        $stmt = $this->executeQuery($statement, $params, $types, $cache, $lifetime, $cacheKey);
        $data = $stmt->fetchAllAssociative();
        $stmt->free();

        return $data;
    }

    /**
     * @param list<mixed>|array<string, mixed> $params
     *
     * @psalm-param WrapperParameterTypeArray $types
     *
     * @return array<string, mixed>
     *
     * @throws DBALException
     */
    public function fetchAssoc(string $statement, array $params = [], array $types = []): array
    {
        $result = $this->executeQuery($statement, $params, $types)->fetchAssociative();

        return $result !== false ? $result : [];
    }

    /**
     * @param list<mixed>|array<string, mixed> $params
     *
     * @psalm-param WrapperParameterTypeArray $types
     *
     * @throws DBALException
     */
    public function fetchColumn(string $statement, array $params = [], array $types = []): mixed
    {
        return $this->executeQuery($statement, $params, $types)->fetchOne();
    }

    /**
     * @param list<mixed>|array<string, mixed> $params
     *
     * @psalm-param WrapperParameterTypeArray $types
     *
     * @throws DBALException
     */
    public function executeQuery(
        string $query,
        array $params = [],
        array $types = [],
        bool $cache = false,
        int $lifetime = 0,
        ?string $cacheKey = null,
    ): Result {
        return $this->getConnection()->executeQuery(
            $query,
            $params,
            $types,
            $cache ? new QueryCacheProfile($lifetime, $cacheKey ?: md5($query)) : null
        );
    }

    /**
     * @param list<mixed>|array<string, mixed> $params
     *
     * @psalm-param WrapperParameterTypeArray $types
     *
     * @throws DBALException
     */
    public function executeStatement(
        string $query,
        array $params = [],
        array $types = [],
    ): int {
        return (int) $this->getConnection()->executeStatement(
            $query,
            $params,
            $types,
        );
    }

    /**
     * @throws DBALException
     */
    private function connect(): DBAL\Connection
    {
        $config = new DBAL\Configuration();

        if ($this->appMode !== ApplicationMode::PRODUCTION) {
            $config->setMiddlewares([new DBAL\Logging\Middleware($this->logger)]);
        }

        return DBAL\DriverManager::getConnection($this->connectionParams, $config);
    }

    /**
     * @return object|resource
     *
     * @throws DBALException
     */
    public function getNativeConnection()
    {
        return $this->getConnection()->getNativeConnection();
    }
}
