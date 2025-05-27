<?php

declare(strict_types=1);

namespace App;

use Symfony\Component\HttpFoundation\Response;
use App\DataConnector;
use Psr\Log\LoggerInterface;

class Bitrix24Connector
{
    private $db;
    private $logger;

    public function __construct($connection, LoggerInterface $logger)
    {
        $this->db = new DataConnector($connection, $logger);
        $this->logger = $logger;
    }

    public function check(): Response
    {
        // Bitrix24 expects any non-empty body and 200 OK
        return new Response('OK', 200, ['Content-Type' => 'text/plain']);
    }

    public function tableList(string $searchString = ''): Response
    {
        $tables = $this->db->listTables();
        $result = [];
        foreach ($tables as $table) {
            if (!$searchString || stripos($table, $searchString) !== false) {
                $result[] = [
                    'code' => $table,
                    'title' => $table
                ];
            }
        }
        return $this->jsonResponse($result);
    }

    public function tableDescription(string $table): Response
    {
        $fields = $this->db->getTableFields($table);
        $allowedTypes = [
            'INT', 'INTEGER', 'BIGINT', 'SMALLINT', 'TINYINT',
            'VARCHAR', 'CHAR', 'STRING', 'TEXT',
            'REAL', 'FLOAT', 'DOUBLE',
            'DATE', 'DATETIME'
        ];
        $typeMap = [
            'INT' => 'INT',
            'INTEGER' => 'INT',
            'BIGINT' => 'INT',
            'SMALLINT' => 'INT',
            'TINYINT' => 'INT',
            'VARCHAR' => 'STRING',
            'CHAR' => 'STRING',
            'TEXT' => 'STRING',
            'STRING' => 'STRING',
            'REAL' => 'DOUBLE',
            'FLOAT' => 'DOUBLE',
            'DOUBLE' => 'DOUBLE',
            'DATE' => 'DATE',
            'DATETIME' => 'DATETIME',
        ];
        $result = [];
        foreach ($fields as $field) {
            $type = strtoupper(preg_replace('/[^a-zA-Z]/', '', $field['type']));
            if (in_array($type, $allowedTypes)) {
                $result[] = [
                    'code' => $field['name'],
                    'name' => $field['name'],
                    'type' => $typeMap[$type] ?? 'string'
                ];
            }
        }
        return $this->jsonResponse($result);
    }

    public function getData(string $table, array $select = [], array $filter = [], int $limit = 100): Response
    {
        $rows = $this->db->getRecords($table, $filter, $limit, null, $select);
        $header = $select ?: array_keys(reset($rows) ?: []);
        $result = [$header];
        foreach ($rows as $row) {
            $result[] = array_values($row);
        }
        return $this->jsonResponse($result);
    }

    private function jsonResponse($data, $status = 200, $error = null): Response
    {
        $payload = $error ? ['error' => $error] : $data;
        return new Response(
            json_encode($payload, JSON_UNESCAPED_UNICODE),
            $status,
            ['Content-Type' => 'application/json']
        );
    }
}