-- Written for SQLite (the zero-setup default). Adjust the column types
-- (e.g. AUTOINCREMENT -> AUTO_INCREMENT/SERIAL) if you switch DB_CONNECTION.
CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    created_at TEXT DEFAULT CURRENT_TIMESTAMP
);
