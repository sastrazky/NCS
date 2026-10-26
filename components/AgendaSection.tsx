"use client";

import { motion } from "framer-motion";
import { AGENDA_ITEMS } from "@/data/ncsData";
import { MapPin, User } from "lucide-react";

export default function AgendaSection() {
  const formatDateParts = (dateStr: string) => {
    const d = new Date(dateStr);
    const day = d.getDate();
    const months = ["Jan", "Feb", "Mar", "Apr", "Mei", "Jun", "Jul", "Agt", "Sep", "Okt", "Nov", "Des"];
    const month = months[d.getMonth()];
    const year = d.getFullYear();
    return { day, month, year };
  };

  return (
    <section id="agenda" className="bg-white py-24 sm:py-32">
      <div className="max-w-[1200px] mx-auto px-4 sm:px-8">
        
        {/* Section Header */}
        <div className="max-w-2xl mb-16">
          <span className="text-[13px] font-medium text-[#707070] uppercase tracking-wider block mb-2">
            Jadwal &amp; Agenda
          </span>
          <h2 className="text-[32px] sm:text-[42px] font-bold text-[#1d1d1f] tracking-tight leading-[1.1] mb-3">
            Jadwal Pelatihan &amp; Kegiatan
          </h2>
          <p className="text-[16px] text-[#707070] leading-[1.5]">
            Jadwal workshop sertifikasi Cisco CCNA, pelatihan keamanan jaringan, dan pengujian berkala.
          </p>
        </div>

        {/* Agenda Cards */}
        <div className="space-y-4 max-w-4xl">
          {AGENDA_ITEMS.map((item, idx) => {
            const { day, month, year } = formatDateParts(item.date);
            return (
              <motion.div
                key={item.id}
                initial={{ opacity: 0, y: 20 }}
                whileInView={{ opacity: 1, y: 0 }}
                viewport={{ once: true, margin: "-50px" }}
                transition={{ duration: 0.5, delay: idx * 0.1 }}
                className="apple-card bg-[#f5f5f7] p-6 sm:p-8 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-6"
              >
                <div className="flex items-start sm:items-center gap-5">
                  {/* Date Block */}
                  <div className="w-16 h-16 rounded-[20px] bg-white flex flex-col items-center justify-center text-center flex-shrink-0">
                    <span className="text-[20px] font-bold text-[#1d1d1f] leading-none">
                      {day}
                    </span>
                    <span className="text-[11px] font-semibold uppercase text-[#707070] tracking-wider mt-1">
                      {month}
                    </span>
                  </div>

                  {/* Details */}
                  <div>
                    <div className="flex items-center gap-2 mb-1">
                      <span className="text-[12px] font-semibold text-[#0071e3]">
                        {item.status}
                      </span>
                      <span className="text-[#707070]">•</span>
                      <span className="text-[12px] text-[#707070]">{item.time}</span>
                    </div>
                    <h3 className="text-[18px] sm:text-[20px] font-semibold text-[#1d1d1f] leading-snug mb-1">
                      {item.title}
                    </h3>
                    <p className="text-[14px] text-[#707070] leading-[1.4] max-w-2xl mb-2">
                      {item.description}
                    </p>
                    <div className="flex flex-wrap items-center gap-x-4 gap-y-1 text-[12px] text-[#707070]">
                      <div className="flex items-center gap-1.5">
                        <MapPin className="w-3.5 h-3.5 text-[#1d1d1f]" />
                        <span>{item.location}</span>
                      </div>
                      {item.instructor && (
                        <div className="flex items-center gap-1.5">
                          <User className="w-3.5 h-3.5 text-[#1d1d1f]" />
                          <span>{item.instructor}</span>
                        </div>
                      )}
                    </div>
                  </div>
                </div>

                <div className="sm:self-center flex-shrink-0">
                  <a href="#services" className="apple-btn-secondary !text-[13px] !py-2 !px-4">
                    Informasi Pendaftaran
                  </a>
                </div>
              </motion.div>
            );
          })}
        </div>

      </div>
    </section>
  );
}
