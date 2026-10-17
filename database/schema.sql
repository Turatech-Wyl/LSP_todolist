-- ============================================================
-- Skema database aplikasi To-Do List Rencana Baca Buku
-- DBMS   : SQLite 3 (file: database/rencana_baca.sqlite)
-- ============================================================

CREATE TABLE IF NOT EXISTS buku (
    id              INTEGER PRIMARY KEY AUTOINCREMENT,
    judul           TEXT    NOT NULL,
    penulis         TEXT    NOT NULL,
    target_selesai  TEXT    NOT NULL,              -- format YYYY-MM-DD
    status          TEXT    NOT NULL DEFAULT 'Belum Dibaca'
                    CHECK (status IN ('Belum Dibaca', 'Sedang Dibaca', 'Selesai')),
    dibuat_pada     TEXT    NOT NULL DEFAULT (datetime('now', 'localtime')),
    diubah_pada     TEXT    NOT NULL DEFAULT (datetime('now', 'localtime'))
);
