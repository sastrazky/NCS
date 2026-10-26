"use client";

import { motion } from "framer-motion";
import { LAB_PROFILE, FACILITIES, TEAM_MEMBERS, ARCHIVE_ITEMS } from "@/data/ncsData";

export default function StatsSection() {
  const stats = [
    {
      value: `${FACILITIES.reduce((acc, curr) => acc + curr.quantity, 0)}+`,
      label: "Unit Perangkat & Server",
      sub: "Server rack Dell PowerEdge, router & switch L3 Cisco, serta firewall Fortinet.",
    },
    {
      value: `${TEAM_MEMBERS.length}+`,
      label: "Dosen & Anggota Peneliti",
      sub: "Dosen pembina, instruktur sertifikasi internasional, dan asisten praktikum.",
    },
    {
      value: `${ARCHIVE_ITEMS.length}+`,
      label: "Dokumen & Modul Riset",
      sub: "Modul praktikum terapan, laporan audit keamanan, dan penelitian ilmiah.",
    },
    {
      value: "100%",
      label: "Kurikulum Terstandar",
      sub: "Terafiliasi resmi Cisco Networking Academy dan panduan keamanan BSSN.",
    },
  ];

  return (
    <section className="bg-[#f5f5f7] py-24 sm:py-28">
      <div className="max-w-[1200px] mx-auto px-4 sm:px-8">
        
        {/* Section Header Left-Aligned */}
        <div className="max-w-xl mb-14">
          <h2 className="text-[28px] sm:text-[32px] font-semibold text-[#1d1d1f] tracking-[0.007em] mb-2">
            Beberapa Fasilitas &amp; Kapasitas Lab.
          </h2>
          <p className="text-[17px] text-[#707070] leading-[1.47]">
            Ringkasan sarana komputasi, perangkat jaringan, dan sumber daya riset aktif di Laboratorium NCS JTI Polinema.
          </p>
        </div>

        {/* 4 Cards Grid (28px radius, white background, ZERO shadow, no border) */}
        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
          {stats.map((item, idx) => (
            <motion.div
              key={item.label}
              initial={{ opacity: 0, y: 20 }}
              whileInView={{ opacity: 1, y: 0 }}
              viewport={{ once: true, margin: "-50px" }}
              transition={{ duration: 0.5, delay: idx * 0.1 }}
              className="apple-card bg-white p-8 flex flex-col justify-between"
            >
              <div>
                <span className="text-[48px] sm:text-[56px] font-bold text-[#1d1d1f] tracking-tight leading-none block mb-2">
                  {item.value}
                </span>
                <h3 className="text-[17px] font-semibold text-[#1d1d1f] mb-1">
                  {item.label}
                </h3>
              </div>
              <p className="text-[14px] text-[#707070] leading-[1.4] mt-4">
                {item.sub}
              </p>
            </motion.div>
          ))}
        </div>

      </div>
    </section>
  );
}
