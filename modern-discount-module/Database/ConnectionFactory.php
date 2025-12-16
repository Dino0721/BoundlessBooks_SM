<?php
/**
 * Database Connection Factory
 * Single Responsibility: Create and manage database connections
 * Follows: Singleton pattern
 */

namespace BoundlessBooks\Database;

use PDO;
use BoundlessBooks\Exceptions\DatabaseException;

class ConnectionFactory
{
    private static ?PDO $connection = null;
    private array $config;

    public function __construct(array $config)
    {
        $this->config = $config['database'];
    }

    /**
     * Get or create PDO connection (Singleton)
     */
    public function getConnection(): PDO
    {
        if (self::$connection === null) {
            self::$connection = $this->createConnection();
        }
        return self::$connection;
    }

    /**
     * Create new database connection
     */
    private function createConnection(): PDO
    {
        try {
            $dsn = sprintf(
                'mysql:host=%s;dbname=%s;charset=%s',
                $this->config['host'],
                $this->config['name'],
                $this->config['charset']
            );

            return new PDO(
                $dsn,
                $this->config['user'],
                $this->config['password'],
                [
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]
            );
        } catch (\PDOException $e) {
            throw new DatabaseException('Failed to connect to database: ' . $e->getMessage());
        }
    }

    /**
     * Reset connection (useful for testing)
     */
    public static function resetConnection(): void
    {
        self::$connection = null;
    }
}
