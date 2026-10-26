import Navbar from "@/components/Navbar";
import Hero from "@/components/Hero";
import StatsSection from "@/components/StatsSection";
import AboutLab from "@/components/AboutLab";
import Facilities from "@/components/Facilities";
import TeamMembers from "@/components/TeamMembers";
import GallerySection from "@/components/GallerySection";
import AgendaSection from "@/components/AgendaSection";
import ArchivesSection from "@/components/ArchivesSection";
import ConsultationSection from "@/components/ConsultationSection";
import Footer from "@/components/Footer";

export default function Home() {
  return (
    <div className="min-h-screen flex flex-col bg-white">
      {/* Floating Pill Navigation */}
      <Navbar />

      {/* Main Content Sections */}
      <main className="flex-1">
        {/* Hero Section with Polaroid Collage & Rainbow Accent */}
        <Hero />

        {/* Laboratory Verified Metrics */}
        <StatsSection />

        {/* Identity, History, Vision & Mission */}
        <AboutLab />

        {/* Infrastructure & Equipment Pods */}
        <Facilities />

        {/* Researchers & Instructors */}
        <TeamMembers />

        {/* Polaroid Scrapbook Gallery & Lightbox */}
        <GallerySection />

        {/* Schedule & Upcoming Drills */}
        <AgendaSection />

        {/* Digital Repositories & Research Papers */}
        <ArchivesSection />

        {/* Consultative Services & Engagement Form */}
        <ConsultationSection />
      </main>

      {/* Laboratory Minimalist Footer */}
      <Footer />
    </div>
  );
}

