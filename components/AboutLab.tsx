"use client";

import Image from "next/image";
import { motion } from "framer-motion";
import { LAB_PROFILE } from "@/data/ncsData";
import { ChevronRight, Check } from "lucide-react";

export default function AboutLab() {
  return (
    <section id="about" className="bg-white py-24 sm:py-32">
      <div className="max-w-[1200px] mx-auto px-4 sm:px-8">
        
        {/* Section Header */}
        <div className="max-w-2xl mb-16">
          <span className="text-[13px] font-medium text-[#707070] uppercase tracking-wider block mb-2">
            Profil Laboratorium
          </span>
          <h2 className="text-[32px] sm:text-[42px] font-bold text-[#1d1d1f] tracking-tight leading-[1.1] mb-3">
            Mengenal Lab Network &amp; Cyber Security
          </h2>
          <p className="text-[16px] sm:text-[18px] text-[#707070] leading-[1.5]">
            Unit pelaksana praktikum dan riset terapan di Jurusan Teknologi Informasi Politeknik Negeri Malang yang berfokus pada teknologi jaringan komputer dan keamanan sistem informasi.
          </p>
        </div>

        {/* Feature Showcase Card */}
        <motion.div
          initial={{ opacity: 0, y: 25 }}
          whileInView={{ opacity: 1, y: 0 }}
          viewport={{ once: true, margin: "-50px" }}
          transition={{ duration: 0.6 }}
          className="apple-card bg-[#f5f5f7] p-8 sm:p-12 mb-8 grid grid-cols-1 lg:grid-cols-12 gap-8 items-center"
        >
          <div className="lg:col-span-6">
            <span className="text-[13px] font-semibold text-[#707070] uppercase tracking-wider block mb-2">
              Sejarah Singkat
            </span>
            <h3 className="text-[24px] sm:text-[30px] font-bold text-[#1d1d1f] tracking-tight leading-tight mb-4">
              Pusat Praktikum &amp; Sertifikasi Berstandar Industri
            </h3>
            <p className="text-[15px] sm:text-[16px] text-[#707070] leading-[1.5] mb-6">
              {LAB_PROFILE.history}
            </p>
            <a href="#services" className="apple-link text-[16px]">
              Lihat layanan konsultasi lab <ChevronRight className="w-4 h-4" />
            </a>
          </div>

          <div className="lg:col-span-6 relative h-[280px] sm:h-[360px] rounded-[24px] overflow-hidden bg-white">
            <Image
              src="/images/gedung-sipil.jpg"
              alt="Gedung Sipil & JTI Polinema"
              fill
              sizes="(max-width: 1024px) 100vw, 550px"
              className="object-cover"
            />
          </div>
        </motion.div>

        {/* 2-Column Cards: Visi & Misi */}
        <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
          
          {/* Visi */}
          <motion.div
            initial={{ opacity: 0, y: 20 }}
            whileInView={{ opacity: 1, y: 0 }}
            viewport={{ once: true, margin: "-50px" }}
            transition={{ duration: 0.6 }}
            className="apple-card bg-[#f5f5f7] p-8 sm:p-10 flex flex-col justify-between"
          >
            <div>
              <span className="text-[13px] font-semibold text-[#707070] uppercase tracking-wider block mb-2">
                Arah Strategis
              </span>
              <h3 className="text-[24px] sm:text-[28px] font-bold text-[#1d1d1f] mb-4">
                Visi Laboratorium
              </h3>
              <p className="text-[17px] text-[#1d1d1f] leading-[1.47] font-normal">
                &ldquo;{LAB_PROFILE.vision}&rdquo;
              </p>
            </div>
            <div className="mt-8 pt-6 border-t border-[#d6d6d6]/60 text-[14px] text-[#707070]">
              Mendukung visi Polinema sebagai institusi vokasi unggul berdaya saing global.
            </div>
          </motion.div>

          {/* Misi */}
          <motion.div
            initial={{ opacity: 0, y: 20 }}
            whileInView={{ opacity: 1, y: 0 }}
            viewport={{ once: true, margin: "-50px" }}
            transition={{ duration: 0.6, delay: 0.15 }}
            className="apple-card bg-[#f5f5f7] p-8 sm:p-10 flex flex-col justify-between"
          >
            <div>
              <span className="text-[13px] font-semibold text-[#707070] uppercase tracking-wider block mb-2">
                Langkah Nyata
              </span>
              <h3 className="text-[24px] sm:text-[28px] font-bold text-[#1d1d1f] mb-4">
                Misi Utama
              </h3>
              <ul className="space-y-3">
                {LAB_PROFILE.missions.slice(0, 3).map((item, idx) => (
                  <li key={idx} className="flex items-start gap-3 text-[15px] text-[#1d1d1f] leading-[1.4]">
                    <div className="w-5 h-5 rounded-full bg-[#0071e3]/10 text-[#0071e3] flex items-center justify-center flex-shrink-0 mt-0.5">
                      <Check className="w-3 h-3 stroke-[2.5]" />
                    </div>
                    <span>{item}</span>
                  </li>
                ))}
              </ul>
            </div>
            <div className="mt-8 pt-6 border-t border-[#d6d6d6]/60">
              <a href="#services" className="apple-link text-[15px]">
                Program sertifikasi mahasiswa <ChevronRight className="w-3.5 h-3.5" />
              </a>
            </div>
          </motion.div>

        </div>

      </div>
    </section>
  );
}
