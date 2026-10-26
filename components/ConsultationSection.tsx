"use client";

import { useState } from "react";
import { motion } from "framer-motion";
import { SERVICES } from "@/data/ncsData";
import { Check, ChevronRight } from "lucide-react";

export default function ConsultationSection() {
  const [submitted, setSubmitted] = useState(false);
  const [formData, setFormData] = useState({
    name: "",
    email: "",
    phone: "",
    category: "Penetration Testing",
    subject: "",
    message: "",
  });

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    setSubmitted(true);
  };

  return (
    <section id="services" className="bg-white py-24 sm:py-32">
      <div className="max-w-[1200px] mx-auto px-4 sm:px-8">
        
        {/* Section Header */}
        <div className="max-w-2xl mb-16">
          <span className="text-[13px] font-medium text-[#707070] uppercase tracking-wider block mb-2">
            Layanan &amp; Konsultasi
          </span>
          <h2 className="text-[32px] sm:text-[42px] font-bold text-[#1d1d1f] tracking-tight leading-[1.1] mb-3">
            Layanan Pengujian &amp; Konsultasi Teknis
          </h2>
          <p className="text-[16px] text-[#707070] leading-[1.5]">
            Layanan audit keamanan siber, pengujian penetrasi kerentanan (pentest), dan pelatihan instruktur untuk institusi Anda.
          </p>
        </div>

        {/* 2-Column: Services Left, Form Right */}
        <div className="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
          
          {/* Services Cards */}
          <div className="lg:col-span-6 space-y-5">
            {SERVICES.map((srv, idx) => (
              <motion.div
                key={srv.id}
                initial={{ opacity: 0, y: 20 }}
                whileInView={{ opacity: 1, y: 0 }}
                viewport={{ once: true, margin: "-50px" }}
                transition={{ duration: 0.5, delay: idx * 0.08 }}
                className="apple-card bg-[#f5f5f7] p-8"
              >
                <span className="text-[12px] font-semibold text-[#0066cc] uppercase tracking-wider block mb-1">
                  {srv.badge}
                </span>
                <h3 className="text-[20px] font-semibold text-[#1d1d1f] mb-2">
                  {srv.title}
                </h3>
                <p className="text-[15px] text-[#707070] leading-[1.47] mb-4">
                  {srv.description}
                </p>
                <ul className="space-y-2 pt-4 border-t border-[#d6d6d6]/60">
                  {srv.features.map((feat, fIdx) => (
                    <li key={fIdx} className="flex items-start gap-2.5 text-[14px] text-[#1d1d1f]">
                      <div className="w-4 h-4 rounded-full bg-[#0071e3]/10 text-[#0071e3] flex items-center justify-center flex-shrink-0 mt-0.5">
                        <Check className="w-2.5 h-2.5 stroke-[2.5]" />
                      </div>
                      <span>{feat}</span>
                    </li>
                  ))}
                </ul>
              </motion.div>
            ))}
          </div>

          {/* Form */}
          <div className="lg:col-span-6 sticky top-20">
            <motion.div
              initial={{ opacity: 0, y: 20 }}
              whileInView={{ opacity: 1, y: 0 }}
              viewport={{ once: true, margin: "-50px" }}
              transition={{ duration: 0.6 }}
              className="apple-card bg-[#f5f5f7] p-8 sm:p-10"
            >
              <h3 className="text-[24px] font-semibold text-[#1d1d1f] mb-2">
                Formulir Permintaan Layanan
              </h3>
              <p className="text-[15px] text-[#707070] mb-8 leading-[1.4]">
                Jelaskan kebutuhan pengujian atau konsultasi Anda. Tim teknis Lab NCS akan menindaklanjuti dalam 1x24 jam kerja.
              </p>

              {submitted ? (
                <div className="p-8 bg-white rounded-[20px] text-center">
                  <div className="w-12 h-12 rounded-full bg-[#0071e3]/10 text-[#0071e3] flex items-center justify-center mx-auto mb-3">
                    <Check className="w-6 h-6 stroke-[2.5]" />
                  </div>
                  <h4 className="text-[20px] font-semibold text-[#1d1d1f] mb-2">
                    Permintaan Terkirim
                  </h4>
                  <p className="text-[14px] text-[#707070] leading-[1.47] mb-6">
                    Terima kasih. Kami telah menerima permohonan Anda dan akan menghubungi melalui email atau nomor telepon yang tertera.
                  </p>
                  <button
                    onClick={() => setSubmitted(false)}
                    className="apple-link text-[14px]"
                  >
                    Kirim permohonan lain <ChevronRight className="w-3.5 h-3.5" />
                  </button>
                </div>
              ) : (
                <form onSubmit={handleSubmit} className="space-y-4">
                  <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                      <label className="block text-[13px] text-[#1d1d1f] mb-1.5 font-medium">
                        Nama Lengkap
                      </label>
                      <input
                        type="text"
                        required
                        placeholder="Nama dan gelar"
                        value={formData.name}
                        onChange={(e) => setFormData({ ...formData, name: e.target.value })}
                        className="w-full text-[14px] px-4 py-3 rounded-[12px] bg-white border border-[#d6d6d6] focus:border-[#0071e3] outline-none text-[#1d1d1f]"
                      />
                    </div>
                    <div>
                      <label className="block text-[13px] text-[#1d1d1f] mb-1.5 font-medium">
                        Email Institusi
                      </label>
                      <input
                        type="email"
                        required
                        placeholder="nama@instansi.ac.id"
                        value={formData.email}
                        onChange={(e) => setFormData({ ...formData, email: e.target.value })}
                        className="w-full text-[14px] px-4 py-3 rounded-[12px] bg-white border border-[#d6d6d6] focus:border-[#0071e3] outline-none text-[#1d1d1f]"
                      />
                    </div>
                  </div>

                  <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                      <label className="block text-[13px] text-[#1d1d1f] mb-1.5 font-medium">
                        Nomor Kontak
                      </label>
                      <input
                        type="tel"
                        placeholder="0812-xxxx-xxxx"
                        value={formData.phone}
                        onChange={(e) => setFormData({ ...formData, phone: e.target.value })}
                        className="w-full text-[14px] px-4 py-3 rounded-[12px] bg-white border border-[#d6d6d6] focus:border-[#0071e3] outline-none text-[#1d1d1f]"
                      />
                    </div>
                    <div>
                      <label className="block text-[13px] text-[#1d1d1f] mb-1.5 font-medium">
                        Kategori Layanan
                      </label>
                      <select
                        value={formData.category}
                        onChange={(e) => setFormData({ ...formData, category: e.target.value })}
                        className="w-full text-[14px] px-4 py-3 rounded-[12px] bg-white border border-[#d6d6d6] focus:border-[#0071e3] outline-none text-[#1d1d1f]"
                      >
                        <option value="Penetration Testing">Penetration Testing &amp; VA</option>
                        <option value="Network Audit">Audit Arsitektur Jaringan</option>
                        <option value="Digital Forensics">Digital Forensics &amp; DFIR</option>
                        <option value="Training">Pelatihan Sertifikasi CCNA</option>
                        <option value="Kemitraan">Kemitraan Riset / Umum</option>
                      </select>
                    </div>
                  </div>

                  <div>
                    <label className="block text-[13px] text-[#1d1d1f] mb-1.5 font-medium">
                      Subjek Permintaan
                    </label>
                    <input
                      type="text"
                      required
                      placeholder="Contoh: Audit Keamanan Sistem Informasi"
                      value={formData.subject}
                      onChange={(e) => setFormData({ ...formData, subject: e.target.value })}
                      className="w-full text-[14px] px-4 py-3 rounded-[12px] bg-white border border-[#d6d6d6] focus:border-[#0071e3] outline-none text-[#1d1d1f]"
                    />
                  </div>

                  <div>
                    <label className="block text-[13px] text-[#1d1d1f] mb-1.5 font-medium">
                      Deskripsi Kebutuhan
                    </label>
                    <textarea
                      required
                      rows={4}
                      placeholder="Jelaskan ruang lingkup, durasi pelaksanaan, atau kebutuhan spesifik..."
                      value={formData.message}
                      onChange={(e) => setFormData({ ...formData, message: e.target.value })}
                      className="w-full text-[14px] px-4 py-3 rounded-[12px] bg-white border border-[#d6d6d6] focus:border-[#0071e3] outline-none text-[#1d1d1f] resize-none"
                    />
                  </div>

                  <button
                    type="submit"
                    className="apple-btn-primary w-full !py-3.5 !text-[15px] mt-2"
                  >
                    Kirim Permintaan Layanan
                  </button>
                </form>
              )}
            </motion.div>
          </div>

        </div>

      </div>
    </section>
  );
}
