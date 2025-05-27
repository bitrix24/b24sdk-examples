<?php

declare(strict_types=1);

namespace App;

use \PDO;
use Psr\Log\LoggerInterface;
use Carbon\Carbon;

class DataConnector
{
    private $pdo;
    private $logger;

    public function __construct($connectionParams, LoggerInterface $logger)
    {

        // For demo purposes, we use a local SQLite database with no connection parameters
        // so $connectionParams is not used here.
        // In a real application, you would use $connectionParams to connect to a database.
        $this->logger = $logger;
        $dbPath = realpath(__DIR__ . '/../data/demo.db');
        $this->pdo = new PDO('sqlite:' . $dbPath);
    }

    private function parseFilter($filter, &$params, $fieldTypeMap, &$paramIdx = 1): string
    {
        // Detect group logic (AND by default)
        $logic = 'AND';
        if (is_array($filter) && isset($filter['LOGIC'])) {
            $logic = strtoupper($filter['LOGIC']) === 'OR' ? 'OR' : 'AND';
            unset($filter['LOGIC']);
        }
        $conditions = [];

        foreach ($filter as $key => $value) {
            if (is_array($value) && $this->isAssoc($value)) {
                // Nested group, parse recursively
                $subCond = $this->parseFilter($value, $params, $fieldTypeMap, $paramIdx);
                if ($subCond) {
                    $conditions[] = "($subCond)";
                }
            } elseif (is_array($value)) {
                // Array of values = IN
                preg_match('/^([<>=~!%]+)?([a-zA-Z0-9_]+)$/', $key, $m);
                $op = $m[1] ?? '=';
                $field = $m[2] ?? $key;
                $op = $op === '=' ? 'IN' : $op;
                $field = preg_replace('/[^a-zA-Z0-9_]/', '', $field);

                $paramsList = [];
                foreach ($value as $val) {
                    $paramName = ":filter" . $paramIdx++;
                    $params[$paramName] = $this->formatFilterValue($val, $fieldTypeMap[$field] ?? '');
                    $paramsList[] = $paramName;
                }
                $conditions[] = "\"$field\" IN (" . implode(",", $paramsList) . ")";
            } else {
                // Simple condition
                preg_match('/^([<>=~!%]+)?([a-zA-Z0-9_]+)$/', $key, $m);
                $op = $m[1] ?? '=';
                $field = $m[2] ?? $key;
                $opMap = [
                    '>=' => '>=',
                    '<=' => '<=',
                    '>' => '>',
                    '<' => '<',
                    '!=' => '!=',
                    '<>' => '!=',
                    '~' => 'LIKE',
                    '%' => 'LIKE',
                    '=' => '=',
                    '' => '='
                ];
                $op = $opMap[$op] ?? '=';
                $field = preg_replace('/[^a-zA-Z0-9_]/', '', $field);
                $paramName = ":filter" . $paramIdx++;
                $valueFormatted = $this->formatFilterValue($value, $fieldTypeMap[$field] ?? '');

                if ($op === 'LIKE' && strpos($valueFormatted, '%') === false) {
                    $valueFormatted = "%$valueFormatted%";
                }

                $conditions[] = "\"$field\" $op $paramName";
                $params[$paramName] = $valueFormatted;
            }
        }

        return implode(" $logic ", $conditions);
    }

    private function isAssoc(array $arr)
    {
        if ([] === $arr)
            return false;
        return array_keys($arr) !== range(0, count($arr) - 1);
    }

    private function formatFilterValue($value, $type)
    {
        if (in_array($type, ['date', 'datetime', 'timestamp'])) {
            return $this->toSqlDate($value);
        }
        return $value;
    }

    private function toSqlDate($value)
    {
        if (preg_match('/^\d{2}\.\d{2}\.\d{4} \d{2}:\d{2}:\d{2}$/', $value)) {
            // 31.05.2025 17:01:55
            return Carbon::createFromFormat('d.m.Y H:i:s', $value)->format('Y-m-d H:i:s');
        }
        if (preg_match('/^\d{2}\.\d{2}\.\d{4}$/', $value)) {
            // 31.05.2025
            return Carbon::createFromFormat('d.m.Y', $value)->format('Y-m-d 00:00:00');
        }
        if (preg_match('/^\d{4}-\d{2}-\d{2}( \d{2}:\d{2}:\d{2})?$/', $value)) {
            // 2025-05-31 или 2025-05-31 17:01:55
            return Carbon::parse($value)->format('Y-m-d H:i:s');
        }
        try {
            // Last attempt — Carbon::parse (can handle ISO, RFC, unix timestamp, etc.)
            return Carbon::parse($value)->format('Y-m-d H:i:s');
        } catch (\Exception $e) {
            // Could not convert — leave original
            return $value;
        }
    }

    public function getRecords(
        string $table,
        array $filters = [],
        ?int $limit = null,
        ?int $offset = null,
        array $select = []
    ): array {
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $table)) {
            throw new \InvalidArgumentException('Invalid table name');
        }

        $columns = $select ? implode(',', array_map(fn($c) => "\"$c\"", $select)) : '*';
        $query = "SELECT $columns FROM \"$table\"";

        $fieldsInfo = $this->getTableFields($table);
        $fieldTypeMap = [];
        foreach ($fieldsInfo as $col) {
            $fieldTypeMap[$col['name']] = strtolower($col['type']);
        }

        $params = [];
        $paramIdx = 1;
        $where = '';
        if (!empty($filters)) {
            $where = $this->parseFilter($filters, $params, $fieldTypeMap, $paramIdx);
        }
        if ($where) {
            $query .= ' WHERE ' . $where;
        }
        if ($limit !== null) {
            $query .= " LIMIT " . (int) $limit;
        }
        if ($offset !== null) {
            $query .= " OFFSET " . (int) $offset;
        }

        $this->logger->debug("getRecords.Before executing query", [
            'query' => $query,
            'params' => $params
        ]);

        $stmt = $this->pdo->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listTables(): array
    {
        $stmt = $this->pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%';");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function getTableFields(string $table): array
    {
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $table)) {
            throw new \InvalidArgumentException('Invalid table name');
        }
        $stmt = $this->pdo->query("PRAGMA table_info('$table')");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}