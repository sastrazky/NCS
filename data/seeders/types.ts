export interface TeamMember {
  id: number;
  nip?: string;
  name: string;
  role: string;
  division: string;
  specialization: string[];
  photo: string;
  email?: string;
  googleScholar?: string;
}

export interface Facility {
  id: number;
  inventoryCode: string;
  name: string;
  category: "Infrastruktur Inti" | "Perangkat Jaringan" | "Komputasi Analitik" | "Keamanan Perimeter";
  brand: string;
  model: string;
  quantity: number;
  description: string;
  specification: string;
  image: string;
  status: "Tersedia" | "Digunakan";
}

export interface GalleryItem {
  id: number;
  title: string;
  category: "Workshop" | "Riset" | "Praktikum" | "Kompetisi";
  date: string;
  location: string;
  image: string;
  description: string;
}

export interface AgendaItem {
  id: number;
  title: string;
  category: string;
  date: string;
  endDate?: string;
  time: string;
  location: string;
  instructor?: string;
  status: "Akan Datang" | "Berlangsung" | "Selesai";
  description: string;
}

export interface ArchiveItem {
  id: number;
  documentCode: string;
  title: string;
  category: "Penelitian" | "Pengabdian" | "Modul";
  year: number;
  fileSize: string;
  author: string;
  downloadUrl: string;
}

export interface ServiceItem {
  id: string;
  title: string;
  badge: string;
  description: string;
  features: string[];
}

