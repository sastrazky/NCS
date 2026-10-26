"use client";

import { useState } from "react";
import Image from "next/image";
import { motion } from "framer-motion";
import { FACILITIES } from "@/data/ncsData";
import { ChevronRight } from "lucide-react";

export default function Facilities() {
  const [activeFilter, setActiveFilter] = useState<string>("Semua");

  const categories = ["Semua", "Infrastruktur Inti", "Perangkat Jaringan", "Komputasi Analitik", "Keamanan Perimeter"];

  const filteredFacilities =
    activeFilter === "Semua"
      ? FACILITIES
      : FACILITIES.filter((item) => item.category === activeFilter);

  return (
    <section id="facilities" className="bg-[#f5f5f7] py-24 sm:py-32">
      <div className="max-w-[1200px] mx-auto px-4 sm:px-8">
        
        {/* Section Header */}
        <div className="flex flex-col md:flex-row md:items-end justify-between mb-12 gap-6">
          <div className="max-w-xl">
            <span className="text-[13px] font-medium text-[#707070] uppercase tracking-wider block mb-2">
              Sarana &amp; Prasarana
            </span>
            <h2 className="text-[32px] sm:text-[42px] font-bold text-[#1d1d1f] tracking-tight leading-[1.1]">
              Fasilitas Perangkat &amp; Server Lab
            </h2>
            <p className="text-[16px] text-[#707070] mt-2 leading-[1.5]">
              Perangkat jaringan enterprise, server komputasi, dan workstation praktikum untuk mendukung riset serta perkuliahan.
            </p>
          </div>

          {/* Apple Pill Filter Tabs */}
          <div className="flex flex-wrap items-center gap-2">
            {categories.map((cat) => (
              <button
                key={cat}
                onClick={() => setActiveFilter(cat)}
                className={`text-[13px] font-normal px-4 py-1.5 rounded-full transition-all cursor-pointer ${
                  activeFilter === cat
                    ? "bg-[#1d1d1f] text-white"
                    : "bg-[#e8e8ed] text-[#1d1d1f] hover:bg-[#dedee3]"
                }`}
              >
                {cat}
              </button>
            ))}
          </div>
        </div>

        {/* Facilities Grid */}
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
          {filteredFacilities.map((facility, idx) => (
            <motion.div
              key={facility.id}
              initial={{ opacity: 0, y: 25 }}
              whileInView={{ opacity: 1, y: 0 }}
              viewport={{ once: true, margin: "-50px" }}
              transition={{ duration: 0.5, delay: idx * 0.08 }}
              className="apple-card bg-white flex flex-col justify-between"
            >
              <div>
                {/* Image */}
                <div className="relative h-52 w-full bg-[#e8e8ed]">
                  <Image
                    src={facility.image}
                    alt={facility.name}
                    fill
                    sizes="(max-width: 768px) 100vw, (max-width: 1200px) 50vw, 300px"
                    className="object-cover"
                  />
                  <div className="absolute top-4 left-4 bg-white/90 backdrop-blur-md px-3 py-1 rounded-full text-[11px] font-medium text-[#1d1d1f]">
                    {facility.category}
                  </div>
                </div>

                {/* Content */}
                <div className="p-7">
                  <div className="flex items-center justify-between text-[12px] text-[#707070] mb-2 font-mono">
                    <span>{facility.inventoryCode}</span>
                    <span className="text-[#0071e3] font-sans font-medium">{facility.quantity} Unit</span>
                  </div>
                  <h3 className="text-[19px] font-semibold text-[#1d1d1f] leading-snug mb-2">
                    {facility.name}
                  </h3>
                  <p className="text-[13px] text-[#707070] leading-[1.47] mb-3">
                    {facility.description}
                  </p>
                  <p className="text-[11px] text-[#707070] bg-[#f5f5f7] p-2.5 rounded-[12px] leading-relaxed">
                    <strong className="text-[#1d1d1f] block mb-0.5">Spesifikasi:</strong>
                    {facility.specification}
                  </p>
                </div>
              </div>

              <div className="p-7 pt-0">
                <a href="#services" className="apple-link text-[14px]">
                  Permohonan penggunaan <ChevronRight className="w-3.5 h-3.5" />
                </a>
              </div>
            </motion.div>
          ))}
        </div>

      </div>
    </section>
  );
}
