/**
 * Database Seeders & Mock Data for NCS Lab
 * Inspired by Laravel Seeder pattern: modular, structured, and extensible.
 */
import {
  profileSeeder,
  externalLinksSeeder,
  teamSeeder,
  facilitiesSeeder,
  gallerySeeder,
  agendaSeeder,
  archivesSeeder,
  servicesSeeder,
} from "./seeders";

export * from "./seeders/types";
export * from "./seeders";

// Compatibility aliases
export const LAB_PROFILE = profileSeeder;
export const EXTERNAL_LINKS = externalLinksSeeder;
export const TEAM_MEMBERS = teamSeeder;
export const FACILITIES = facilitiesSeeder;
export const GALLERY_ITEMS = gallerySeeder;
export const AGENDA_ITEMS = agendaSeeder;
export const ARCHIVE_ITEMS = archivesSeeder;
export const SERVICES = servicesSeeder;
