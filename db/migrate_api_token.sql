-- Add API token column for SwaggerHub-friendly auth (run once on existing DBs)
-- mysql -u <user> -p <database_name> < db/migrate_api_token.sql

ALTER TABLE users
    ADD COLUMN api_token VARCHAR(64) NULL DEFAULT NULL AFTER password_hash,
    ADD UNIQUE KEY uq_users_api_token (api_token);
