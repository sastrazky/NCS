"use client";

import { useState, useEffect } from "react";
import Image from "next/image";
import Link from "next/link";
import { EXTERNAL_LINKS } from "@/data/ncsData";
import { Menu, X, ChevronRight } from "lucide-react";

export default function Navbar() {
  const [scrolled, setScrolled] = useState(false);
  const [mobileMenuOpen, setMobileMenuOpen] = useState(false);

  useEffect(() => {
    const handleScroll = () => {
      setScrolled(window.scrollY > 10);
    };
    window.addEventListener("scroll", handleScroll);
    return () => window.removeEventListener("scroll", handleScroll);
  }, []);

  const navLinks = [
    { name: "Beranda", href: "#hero" },
    { name: "Profil", href: "#about" },
    { name: "Fasilitas", href: "#facilities" },
    { name: "Tim Peneliti", href: "#team" },
    { name: "Galeri", href: "#gallery" },
    { name: "Agenda", href: "#agenda" },
    { name: "Arsip", href: "#archives" },
    { name: "Layanan", href: "#services" },
  ];

  return (
    <>
      {/* Top Banner Ribbon without 'BARU' */}
      <div className="bg-[#f5f5f7] border-b border-[#d6d6d6]/60 text-center py-2 px-4 text-[12px] text-[#1d1d1f] font-normal z-50 relative">
        <div className="max-w-[1200px] mx-auto flex items-center justify-center gap-2">
          <span>Laboratorium Riset Keamanan Siber &amp; Arsitektur Jaringan — JTI Polinema.</span>
          <a href="#services" className="text-[#0066cc] hover:underline inline-flex items-center gap-0.5 font-medium">
            Konsultasi Layanan <ChevronRight className="w-3 h-3" />
          </a>
        </div>
      </div>

      {/* Global Navigation Bar (44px Apple style, frosted glass) */}
      <header
        className={`sticky top-0 left-0 right-0 z-40 transition-colors duration-300 ${
          scrolled ? "bg-[#fafafc]/80 backdrop-blur-xl border-b border-[#d6d6d6]/60" : "bg-white/80 backdrop-blur-xl border-b border-transparent"
        }`}
      >
        <div className="max-w-[1200px] mx-auto px-4 sm:px-8 h-[44px] flex items-center justify-between">
          
          {/* Logo / Brand */}
          <Link href="#hero" className="flex items-center gap-2.5 text-[#1d1d1f] hover:opacity-80 transition-opacity">
            <div className="relative w-5 h-5 flex-shrink-0">
              <Image
                src="/images/LOGOC.png"
                alt="NCS"
                width={20}
                height={20}
                className="object-contain"
                priority
              />
            </div>
            <span className="font-semibold text-[14px] tracking-tight text-[#1d1d1f]">
              Lab NCS Polinema
            </span>
          </Link>

          {/* Desktop Nav Items */}
          <nav className="hidden md:flex items-center gap-6">
            {navLinks.map((item) => (
              <Link
                key={item.name}
                href={item.href}
                className="text-[12px] text-[#1d1d1f] hover:text-[#0066cc] font-normal transition-colors"
              >
                {item.name}
              </Link>
            ))}
          </nav>

          {/* Right Action: Apple Blue CTA */}
          <div className="hidden sm:flex items-center gap-3">
            <a
              href="#services"
              className="apple-btn-primary !py-1.5 !px-3.5 !text-[12px]"
            >
              Layanan
            </a>
          </div>

          {/* Mobile hamburger */}
          <button
            onClick={() => setMobileMenuOpen(!mobileMenuOpen)}
            className="md:hidden p-1.5 text-[#1d1d1f]"
            aria-label="Toggle navigation"
          >
            {mobileMenuOpen ? <X className="w-5 h-5" /> : <Menu className="w-5 h-5" />}
          </button>
        </div>

        {/* Mobile menu sheet */}
        {mobileMenuOpen && (
          <div className="md:hidden bg-[#fafafc] border-b border-[#d6d6d6] px-6 py-5 animate-in fade-in">
            <div className="flex flex-col gap-3">
              {navLinks.map((item) => (
                <Link
                  key={item.name}
                  href={item.href}
                  onClick={() => setMobileMenuOpen(false)}
                  className="text-[17px] font-semibold text-[#1d1d1f] py-1 border-b border-[#e8e8ed]"
                >
                  {item.name}
                </Link>
              ))}
              <div className="pt-3">
                <a
                  href="#services"
                  onClick={() => setMobileMenuOpen(false)}
                  className="apple-btn-primary w-full text-center"
                >
                  Permohonan Layanan Lab
                </a>
              </div>
            </div>
          </div>
        )}
      </header>
    </>
  );
}
