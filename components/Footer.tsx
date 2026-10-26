import Image from "next/image";
import Link from "next/link";
import { LAB_PROFILE, EXTERNAL_LINKS } from "@/data/ncsData";
import { ChevronRight } from "lucide-react";

export default function Footer() {
  return (
    <footer className="bg-[#f5f5f7] border-t border-[#d6d6d6] text-[#707070] pt-12 pb-16 text-[12px] leading-[1.33]">
      <div className="max-w-[1200px] mx-auto px-4 sm:px-8">
        
        {/* Footnote statement */}
        <div className="pb-8 mb-8 border-b border-[#d6d6d6] text-[#707070] space-y-2">
          <p>
            1. Laboratorium Network and Cyber Security (NCS) merupakan bagian terintegrasi dari Jurusan Teknologi Informasi, Politeknik Negeri Malang.
          </p>
          <p>
            2. Program sertifikasi CCNA dan Cyber Range dilaksanakan bekerja sama dengan Cisco Networking Academy dan kurikulum standar industri.
          </p>
        </div>

        {/* Directory columns */}
        <div className="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-8 mb-10">
          
          {/* Col 1: Profil Lab */}
          <div>
            <h4 className="text-[12px] font-semibold text-[#1d1d1f] mb-3">
              Laboratorium
            </h4>
            <ul className="space-y-2">
              <li><Link href="#hero" className="hover:text-[#1d1d1f] transition-colors">Beranda</Link></li>
              <li><Link href="#about" className="hover:text-[#1d1d1f] transition-colors">Sejarah &amp; Visi</Link></li>
              <li><Link href="#team" className="hover:text-[#1d1d1f] transition-colors">Anggota Peneliti</Link></li>
              <li><Link href="#facilities" className="hover:text-[#1d1d1f] transition-colors">Sarana &amp; Fasilitas</Link></li>
              <li><Link href="#gallery" className="hover:text-[#1d1d1f] transition-colors">Galeri Dokumentasi</Link></li>
            </ul>
          </div>

          {/* Col 2: Layanan */}
          <div>
            <h4 className="text-[12px] font-semibold text-[#1d1d1f] mb-3">
              Layanan &amp; Uji
            </h4>
            <ul className="space-y-2">
              <li><Link href="#services" className="hover:text-[#1d1d1f] transition-colors">Penetration Testing</Link></li>
              <li><Link href="#services" className="hover:text-[#1d1d1f] transition-colors">Audit Jaringan</Link></li>
              <li><Link href="#services" className="hover:text-[#1d1d1f] transition-colors">Digital Forensics</Link></li>
              <li><Link href="#services" className="hover:text-[#1d1d1f] transition-colors">Pelatihan CCNA</Link></li>
            </ul>
          </div>

          {/* Col 3: Repositori */}
          <div>
            <h4 className="text-[12px] font-semibold text-[#1d1d1f] mb-3">
              Publikasi
            </h4>
            <ul className="space-y-2">
              <li><Link href="#archives" className="hover:text-[#1d1d1f] transition-colors">Modul Praktikum</Link></li>
              <li><Link href="#archives" className="hover:text-[#1d1d1f] transition-colors">Laporan Riset</Link></li>
              <li><Link href="#agenda" className="hover:text-[#1d1d1f] transition-colors">Jadwal Agenda</Link></li>
            </ul>
          </div>

          {/* Col 4: Tautan Eksternal */}
          <div>
            <h4 className="text-[12px] font-semibold text-[#1d1d1f] mb-3">
              Tautan Institusi
            </h4>
            <ul className="space-y-2">
              {EXTERNAL_LINKS.map((link) => (
                <li key={link.name}>
                  <a
                    href={link.url}
                    target="_blank"
                    rel="noopener noreferrer"
                    className="hover:text-[#1d1d1f] transition-colors"
                  >
                    {link.name}
                  </a>
                </li>
              ))}
            </ul>
          </div>

          {/* Col 5: Kontak */}
          <div className="col-span-2 lg:col-span-1">
            <h4 className="text-[12px] font-semibold text-[#1d1d1f] mb-3">
              Alamat
            </h4>
            <p className="text-[#707070] leading-relaxed mb-2">
              {LAB_PROFILE.contact.address}
            </p>
            <p className="text-[#1d1d1f] font-medium">
              {LAB_PROFILE.contact.email}
            </p>
          </div>

        </div>

        {/* Bottom copyright & legal */}
        <div className="pt-6 border-t border-[#d6d6d6] flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 text-[11px] text-[#707070]">
          <p>
            Hak Cipta &copy; {new Date().getFullYear()} Laboratorium Network &amp; Cyber Security, Politeknik Negeri Malang. Seluruh hak cipta dilindungi undang-undang.
          </p>
          <div className="flex items-center gap-4">
            <span className="hover:underline cursor-pointer">Kebijakan Privasi</span>
            <span>•</span>
            <span className="hover:underline cursor-pointer">Ketentuan Penggunaan</span>
            <span>•</span>
            <span className="hover:underline cursor-pointer">Peta Situs</span>
          </div>
        </div>

      </div>
    </footer>
  );
}
