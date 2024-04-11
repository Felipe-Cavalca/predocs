CREATE TABLE IF NOT EXISTS `migration` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `name` VARCHAR(255) NOT NULL,
    `executed_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_migration_name ON `migration` (`name`);
