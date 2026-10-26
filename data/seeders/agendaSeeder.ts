import { AgendaItem } from "./types";

export const agendaSeeder: AgendaItem[] = [
  {
    id: 1,
    title: "Workshop Hands-on: Blue Team Incident Defense & SIEM Analytics",
    category: "Workshop",
    date: "2026-11-08",
    time: "09:00 – 15:30 WIB",
    location: "Ruang Lab NCS, Gedung Sipil Lt. 7",
    instructor: "Moch. Zawaruddin Abdullah & Tim SOC",
    status: "Akan Datang",
    description: "Bedah taktik mendeteksi serangan lateral movement dan ransomware menggunakan Wazuh serta Elastic SIEM."
  },
  {
    id: 2,
    title: "Bootcamp Persiapan Sertifikasi CCNA & MTCNA 2026",
    category: "Pelatihan",
    date: "2026-11-15",
    endDate: "2026-11-17",
    time: "08:30 – 16:00 WIB",
    location: "Lab Jaringan Terpadu JTI Polinema",
    instructor: "Dedy Hidayat Kusuma & Cisco Certified Trainer",
    status: "Akan Datang",
    description: "Pelatihan intensif 3 hari meliputi switching, IPv6 subnetting, security ACLs, dan dasar otomasi jaringan."
  },
  {
    id: 3,
    title: "Evaluasi & Vulnerability Assessment Sistem Informasi Kampus",
    category: "Audit Teknis",
    date: "2026-10-24",
    endDate: "2026-10-28",
    time: "10:00 – 16:00 WIB",
    location: "NCS Security Operations Center",
    instructor: "Dr. Eng. Rosa Andrie Asmara & Tim Auditor",
    status: "Berlangsung",
    description: "Evaluasi kerentanan berkala pada subnet layanan akademik dan infrastruktur portal Politeknik Negeri Malang."
  }
];

