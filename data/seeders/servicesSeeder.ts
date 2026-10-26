import { ServiceItem } from "./types";

export const servicesSeeder: ServiceItem[] = [
  {
    id: "pentest",
    title: "Penetration Testing & Vulnerability Assessment",
    badge: "Keamanan Siber",
    description: "Pengujian penetrasi berkala terhadap aplikasi web, API, dan infrastruktur server untuk mengidentifikasi celah keamanan sebelum dieksploitasi pihak tidak bertanggung jawab.",
    features: [
      "Pengujian Black-box & Grey-box metodologi OWASP Top 10",
      "Laporan audit formal dengan kalkulasi skor risiko CVSS",
      "Rekomendasi teknis mitigasi dan sesi re-testing"
    ]
  },
  {
    id: "network-audit",
    title: "Audit & Desain Arsitektur Jaringan Enterprise",
    badge: "Infrastruktur",
    description: "Evaluasi topologi jaringan, segmentasi VLAN, firewall rule-base, dan performa throughput untuk memastikan keandalan transmisi data dan skalabilitas organisasi.",
    features: [
      "Review arsitektur & kebijakan routing (BGP/OSPF)",
      "Analisis packet loss dan bottleneck performa trafik",
      "Perancangan blueprint High Availability (HA) & redundansi"
    ]
  },
  {
    id: "forensics",
    title: "Digital Forensics & Incident Response (DFIR)",
    badge: "Investigasi Forensik",
    description: "Investigasi bukti digital pasca-insiden serangan malware, ransomware, atau kebocoran data dengan prosedur Chain of Custody yang legal dan valid.",
    features: [
      "Akuisisi bukti memori RAM dan disk storage forensik",
      "Analisis jejak log sistem dan timeline serangan",
      "Penyusunan laporan investigasi teknis formal"
    ]
  },
  {
    id: "training",
    title: "Pelatihan & Sertifikasi Industri Terstandar",
    badge: "Pelatihan Profesional",
    description: "Program pelatihan teknis intensif bersertifikasi bagi staf TI instansi, dosen, dan mahasiswa di bidang rekayasa jaringan dan pertahanan siber.",
    features: [
      "Kurikulum Cisco Networking Academy & Cyber Range",
      "Instruktur bersertifikasi internasional (CCNA, CEH, Security+)",
      "Hands-on praktikum di perangkat keras enterprise"
    ]
  }
];

