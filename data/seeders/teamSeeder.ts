import { TeamMember } from "./types";

export const teamSeeder: TeamMember[] = [
  {
    id: 1,
    nip: "19780512 200312 1 002",
    name: "Dr. Eng. Rosa Andrie Asmara, S.T., M.T.",
    role: "Kepala Laboratorium",
    division: "Pimpinan & Riset Utama",
    specialization: ["Network Security", "Applied Cryptography", "Digital Forensics"],
    photo: "/uploads/anggota/anggota_1765461875_693acf734178b.jpg",
    email: "rosa@polinema.ac.id",
    googleScholar: "https://scholar.google.com"
  },
  {
    id: 2,
    nip: "19840215 200812 1 004",
    name: "Moch. Zawaruddin Abdullah, S.ST., M.Kom.",
    role: "Dosen Pembina Keamanan Siber",
    division: "Cyber Defense & SOC",
    specialization: ["Penetration Testing", "Malware Analysis", "Threat Hunting"],
    photo: "/uploads/anggota/anggota_1765461920_693acfa0d53b9.jpg",
    email: "zawaruddin@polinema.ac.id"
  },
  {
    id: 3,
    nip: "19820719 200501 1 003",
    name: "Dedy Hidayat Kusuma, S.T., M.Cs.",
    role: "Dosen Pembina Infrastruktur",
    division: "Network Architecture",
    specialization: ["Software-Defined Networking (SDN)", "Cloud Infrastructure", "BGP Routing"],
    photo: "/uploads/anggota/anggota_1765461957_693acfc5077f5.jpg",
    email: "dedy@polinema.ac.id"
  },
  {
    id: 4,
    nip: "19901103 201903 1 011",
    name: "M. Hasyim Ratsanjani, S.Kom., M.Kom.",
    role: "Instruktur & Peneliti Muda",
    division: "Digital Investigation",
    specialization: ["Incident Response", "Memory Forensics", "Network Traffic Analysis"],
    photo: "/uploads/anggota/anggota_1765802327_69400157e2019.jpg",
    email: "hasyim@polinema.ac.id"
  },
  {
    id: 5,
    nip: "19950821 202203 1 008",
    name: "Ahmad Wildan, S.Tr.Kom.",
    role: "Laboran & System Engineer",
    division: "Operasional Lab & Server",
    specialization: ["Server Virtualization", "Kubernetes Clustering", "Firewall Hardening"],
    photo: "/uploads/anggota/anggota_1765853351_6940c8a7b62c8.jpg",
    email: "wildan.lab@polinema.ac.id"
  },
  {
    id: 6,
    name: "Tim Asisten Riset Mahasiswa",
    role: "Asisten Praktikum & Anggota CTF",
    division: "Student Chapter NCS",
    specialization: ["Binary Exploitation", "Web Vulnerability", "Network Automation"],
    photo: "/uploads/anggota/anggota_1765853413_6940c8e5904fe.jpg"
  }
];

