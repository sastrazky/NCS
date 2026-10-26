"use client";

import { useState } from "react";
import Image from "next/image";
import { motion, AnimatePresence } from "framer-motion";
import { GALLERY_ITEMS, GalleryItem } from "@/data/ncsData";
import { X, ChevronRight, MapPin } from "lucide-react";

export default function GallerySection() {
  const [activeCategory, setActiveCategory] = useState<string>("Semua");
  const [selectedItem, setSelectedItem] = useState<GalleryItem | null>(null);

  const categories = ["Semua", "Workshop", "Riset", "Praktikum", "Kompetisi"];

  const filteredItems =
    activeCategory === "Semua"
      ? GALLERY_ITEMS
      : GALLERY_ITEMS.filter((item) => item.category === activeCategory);

  return (
    <section id="gallery" className="bg-[#f5f5f7] py-24 sm:py-32">
      <div className="max-w-[1200px] mx-auto px-4 sm:px-8">
        
        {/* Header */}
        <div className="flex flex-col md:flex-row md:items-end justify-between mb-12 gap-6">
          <div className="max-w-xl">
            <span className="text-[13px] font-medium text-[#707070] uppercase tracking-wider block mb-2">
              Dokumentasi &amp; Galeri
            </span>
            <h2 className="text-[32px] sm:text-[42px] font-bold text-[#1d1d1f] tracking-tight leading-[1.1]">
              Dokumentasi Kegiatan Lab
            </h2>
            <p className="text-[16px] text-[#707070] mt-2 leading-[1.5]">
              Dokumentasi kegiatan praktikum mahasiswa, pelatihan sertifikasi, dan kompetisi keamanan siber.
            </p>
          </div>

          {/* Filter Tabs */}
          <div className="flex flex-wrap items-center gap-2">
            {categories.map((cat) => (
              <button
                key={cat}
                onClick={() => setActiveCategory(cat)}
                className={`text-[13px] font-normal px-4 py-1.5 rounded-full transition-all cursor-pointer ${
                  activeCategory === cat
                    ? "bg-[#1d1d1f] text-white"
                    : "bg-[#e8e8ed] text-[#1d1d1f] hover:bg-[#dedee3]"
                }`}
              >
                {cat}
              </button>
            ))}
          </div>
        </div>

        {/* Gallery Grid */}
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 sm:gap-8">
          {filteredItems.map((item, idx) => (
            <motion.div
              key={item.id}
              layout
              initial={{ opacity: 0, scale: 0.98 }}
              animate={{ opacity: 1, scale: 1 }}
              exit={{ opacity: 0, scale: 0.98 }}
              transition={{ duration: 0.4, delay: idx * 0.05 }}
              onClick={() => setSelectedItem(item)}
              className="apple-card bg-white cursor-pointer flex flex-col justify-between group"
            >
              <div>
                <div className="relative h-60 w-full overflow-hidden bg-[#e8e8ed]">
                  <Image
                    src={item.image}
                    alt={item.title}
                    fill
                    sizes="(max-width: 768px) 100vw, (max-width: 1200px) 50vw, 380px"
                    className="object-cover group-hover:scale-102 transition-transform duration-500"
                  />
                  <div className="absolute top-4 left-4 bg-white/90 backdrop-blur-md px-3 py-1 rounded-full text-[11px] font-medium text-[#1d1d1f]">
                    {item.category}
                  </div>
                </div>

                <div className="p-7">
                  <div className="flex items-center justify-between text-[12px] text-[#707070] mb-1">
                    <span>{item.date}</span>
                    <span className="flex items-center gap-1">
                      <MapPin className="w-3 h-3 text-[#1d1d1f]" />
                      {item.location}
                    </span>
                  </div>
                  <h3 className="text-[18px] font-semibold text-[#1d1d1f] leading-snug mb-2">
                    {item.title}
                  </h3>
                  <p className="text-[14px] text-[#707070] leading-[1.47] line-clamp-2">
                    {item.description}
                  </p>
                </div>
              </div>

              <div className="p-7 pt-0">
                <span className="apple-link text-[14px]">
                  Buka pratinjau <ChevronRight className="w-3.5 h-3.5" />
                </span>
              </div>
            </motion.div>
          ))}
        </div>

      </div>

      {/* Modal Lightbox */}
      <AnimatePresence>
        {selectedItem && (
          <div
            className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-md"
            onClick={() => setSelectedItem(null)}
          >
            <motion.div
              initial={{ opacity: 0, scale: 0.95 }}
              animate={{ opacity: 1, scale: 1 }}
              exit={{ opacity: 0, scale: 0.95 }}
              onClick={(e) => e.stopPropagation()}
              className="bg-white rounded-[28px] max-w-2xl w-full overflow-hidden p-6 sm:p-8 relative shadow-2xl"
            >
              <button
                onClick={() => setSelectedItem(null)}
                className="absolute top-5 right-5 z-10 p-2 bg-[#f5f5f7] hover:bg-[#e8e8ed] rounded-full text-[#1d1d1f] transition-colors"
              >
                <X className="w-5 h-5" />
              </button>

              <div className="relative h-72 sm:h-96 w-full rounded-[20px] overflow-hidden bg-[#e8e8ed] mb-6">
                <Image
                  src={selectedItem.image}
                  alt={selectedItem.title}
                  fill
                  sizes="700px"
                  className="object-cover"
                />
              </div>

              <div className="flex items-center gap-2 mb-2">
                <span className="text-[12px] font-medium px-3 py-0.5 rounded-full bg-[#f5f5f7] text-[#1d1d1f]">
                  {selectedItem.category}
                </span>
                <span className="text-[13px] text-[#707070]">
                  {selectedItem.date} • {selectedItem.location}
                </span>
              </div>

              <h3 className="text-[24px] font-bold text-[#1d1d1f] mb-3">
                {selectedItem.title}
              </h3>
              <p className="text-[15px] text-[#707070] leading-[1.5]">
                {selectedItem.description}
              </p>
            </motion.div>
          </div>
        )}
      </AnimatePresence>
    </section>
  );
}
