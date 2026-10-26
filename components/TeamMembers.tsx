"use client";

import Image from "next/image";
import { motion } from "framer-motion";
import { TEAM_MEMBERS } from "@/data/ncsData";
import { Mail } from "lucide-react";

export default function TeamMembers() {
  return (
    <section id="team" className="bg-white py-24 sm:py-32">
      <div className="max-w-[1200px] mx-auto px-4 sm:px-8">
        
        {/* Section Header */}
        <div className="max-w-2xl mb-16">
          <span className="text-[13px] font-medium text-[#707070] uppercase tracking-wider block mb-2">
            Staf Pengajar &amp; Laboran
          </span>
          <h2 className="text-[32px] sm:text-[42px] font-bold text-[#1d1d1f] tracking-tight leading-[1.1] mb-3">
            Dosen Pembina &amp; Anggota Tim
          </h2>
          <p className="text-[16px] text-[#707070] leading-[1.5]">
            Dosen pengampu mata kuliah jaringan, instruktur sertifikasi Cisco, dan laboran di Lab Network and Cyber Security.
          </p>
        </div>

        {/* Team Cards Grid */}
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 sm:gap-8">
          {TEAM_MEMBERS.map((member, idx) => (
            <motion.div
              key={member.id}
              initial={{ opacity: 0, y: 25 }}
              whileInView={{ opacity: 1, y: 0 }}
              viewport={{ once: true, margin: "-50px" }}
              transition={{ duration: 0.5, delay: idx * 0.1 }}
              className="apple-card bg-[#f5f5f7] flex flex-col justify-between"
            >
              <div>
                {/* Photo */}
                <div className="relative h-72 w-full bg-[#e8e8ed] overflow-hidden">
                  <Image
                    src={member.photo}
                    alt={member.name}
                    fill
                    sizes="(max-width: 768px) 100vw, (max-width: 1200px) 50vw, 380px"
                    className="object-cover"
                  />
                  <div className="absolute top-4 left-4 bg-white/90 backdrop-blur-md px-3 py-1 rounded-full text-[11px] font-medium text-[#1d1d1f]">
                    {member.division}
                  </div>
                </div>

                {/* Info */}
                <div className="p-7">
                  <span className="text-[13px] font-medium text-[#707070] block mb-1">
                    {member.role}
                  </span>
                  <h3 className="text-[19px] font-semibold text-[#1d1d1f] leading-snug mb-1">
                    {member.name}
                  </h3>
                  {member.nip && (
                    <span className="text-[12px] text-[#707070] block mb-3 font-normal">
                      NIP {member.nip}
                    </span>
                  )}

                  {/* Specializations as clean pills */}
                  <div className="flex flex-wrap gap-1.5 mb-2">
                    {member.specialization.map((spec) => (
                      <span
                        key={spec}
                        className="text-[12px] bg-white text-[#1d1d1f] px-3 py-1 rounded-full font-normal"
                      >
                        {spec}
                      </span>
                    ))}
                  </div>
                </div>
              </div>

              {/* Card Footer */}
              <div className="p-7 pt-0">
                {member.email ? (
                  <a
                    href={`mailto:${member.email}`}
                    className="apple-link text-[14px]"
                  >
                    <Mail className="w-4 h-4 mr-1" />
                    <span>{member.email}</span>
                  </a>
                ) : (
                  <span className="text-[13px] text-[#707070]">Tim Asisten Mahasiswa</span>
                )}
              </div>
            </motion.div>
          ))}
        </div>

      </div>
    </section>
  );
}
