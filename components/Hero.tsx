"use client";

import Image from "next/image";
import { motion } from "framer-motion";
import { ChevronRight } from "lucide-react";
import { LAB_PROFILE } from "@/data/ncsData";

export default function Hero() {
  return (
    <section id="hero" className="relative bg-white pt-16 pb-24 sm:pt-24 sm:pb-32 overflow-hidden">
      <div className="max-w-[1200px] mx-auto px-4 sm:px-8 text-center flex flex-col items-center">
        
        {/* Eyebrow Label (Clean, neutral without orange 'Baru' badge) */}
        <motion.div
          initial={{ opacity: 0, y: 15 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ duration: 0.6 }}
          className="flex items-center justify-center gap-2 mb-3 text-[14px] text-[#707070] font-normal"
        >
          <span>Gedung Sipil &amp; JTI Lantai 7</span>
          <span>•</span>
          <span>Politeknik Negeri Malang</span>
        </motion.div>

        {/* Hero Headline */}
        <motion.h1
          initial={{ opacity: 0, y: 20 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ duration: 0.7, delay: 0.1 }}
          className="text-[40px] sm:text-[58px] md:text-[72px] lg:text-[84px] font-bold text-[#1d1d1f] tracking-tight leading-[1.08] max-w-4xl mb-4"
        >
          Laboratorium Network &amp; Cyber Security
        </motion.h1>

        {/* Hero Subtext */}
        <motion.p
          initial={{ opacity: 0, y: 20 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ duration: 0.7, delay: 0.2 }}
          className="text-[17px] sm:text-[20px] text-[#707070] leading-[1.5] max-w-[640px] mb-8 font-normal"
        >
          Fasilitas praktikum, penelitian terapan, dan sertifikasi kompetensi bidang arsitektur jaringan serta keamanan informasi di Jurusan Teknologi Informasi Politeknik Negeri Malang.
        </motion.p>

        {/* Action Pair */}
        <motion.div
          initial={{ opacity: 0, y: 20 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ duration: 0.7, delay: 0.3 }}
          className="flex flex-wrap items-center justify-center gap-4 sm:gap-6 mb-16"
        >
          <a href="#facilities" className="apple-btn-primary">
            Lihat Fasilitas Lab
          </a>
          <a href="#services" className="apple-link">
            Layanan &amp; Konsultasi <ChevronRight className="w-4 h-4 ml-0.5" />
          </a>
        </motion.div>

        {/* Large Product-First Visual Showcase (Removed 'Lingkungan Riset Modern') */}
        <motion.div
          initial={{ opacity: 0, scale: 0.98, y: 30 }}
          animate={{ opacity: 1, scale: 1, y: 0 }}
          transition={{ duration: 0.9, delay: 0.35, ease: [0.16, 1, 0.3, 1] }}
          className="relative w-full max-w-[1060px] h-[340px] sm:h-[480px] md:h-[560px] rounded-[28px] overflow-hidden bg-[#f5f5f7]"
        >
          <Image
            src="/images/LABNCS.jpg"
            alt="Laboratorium NCS JTI Polinema"
            fill
            sizes="(max-width: 1200px) 100vw, 1060px"
            className="object-cover"
            priority
          />
        </motion.div>

      </div>
    </section>
  );
}
