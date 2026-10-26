import { Facility } from "./types";

export const facilitiesSeeder: Facility[] = [
  {
    id: 1,
    inventoryCode: "NCS-SRV-01",
    name: "Server Rack & High-Performance Compute Cluster",
    category: "Infrastruktur Inti",
    brand: "Dell EMC",
    model: "PowerEdge R740 2U Dual Xeon Silver",
    quantity: 4,
    description: "Klaster server enterprise untuk virtualisasi Proxmox VE, simulasi skenario cyber attack, honeynet, dan klaster private cloud.",
    specification: "Dual Intel Xeon Silver 4210R, 128GB DDR4 ECC, 4x 1.92TB NVMe SSD RAID-10, 10GbE SFP+ Dual Port",
    image: "/uploads/sarana/sarana_1765786334_693fc2decd90e.jpg",
    status: "Tersedia"
  },
  {
    id: 2,
    inventoryCode: "NCS-NET-02",
    name: "Cisco Enterprise Switching & Routing Pods",
    category: "Perangkat Jaringan",
    brand: "Cisco Systems",
    model: "Catalyst 3850 Series Layer-3 & ISR 4331",
    quantity: 16,
    description: "Rak switch multilayer dan router untuk konfigurasi routing dinamis skala luas (BGP, OSPF, MPLS), VLAN trunking, dan QoS enterprise.",
    specification: "48-Port Gigabit PoE+, 4x 10G Uplink, IP Base Feature Set, Hardware Stacking 480 Gbps",
    image: "/uploads/sarana/sarana_1765786517_693fc395d144f.jpg",
    status: "Tersedia"
  },
  {
    id: 3,
    inventoryCode: "NCS-WKS-03",
    name: "Cyber Security Investigation Workstations",
    category: "Komputasi Analitik",
    brand: "HP ProDesk",
    model: "Z2 G9 Workstation Tower",
    quantity: 36,
    description: "Workstation analisis dengan lingkungan isolasi sandbox network untuk analisa reverse engineering malware dan latihan Capture The Flag (CTF).",
    specification: "Intel Core i7-13700, 32GB RAM, 1TB NVMe, Dual IPS Monitor 24-inch, Gigabit Isolated NIC",
    image: "/images/LABNCS.jpg",
    status: "Tersedia"
  },
  {
    id: 4,
    inventoryCode: "NCS-SEC-04",
    name: "Next-Generation Firewall (NGFW) Hardware Appliance",
    category: "Keamanan Perimeter",
    brand: "Fortinet",
    model: "FortiGate 100F Enterprise Firewall",
    quantity: 6,
    description: "Perangkat firewall generasi baru untuk simulasi inspeksi paket mendalam (DPI), Intrusion Prevention System (IPS), dan secure SD-WAN.",
    specification: "1 Gbps Threat Protection, 10 Gbps Firewall Throughput, 16x GE RJ45, 4x 10GE SFP+ Slots",
    image: "/images/gedungsipil2.png",
    status: "Tersedia"
  }
];

