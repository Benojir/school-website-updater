-- ---------------------------------------------------------------------------
-- Migration: Double-sided support for the Fabric.js ID card builder
-- ---------------------------------------------------------------------------
-- Adds the two columns needed to store an optional BACK side design for the
-- canvas-based student ID card template.
--
-- Card SIZE needs no new column: `canvas_width`/`canvas_height` already hold
-- the size in pixels and the builder works at a fixed 10 px per mm, so
-- millimetres are simply px / 10 (e.g. 85.6 mm <-> 856 px). Orientation is
-- derived from the two values (width > height => horizontal/landscape).
--
-- Safe to run on an existing database: both columns are additive and their
-- defaults (`has_back = 0`) make every already-saved design behave exactly as
-- it did before — single-sided, same size.
-- ---------------------------------------------------------------------------

ALTER TABLE `student_id_card_templates`
  ADD COLUMN `has_back` TINYINT(1) NOT NULL DEFAULT 0 AFTER `design_json`,
  ADD COLUMN `back_design_json` LONGTEXT NULL AFTER `has_back`;
