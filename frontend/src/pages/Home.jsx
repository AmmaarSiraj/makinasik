// src/pages/Home.jsx
import React, { useEffect, useState } from 'react';
import axios from 'axios';
import { Link } from 'react-router-dom';
import { 
  FaClipboardList, 
  FaUsers, 
  FaArrowDown, 
  FaChartLine, 
  FaArrowRight 
} from 'react-icons/fa';

// Components
import PartDaftarKegiatan from '../components/PartDaftarKegiatan';
import PartPerencanaan from '../components/PartPerencanaan';
import PartRekapPerencanaan from '../components/PartRekapPerencanaan'; // Import Komponen Baru
import Footer from '../components/Footer';

const API_URL = import.meta.env.VITE_API_URL || 'http://127.0.0.1:8000';

const Home = () => {
  // State untuk menyimpan data statistik
  const [stats, setStats] = useState({
    kegiatan_aktif: 0,
    total_mitra: 0,
    mitra_aktif: 0
  });

  const scrollToContent = () => {
    const element = document.getElementById('konten-kegiatan');
    if (element) {
      element.scrollIntoView({ behavior: 'smooth' });
    }
  };

  // Fetch data & Hitung Statistik
  useEffect(() => {
    const fetchStats = async () => {
      try {
        const token = localStorage.getItem('token');
        const config = token ? { headers: { Authorization: `Bearer ${token}` } } : {};

        const [resPenugasan, resKelompok, resMitra] = await Promise.all([
          axios.get(`${API_URL}/api/penugasan`, config).catch(() => ({ data: { data: [] } })),
          axios.get(`${API_URL}/api/kelompok-penugasan`, config).catch(() => ({ data: { data: [] } })),
          axios.get(`${API_URL}/api/mitra`, config).catch(() => ({ data: { data: [] } }))
        ]);

        const penugasanData = resPenugasan.data.data || [];
        const kelompokData = resKelompok.data.data || [];
        const mitraData = resMitra.data.data || [];

        // Siapkan Filter Waktu (Bulan Ini)
        const today = new Date();
        const startOfMonth = new Date(today.getFullYear(), today.getMonth(), 1);
        const endOfMonth = new Date(today.getFullYear(), today.getMonth() + 1, 0);

        // Hitung Kegiatan/Penugasan Aktif Bulan Ini
        const activeTaskIdsMonth = new Set();
        penugasanData.forEach(task => {
            const start = new Date(task.tanggal_mulai);
            const end = new Date(task.tanggal_selesai);
            if (start <= endOfMonth && end >= startOfMonth) {
                activeTaskIdsMonth.add(task.id_penugasan);
            }
        });

        // Hitung Mitra Aktif Bulan Ini
        const activeMitraMonthSet = new Set();
        kelompokData.forEach(k => {
            if (activeTaskIdsMonth.has(k.id_penugasan)) {
                activeMitraMonthSet.add(k.id_mitra);
            }
        });

        setStats({
          kegiatan_aktif: activeTaskIdsMonth.size,
          mitra_aktif: activeMitraMonthSet.size,
          total_mitra: mitraData.length
        });

      } catch (error) {
        console.error("Gagal memuat statistik:", error);
      }
    };

    fetchStats();
  }, []);

  // --- REUSABLE HEADER COMPONENT ---
  const SectionHeader = ({ tag, titlePrimary, titleSecondary, linkTo }) => (
    <div className="flex flex-col md:flex-row justify-between items-end mb-8 gap-4 border-b border-gray-100 pb-4">
      <div>
        <span className="text-blue-600 font-bold tracking-widest text-xs uppercase mb-2 block relative pl-3 before:content-[''] before:absolute before:left-0 before:top-1/2 before:-translate-y-1/2 before:w-1.5 before:h-1.5 before:bg-blue-600 before:rounded-full">
            {tag}
        </span>
        <h2 className="text-2xl md:text-3xl font-extrabold text-slate-800 leading-tight">
          {titlePrimary} <br className="hidden md:block" />
          <span className="text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-indigo-600">
            {titleSecondary}
          </span>
        </h2>
      </div>
      
      {linkTo && (
        <div className="flex items-center gap-4 mb-1">
            <Link 
            to={linkTo} 
            className="group flex items-center gap-2 text-sm font-bold text-gray-500 hover:text-blue-600 transition-colors"
            >
            Lihat Selengkapnya
            <span className="p-2 bg-gray-50 rounded-full group-hover:bg-blue-50 group-hover:translate-x-1 transition-all">
                <FaArrowRight size={10} />
            </span>
            </Link>
        </div>
      )}
    </div>
  );

  return (
    <div className="relative w-full min-h-screen font-sans bg-gray-50 overflow-x-hidden">
      
      {/* Inject Styles */}
      <style>{`
        @keyframes float { 0% { transform: translateY(0px); } 50% { transform: translateY(-20px); } 100% { transform: translateY(0px); } }
        .animate-float { animation: float 6s ease-in-out infinite; }
      `}</style>

      {/* --- LAYER 1: BACKGROUND --- */}
      <div className="fixed inset-0 z-0">
        <img 
          src="https://images.unsplash.com/photo-1551288049-bebda4e38f71?q=80&w=2070&auto=format&fit=crop" 
          alt="Background" className="w-full h-full object-cover"
        />
        <div className="absolute inset-0 bg-gradient-to-b from-slate-900/90 via-slate-900/80 to-blue-900/80"></div>
        <div className="absolute inset-0 bg-[url('https://grainy-gradients.vercel.app/noise.svg')] opacity-20 mix-blend-soft-light"></div>
      </div>

      {/* --- LAYER 2: CONTENT --- */}
      <div className="relative z-10 flex flex-col min-h-screen">

        {/* --- HERO --- */}
        <div className="relative min-h-[90vh] flex flex-col justify-center items-center px-4 text-center">
          <div className="space-y-8 max-w-5xl mx-auto">
            <div className="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-white/10 border border-white/20 backdrop-blur-md shadow-lg mb-4 animate-fade-in-up">
              <img src="/src/assets/bpslogo.png" alt="BPS" className="h-5 w-auto opacity-90" />
              <span className="text-blue-100 text-xs font-bold tracking-widest uppercase">Badan Pusat Statistik</span>
            </div>
            <h1 className="text-6xl md:text-8xl font-black text-white tracking-tighter leading-none drop-shadow-2xl">
              MAKIN<span className="text-transparent bg-clip-text bg-gradient-to-r from-yellow-400 to-yellow-200">ASIK</span>
            </h1>
            <p className="text-lg md:text-2xl text-gray-200 max-w-2xl mx-auto font-light leading-relaxed drop-shadow-md">
              Manajemen Kinerja dan Administrasi <br className="hidden md:block"/> Mitra Statistik yang Modern & Terintegrasi.
            </p>
            <div className="pt-8 animate-float">
              <button onClick={scrollToContent} className="group relative inline-flex items-center gap-3 px-8 py-4 bg-gradient-to-r from-blue-600 to-blue-700 text-white text-sm font-bold uppercase tracking-widest rounded-full hover:from-blue-500 hover:to-blue-600 transition-all duration-300 shadow-[0_0_40px_rgba(37,99,235,0.4)] border border-white/20">
                Lihat Dashboard
                <span className="bg-white/20 p-1.5 rounded-full group-hover:translate-y-1 transition-transform duration-300"><FaArrowDown size={12} /></span>
              </button>
            </div>
          </div>
        </div>

        {/* --- STATS --- */}
        <div className="relative container mx-auto px-4 md:px-8 -mt-24 mb-12">
          <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div className="relative group bg-white/90 backdrop-blur-xl border border-white/50 p-6 rounded-3xl shadow-2xl hover:bg-white transition-all duration-500 hover:-translate-y-2">
              <div className="absolute top-0 right-0 p-4 opacity-10"><FaClipboardList className="text-6xl text-blue-600" /></div>
              <div className="flex flex-col">
                <span className="text-sm font-bold text-blue-600 uppercase tracking-wider mb-2">Kegiatan Bulan Ini</span>
                <div className="flex items-baseline gap-2"><span className="text-5xl font-black text-gray-800">{stats.kegiatan_aktif}</span><span className="text-gray-500 font-medium">Kegiatan</span></div>
                <div className="mt-4 h-1.5 w-full bg-blue-100 rounded-full overflow-hidden"><div className="h-full bg-blue-600 w-2/3 rounded-full animate-pulse"></div></div>
              </div>
            </div>
            <div className="relative group bg-white/90 backdrop-blur-xl border border-white/50 p-6 rounded-3xl shadow-2xl hover:bg-white transition-all duration-500 hover:-translate-y-2">
              <div className="absolute top-0 right-0 p-4 opacity-10"><FaUsers className="text-6xl text-yellow-500" /></div>
              <div className="flex flex-col">
                <span className="text-sm font-bold text-yellow-600 uppercase tracking-wider mb-2">Total Mitra</span>
                <div className="flex items-baseline gap-2"><span className="text-5xl font-black text-gray-800">{stats.total_mitra}</span><span className="text-gray-500 font-medium">Orang</span></div>
                <div className="mt-4 h-1.5 w-full bg-yellow-100 rounded-full overflow-hidden"><div className="h-full bg-yellow-500 w-full rounded-full"></div></div>
              </div>
            </div>
            <div className="relative group bg-white/90 backdrop-blur-xl border border-white/50 p-6 rounded-3xl shadow-2xl hover:bg-white transition-all duration-500 hover:-translate-y-2">
              <div className="absolute top-0 right-0 p-4 opacity-10"><FaChartLine className="text-6xl text-emerald-600" /></div>
              <div className="flex flex-col">
                <span className="text-sm font-bold text-emerald-600 uppercase tracking-wider mb-2">Mitra Aktif</span>
                <div className="flex items-baseline gap-2"><span className="text-5xl font-black text-gray-800">{stats.mitra_aktif}</span><span className="text-gray-500 font-medium">Orang</span></div>
                <div className="mt-4 h-1.5 w-full bg-emerald-100 rounded-full overflow-hidden"><div className="h-full bg-emerald-500 w-1/2 rounded-full animate-pulse"></div></div>
              </div>
            </div>
          </div>
        </div>

        {/* --- MAIN CONTENT (STACKED LAYOUT) --- */}
        <div id="konten-kegiatan" className="flex-grow bg-white relative pt-16 pb-24 rounded-t-0 shadow-[0_-20px_60px_rgba(0,0,0,0.1)] -mt-10 z-20">
          <div className="container mx-auto px-4 md:px-8 space-y-16">
            
            {/* PART 1: DAFTAR KEGIATAN */}
            <section>
                <SectionHeader 
                    tag="Monitoring"
                    titlePrimary="Daftar Survei &"
                    titleSecondary="Kegiatan Terbaru"
                    linkTo="/manajemen-kegiatan"
                />
                <div className="bg-gray-50 rounded-3xl border border-gray-100 p-2 md:p-6 shadow-inner">
                   <PartDaftarKegiatan />
                </div>
            </section>

            {/* PART 2: PERENCANAAN */}
            <section>
                <SectionHeader 
                    tag="Planning"
                    titlePrimary="Daftar Rencana"
                    titleSecondary="Kegiatan Akan Datang"
                    linkTo="/perencanaan"
                />
                {/* Tampilan Grid untuk card perencanaan */}
                <div className="bg-white rounded-3xl border border-gray-100 p-4 md:p-8 shadow-lg shadow-gray-100/50">
                   <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <PartPerencanaan /> 
                        {/* Note: Pastikan PartPerencanaan me-render multiple card/div atau sesuaikan styling di dalamnya agar responsive */}
                   </div>
                </div>
            </section>

            {/* PART 3: REKAPITULASI */}
            <section>
                <SectionHeader 
                    tag="Evaluation"
                    titlePrimary="Rekapitulasi &"
                    titleSecondary="Realisasi Anggaran"
                    linkTo="/rekap-perencanaan"
                />
                <div className="bg-gradient-to-br from-gray-50 to-white rounded-3xl border border-gray-100 p-4 md:p-8 shadow-sm">
                   <PartRekapPerencanaan />
                </div>
            </section>

          </div>
        </div>

        <div className="bg-white">
            <Footer />
        </div>

      </div>
    </div>
  );
};

export default Home;