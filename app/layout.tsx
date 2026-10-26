import type { Metadata, Viewport } from "next";
import "./globals.css";

export const viewport: Viewport = {
  themeColor: "#ffffff",
  width: "device-width",
  initialScale: 1,
  maximumScale: 5,
};

export const metadata: Metadata = {
  metadataBase: new URL("https://ncs.polinema.ac.id"),
  title: "Lab Network & Cyber Security (NCS) - JTI Polinema",
  description:
    "Portal Resmi Laboratorium Network and Cyber Security (NCS) Jurusan Teknologi Informasi Politeknik Negeri Malang. Pusat keunggulan riset arsitektur jaringan, ethical hacking, digital forensics, dan infrastruktur cloud.",
  keywords: [
    "Lab NCS",
    "Network and Cyber Security",
    "JTI Polinema",
    "Politeknik Negeri Malang",
    "Cyber Security Malang",
    "Penetration Testing",
    "Digital Forensics",
    "Cisco Academy Polinema",
    "Laboratorium Komputer",
  ],
  authors: [{ name: "Lab NCS JTI Polinema" }],
  creator: "Laboratorium Network and Cyber Security",
  publisher: "Jurusan Teknologi Informasi Politeknik Negeri Malang",
  robots: {
    index: true,
    follow: true,
    googleBot: {
      index: true,
      follow: true,
      "max-video-preview": -1,
      "max-image-preview": "large",
      "max-snippet": -1,
    },
  },
  openGraph: {
    type: "website",
    locale: "id_ID",
    url: "https://ncs.polinema.ac.id",
    siteName: "Laboratorium Network & Cyber Security - JTI Polinema",
    title: "Lab Network & Cyber Security (NCS) - JTI Polinema",
    description:
      "Pusat riset, praktikum, dan layanan konsultatif keamanan siber dan infrastruktur jaringan di Jurusan Teknologi Informasi Polinema.",
    images: [
      {
        url: "/images/LABNCS.jpg",
        width: 1200,
        height: 630,
        alt: "Laboratorium Network and Cyber Security JTI Polinema",
      },
    ],
  },
  twitter: {
    card: "summary_large_image",
    title: "Lab Network & Cyber Security (NCS) - JTI Polinema",
    description:
      "Pusat riset, praktikum, dan layanan konsultatif keamanan siber dan infrastruktur jaringan di Jurusan Teknologi Informasi Polinema.",
    images: ["/images/LABNCS.jpg"],
  },
  icons: {
    icon: "/images/LOGOC.png",
    shortcut: "/images/LOGOC.png",
    apple: "/images/LOGOC.png",
  },
};

export default function RootLayout({
  children,
}: Readonly<{
  children: React.ReactNode;
}>) {
  return (
    <html lang="id" className="scroll-smooth">
      <body className="font-sans antialiased bg-white text-[#08304c]">
        {children}
      </body>
    </html>
  );
}
