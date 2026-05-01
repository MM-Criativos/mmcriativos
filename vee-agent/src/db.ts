import mysql from "mysql2/promise";
import { DB_HOST, DB_PORT, DB_USER, DB_PASSWORD, DB_DATABASE } from "./config.js";

export const pool = mysql.createPool({
  host: DB_HOST,
  port: Number.isNaN(DB_PORT) ? 3306 : DB_PORT,
  user: DB_USER,
  password: DB_PASSWORD,
  database: DB_DATABASE,
  waitForConnections: true,
  connectionLimit: 10,
  timezone: "Z"
});

export async function ensureTables(): Promise<void> {
  const conn = await pool.getConnection();
  try {
    await conn.execute(`
      CREATE TABLE IF NOT EXISTS vee_sessions (
        id VARCHAR(36) NOT NULL PRIMARY KEY,
        title VARCHAR(255) NOT NULL DEFAULT 'Nova tarefa',
        mode ENUM('chat', 'cowork') NOT NULL DEFAULT 'chat',
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    `);

    await conn.execute(`
      CREATE TABLE IF NOT EXISTS vee_messages (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
        session_id VARCHAR(36) NOT NULL,
        role ENUM('user', 'assistant', 'tool') NOT NULL,
        content MEDIUMTEXT,
        tool_calls JSON,
        tool_call_id VARCHAR(255),
        tool_name VARCHAR(255),
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_session_id (session_id),
        CONSTRAINT fk_vee_messages_session FOREIGN KEY (session_id)
          REFERENCES vee_sessions (id) ON DELETE CASCADE
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    `);


    await conn.execute(`
      CREATE TABLE IF NOT EXISTS vee_test_runs (
        id           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
        group_id     VARCHAR(10)     NOT NULL,
        status       ENUM('pass','fail','error') NOT NULL,
        execution_id VARCHAR(100)    DEFAULT NULL,
        route        VARCHAR(50)     DEFAULT NULL,
        tenant_slug  VARCHAR(100)    DEFAULT NULL,
        message      TEXT            DEFAULT NULL,
        detail       TEXT            DEFAULT NULL,
        executed_at  TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_group_id    (group_id),
        INDEX idx_executed_at (executed_at)
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    `);

    // Add context summary columns if they don't exist yet (idempotent)
    try {
      await conn.execute(`
        ALTER TABLE vee_sessions
          ADD COLUMN context_summary MEDIUMTEXT DEFAULT NULL,
          ADD COLUMN context_summary_turns INT UNSIGNED NOT NULL DEFAULT 0
      `);
    } catch {
      // Columns already exist — safe to ignore
    }
  } finally {
    conn.release();
  }
}
