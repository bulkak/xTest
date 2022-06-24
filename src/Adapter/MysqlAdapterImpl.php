<?php
declare(strict_types=1);

namespace xTest\Adapter;

use PDO;
use PDOStatement;

class MysqlAdapterImpl implements SqlAdapter
{
    public function __construct(
        private readonly PDO $connection
    ) {}

    public function query(string $sql, array $bind = []): ?PDOStatement
    {
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($bind);
            return $stmt;
        } catch (\PDOException $e) {
            throw new AdapterException(
                (is_numeric($e->getCode()) ? '' : '[' . $e->getCode() . ']') . $e->getMessage(),
                0,
                $e
            );
        }
    }

    public function insert(string $table, array $data): ?PDOStatement
    {
        if (count($data) > 0) {
            [$values, $bind] = $this->getValuesAndBind($data);

            $columns = implode(', ', array_keys($data));
            $values = implode(', ', $values);

            $sql = 'INSERT INTO ' . $table . ' (' . $columns . ')
					VALUES (' . $values . ')';

            return $this->query($sql, $bind);
        }

        return null;
    }

    public function update(string $table, array $data, array $where): ?PDOStatement
    {
        if (count($data) && $where) {
            $fields = [];
            $values = [];

            foreach ($data as $key => $val) {
                if (is_bool($val)) {
                    $val = (int)$val;
                }
                $fields[] = $key . ' = ?';
                $values[] = $val;
            }

            $sql = 'UPDATE ' . $table . '
                    SET   ' . implode(', ', $fields) . '
                    WHERE ' . implode(' AND ', array_keys($where));

            $bind = array_merge($values, array_values($where));
            return $this->query($sql, $bind);
        }

        return null;
    }

    public function select(string $table, array $where, array $fields = null, $order = null,
                           ?int $limit = null, int $offset = 0): ?PDOStatement
    {
        $whereString = '';

        if ($where) {
            $columns = [];
            foreach ($where as $key => $value) {
                if (is_array($value)) {
                    unset($where[$key]);
                    $columns[] = $key . ' IN (' . $this->quoteInStrings($value) . ')';
                } else {
                    $columns[] = $key . ' = ?';
                }
            }
            $whereString = ' WHERE ' . implode(' AND ', $columns);
        }

        $fields = $fields ? implode(', ', $fields) : ' * ';
        $sql = 'SELECT ' . $fields . ' FROM ' . $table . $whereString;

        if ($order) {
            is_array($order)
                ? $sql .= ' ORDER BY ' . implode(',', $order)
                : $sql .= ' ORDER BY ' . $order;
        }
        if (isset($limit)) {
            $sql .= ' LIMIT ' . $limit;
            if ($offset > 0) {
                $sql .= ' OFFSET ' . $offset;
            }
        }

        $bind = $where ? array_values($where) : [];

        return $this->query($sql, $bind);
    }

    public function quote(string $string): string
    {
        return '"' . addslashes($string) . '"';
    }

    public function quoteInStrings(array $items): string
    {
        return implode(
            ', ',
            array_map(
                static function ($item) {
                    return '"' . addslashes($item) . '"';
                },
                $items
            )
        );
    }

    private function getValuesAndBind(array $data): array
    {
        $values = [];
        $bind = [];

        foreach ($data as $key => $val) {
            if (is_bool($val)) {
                $val = (int)$val;
            }
            $values[] = '?';
            $bind[] = $val;
        }
        return [$values, $bind];
    }
}
