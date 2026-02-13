-- Migration to add time and location fields to existing databases
-- Run this if you already have the database set up

USE party_manager;

-- Add new columns to projects table
ALTER TABLE projects 
ADD COLUMN event_time TIME AFTER event_date,
ADD COLUMN event_end_date DATE AFTER event_time,
ADD COLUMN event_end_time TIME AFTER event_end_date,
ADD COLUMN event_location VARCHAR(255) AFTER event_end_time;
