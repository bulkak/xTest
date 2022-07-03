<?php

namespace xTest\Adapter;

interface SqlAdapter
{
    /**
     * @throws AdapterException
     */
    public function getLastInsertId():  int|bool;

    /**
     * @throws AdapterException
     */
    public function insert(string $table, array $data): ?int;

    /**
     * @throws AdapterException
     */
    public function update(string $table, array $data, array $where): ?int;

    /**
     * @throws AdapterException
     */
    public function fetchAll(string $table, array $where, array $fields = null, $order = null, ?int $limit = null, int $offset = 0): ?array;

    public function getByColumn(string $table, string $column, string | int $value): array | bool;

    public function truncateTable(string $table): bool;

    public function beginTransaction(): SqlAdapter;

    public function commitTransaction(): SqlAdapter;

    public function rollbackTransaction(): SqlAdapter;

    public function isTransactionOpened(): bool;
}