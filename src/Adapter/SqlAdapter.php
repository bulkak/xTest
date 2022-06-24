<?php

namespace xTest\Adapter;

use PDOStatement;

interface SqlAdapter
{
    /**
     * @throws AdapterException
     */
    public function query(string $sql, array $bind = []): ?PDOStatement;

    /**
     * @throws AdapterException
     */
    public function insert(string $table, array $data): ?PDOStatement;

    /**
     * @throws AdapterException
     */
    public function update(string $table, array $data, array $where): ?PDOStatement;

    /**
     * @throws AdapterException
     */
    public function select(string $table, array $where, array $fields = null, $order = null, ?int $limit = null, int $offset = 0): ?PDOStatement;

    public function quote(string $string): string;

    public function quoteInStrings(array $items): string;
}