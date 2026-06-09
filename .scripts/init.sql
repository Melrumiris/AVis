-- init.sql
-- AVis PostgreSQL Schema
-- Optimized for the Kaggle US Accidents dataset (7.7M rows)

BEGIN;

-- ══════════════════════════════════════════════════════════════════
-- Enable UUID extension
-- ══════════════════════════════════════════════════════════════════
CREATE EXTENSION IF NOT EXISTS pgcrypto;

-- ══════════════════════════════════════════════════════════════════
-- Users table
-- ══════════════════════════════════════════════════════════════════
DROP TYPE IF EXISTS user_role CASCADE;
CREATE TYPE user_role AS ENUM ('user', 'admin');

DROP TABLE IF EXISTS users CASCADE;

CREATE TABLE users (
    id          UUID         PRIMARY KEY DEFAULT gen_random_uuid(),
    username    VARCHAR(100) NOT NULL,
    email       VARCHAR(255) NOT NULL UNIQUE,
    password    VARCHAR(255) NOT NULL,
    role        user_role    NOT NULL DEFAULT 'user',
    bio         TEXT         NOT NULL DEFAULT '',
    profile_pic VARCHAR(500),
    user_lat    FLOAT,
    user_lng    FLOAT
);

-- Index for login lookups
CREATE INDEX idx_users_username ON users (username);

-- ══════════════════════════════════════════════════════════════════
-- Accidents table (accident records)
-- ══════════════════════════════════════════════════════════════════
DROP TABLE IF EXISTS accidents CASCADE;

CREATE TABLE accidents (
    id          VARCHAR(20)    PRIMARY KEY,
    date_time   TIMESTAMP      NOT NULL,
    severity    SMALLINT       NOT NULL CHECK (severity BETWEEN 1 AND 4),
    latitude    NUMERIC(10,6),
    longitude   NUMERIC(10,6),
    state       VARCHAR(2),
    city        VARCHAR(100),
    county      VARCHAR(100),
    weather_condition VARCHAR(100),
    temperature NUMERIC(5,2),
    visibility  NUMERIC(5,2),
    crossing    BOOLEAN,
    junction    BOOLEAN,
    traffic_signal BOOLEAN,
    sunrise_sunset VARCHAR(10)
);

-- B-Tree indexes to prevent query timeouts on 7.7M rows
CREATE INDEX idx_accidents_date_time  ON accidents (date_time);
CREATE INDEX idx_accidents_severity   ON accidents (severity);
CREATE INDEX idx_accidents_state      ON accidents (state);

-- Composite index for common filter combinations (date range + severity)
CREATE INDEX idx_accidents_date_sev   ON accidents (date_time, severity);

-- Index for map queries (lat/lng not null, limited by date)
CREATE INDEX idx_accidents_geo        ON accidents (latitude, longitude)
    WHERE latitude IS NOT NULL AND longitude IS NOT NULL;

COMMIT;
