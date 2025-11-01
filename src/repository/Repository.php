<?php

require_once __DIR__ . '/Database.php';

abstract class Repository {
    
    protected $database;
    
    public function __construct() {
        $this->database = Database::getInstance()->getConnection();
    }
    
    protected function executeQuery(string $query, array $params = []): PDOStatement {
        try {
            $stmt = $this->database->prepare($query);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            throw new Exception("Query execution failed: " . $e->getMessage());
        }
    }
    
    protected function fetchOne(string $query, array $params = []): ?array {
        $stmt = $this->executeQuery($query, $params);
        $result = $stmt->fetch();
        return $result ?: null;
    }
    
    protected function fetchAll(string $query, array $params = []): array {
        $stmt = $this->executeQuery($query, $params);
        return $stmt->fetchAll();
    }
    
    protected function insert(string $table, array $data): ?int {
        $columns = array_keys($data);
        $placeholders = array_map(fn($col) => ":$col", $columns);
        
        $query = sprintf(
            "INSERT INTO %s (%s) VALUES (%s) RETURNING id",
            $table,
            implode(', ', $columns),
            implode(', ', $placeholders)
        );
        
        $params = [];
        foreach ($data as $key => $value) {
            $params[":$key"] = $value;
        }
        
        $result = $this->fetchOne($query, $params);
        return $result ? (int)$result['id'] : null;
    }
    
    protected function update(string $table, int $id, array $data): bool {
        $setParts = [];
        foreach (array_keys($data) as $column) {
            $setParts[] = "$column = :$column";
        }
        
        $query = sprintf(
            "UPDATE %s SET %s WHERE id = :id",
            $table,
            implode(', ', $setParts)
        );
        
        $params = [':id' => $id];
        foreach ($data as $key => $value) {
            $params[":$key"] = $value;
        }
        
        $stmt = $this->executeQuery($query, $params);
        return $stmt->rowCount() > 0;
    }
    
    protected function delete(string $table, int $id): bool {
        $query = "DELETE FROM $table WHERE id = :id";
        $stmt = $this->executeQuery($query, [':id' => $id]);
        return $stmt->rowCount() > 0;
    }
    
    protected function findById(string $table, int $id): ?array {
        $query = "SELECT * FROM $table WHERE id = :id";
        return $this->fetchOne($query, [':id' => $id]);
    }
    
    protected function findAll(string $table, string $orderBy = 'id'): array {
        $query = "SELECT * FROM $table ORDER BY $orderBy";
        return $this->fetchAll($query);
    }
    
    public function beginTransaction(): bool {
        return $this->database->beginTransaction();
    }
    
    public function commit(): bool {
        return $this->database->commit();
    }
    
    public function rollback(): bool {
        return $this->database->rollBack();
    }
}