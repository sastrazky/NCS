"use client";

import { useState } from "react";
import { motion } from "framer-motion";
import { ARCHIVE_ITEMS } from "@/data/ncsData";
import { Download } from "lucide-react";

export default function ArchivesSection() {
  const [filter, setFilter] = useState<string>("Semua");
  const [searchTerm, setSearchTerm] = useState<string>("");

  const categories = ["Semua", "Penelitian", "Pengabdian", "Modul"];

  const filteredArchives = ARCHIVE_ITEMS.filter((item) => {
    const matchCat = filter === "Semua" || item.category === filter;
    const matchSearch =
      item.title.toLowerCase().includes(searchTerm.toLowerCase()) ||
      item.author.toLowerCase().includes(searchTerm.toLowerCase());
    return matchCat && matchSearch;
  });

  return (
    <section id="archives" className="bg-[#f5f5f7] py-24 sm:py-32">
      <div className="max-w-[1200px] mx-auto px-4 sm:px-8">
        
        {/* Header */}
        <div className="flex flex-col md:flex-row md:items-end justify-between mb-12 gap-6">
          <div className="max-w-xl">
            <span className="text-[13px] font-medium text-[#707070] uppercase tracking-wider block mb-2">
              Repositori Dokumen
            </span>
            <h2 className="text-[32px] sm:text-[42px] font-bold text-[#1d1d1f] tracking-tight leading-[1.1]">
              Modul Praktikum &amp; Laporan Riset
            </h2>
            <p className="text-[16px] text-[#707070] mt-2 leading-[1.5]">
              Kumpulan modul pembelajaran jaringan, laporan pengabdian masyarakat, dan dokumen panduan lab.
            </p>
          </div>

          {/* Filter Pills */}
          <div className="flex flex-wrap items-center gap-2">
            {categories.map((cat) => (
              <button
                key={cat}
                onClick={() => setFilter(cat)}
                className={`text-[13px] font-normal px-4 py-1.5 rounded-full transition-all cursor-pointer ${
                  filter === cat
                    ? "bg-[#1d1d1f] text-white"
                    : "bg-[#e8e8ed] text-[#1d1d1f] hover:bg-[#dedee3]"
                }`}
              >
                {cat}
              </button>
            ))}
          </div>
        </div>

        {/* Grid */}
        <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
          {filteredArchives.map((doc, idx) => (
            <motion.div
              key={doc.id}
              initial={{ opacity: 0, y: 20 }}
              whileInView={{ opacity: 1, y: 0 }}
              viewport={{ once: true, margin: "-50px" }}
              transition={{ duration: 0.5, delay: idx * 0.1 }}
              className="apple-card bg-white p-8 flex flex-col justify-between"
            >
              <div>
                <div className="flex items-center justify-between text-[13px] text-[#707070] mb-3">
                  <span className="font-medium text-[#1d1d1f] bg-[#f5f5f7] px-3 py-0.5 rounded-full">
                    {doc.category}
                  </span>
                  <span>Tahun {doc.year} • {doc.fileSize}</span>
                </div>

                <h3 className="text-[20px] font-semibold text-[#1d1d1f] leading-snug mb-2">
                  {doc.title}
                </h3>
                <p className="text-[14px] text-[#707070]">Penulis: {doc.author}</p>
              </div>

              <div className="mt-8 pt-6 border-t border-[#e8e8ed] flex items-center justify-between">
                <span className="text-[13px] text-[#707070]">Format Dokumen PDF</span>
                <a
                  href={doc.downloadUrl}
                  download
                  className="apple-link text-[15px]"
                >
                  <Download className="w-4 h-4 mr-1" />
                  <span>Unduh Dokumen</span>
                </a>
              </div>
            </motion.div>
          ))}
        </div>

      </div>
    </section>
  );
}
