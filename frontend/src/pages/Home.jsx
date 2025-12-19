// src/pages/Home.jsx
import React, { useEffect, useState } from 'react';
import axios from 'axios';
import { FaClipboardList, FaUsers, FaUserCheck, FaArrowDown, FaChartLine } from 'react-icons/fa';
import PartDaftarKegiatan from '../components/PartDaftarKegiatan';
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
        const currentYear = today.getFullYear();
        const currentMonth = today.getMonth(); 
        const startOfMonth = new Date(currentYear, currentMonth, 1);
        const endOfMonth = new Date(currentYear, currentMonth + 1, 0);

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

  return (
    <div className="relative w-full min-h-screen font-sans bg-gray-50 overflow-x-hidden">
      
      {/* Inject Custom Styles for Animations */}
      <style>{`
        @keyframes float {
          0% { transform: translateY(0px); }
          50% { transform: translateY(-20px); }
          100% { transform: translateY(0px); }
        }
        .animate-float { animation: float 6s ease-in-out infinite; }
      `}</style>

      {/* --- LAYER 1: FIXED BACKGROUND IMAGE --- */}
      <div className="fixed inset-0 z-0">
        <img 
          src="https://images.unsplash.com/photo-1551288049-bebda4e38f71?q=80&w=2070&auto=format&fit=crop" 
          alt="Background MAKINASIK"
          className="w-full h-full object-cover"
        />
        {/* Modern Dark Overlay with Blue Tint */}
        <div className="absolute inset-0 bg-gradient-to-b from-slate-900/90 via-slate-900/80 to-blue-900/80"></div>
        {/* Texture Overlay */}
        <div className="absolute inset-0 bg-[url('https://grainy-gradients.vercel.app/noise.svg')] opacity-20 mix-blend-soft-light"></div>
      </div>

      {/* --- LAYER 2: CONTENT WRAPPER --- */}
      <div className="relative z-10 flex flex-col min-h-screen">

        {/* --- HERO SECTION --- */}
        <div className="relative min-h-[90vh] flex flex-col justify-center items-center px-4 text-center">
          
          <div className="space-y-8 max-w-5xl mx-auto">
            
            {/* Badge BPS */}
            <div className="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-white/10 border border-white/20 backdrop-blur-md shadow-lg mb-4 animate-fade-in-up">
              <img src="/src/assets/bpslogo.png" alt="BPS" className="h-5 w-auto opacity-90" />
              <span className="text-blue-100 text-xs font-bold tracking-widest uppercase">
                Badan Pusat Statistik
              </span>
            </div>

            {/* Main Title */}
            <h1 className="text-6xl md:text-8xl font-black text-white tracking-tighter leading-none drop-shadow-2xl">
              MAKIN<span className="text-transparent bg-clip-text bg-gradient-to-r from-yellow-400 to-yellow-200">ASIK</span>
            </h1>
            
            <p className="text-lg md:text-2xl text-gray-200 max-w-2xl mx-auto font-light leading-relaxed drop-shadow-md">
              Manajemen Kinerja dan Administrasi <br className="hidden md:block"/> Mitra Statistik yang Modern & Terintegrasi.
            </p>

            {/* CTA Button */}
            <div className="pt-8 animate-float">
              <button 
                onClick={scrollToContent}
                className="group relative inline-flex items-center gap-3 px-8 py-4 bg-gradient-to-r from-blue-600 to-blue-700 text-white text-sm font-bold uppercase tracking-widest rounded-full hover:from-blue-500 hover:to-blue-600 transition-all duration-300 shadow-[0_0_40px_rgba(37,99,235,0.4)] hover:shadow-[0_0_60px_rgba(37,99,235,0.6)] transform hover:-translate-y-1 border border-white/20"
              >
                Lihat Dashboard
                <span className="bg-white/20 p-1.5 rounded-full group-hover:translate-y-1 transition-transform duration-300">
                   <FaArrowDown size={12} />
                </span>
              </button>
            </div>
          </div>
        </div>

        {/* --- STATS SECTION (Floating Cards) --- */}
        <div className="relative container mx-auto px-4 md:px-8 -mt-24 mb-12">
          <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
            
            {/* Card 1 */}
            <div className="relative group bg-white/90 backdrop-blur-xl border border-white/50 p-6 rounded-3xl shadow-2xl hover:bg-white transition-all duration-500 hover:-translate-y-2">
              <div className="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                <FaClipboardList className="text-6xl text-blue-600" />
              </div>
              <div className="flex flex-col">
                <span className="text-sm font-bold text-blue-600 uppercase tracking-wider mb-2">Kegiatan Bulan Ini</span>
                <div className="flex items-baseline gap-2">
                  <span className="text-5xl font-black text-gray-800">{stats.kegiatan_aktif}</span>
                  <span className="text-gray-500 font-medium">Kegiatan</span>
                </div>
                <div className="mt-4 h-1.5 w-full bg-blue-100 rounded-full overflow-hidden">
                  <div className="h-full bg-blue-600 w-2/3 rounded-full animate-pulse"></div>
                </div>
              </div>
            </div>

            {/* Card 2 */}
            <div className="relative group bg-white/90 backdrop-blur-xl border border-white/50 p-6 rounded-3xl shadow-2xl hover:bg-white transition-all duration-500 hover:-translate-y-2">
              <div className="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                <FaUsers className="text-6xl text-yellow-500" />
              </div>
              <div className="flex flex-col">
                <span className="text-sm font-bold text-yellow-600 uppercase tracking-wider mb-2">Total Database Mitra</span>
                <div className="flex items-baseline gap-2">
                  <span className="text-5xl font-black text-gray-800">{stats.total_mitra}</span>
                  <span className="text-gray-500 font-medium">Orang</span>
                </div>
                <div className="mt-4 h-1.5 w-full bg-yellow-100 rounded-full overflow-hidden">
                  <div className="h-full bg-yellow-500 w-full rounded-full"></div>
                </div>
              </div>
            </div>

            {/* Card 3 */}
            <div className="relative group bg-white/90 backdrop-blur-xl border border-white/50 p-6 rounded-3xl shadow-2xl hover:bg-white transition-all duration-500 hover:-translate-y-2">
              <div className="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                <FaChartLine className="text-6xl text-emerald-600" />
              </div>
              <div className="flex flex-col">
                <span className="text-sm font-bold text-emerald-600 uppercase tracking-wider mb-2">Mitra Aktif (Bln Ini)</span>
                <div className="flex items-baseline gap-2">
                  <span className="text-5xl font-black text-gray-800">{stats.mitra_aktif}</span>
                  <span className="text-gray-500 font-medium">Orang</span>
                </div>
                <div className="mt-4 h-1.5 w-full bg-emerald-100 rounded-full overflow-hidden">
                  <div className="h-full bg-emerald-500 w-1/2 rounded-full animate-pulse"></div>
                </div>
              </div>
            </div>

          </div>
        </div>

        {/* --- MAIN CONTENT --- */}
        {/* rounded-t-0 agar atasnya siku-siku (tidak melengkung) */}
        <div id="konten-kegiatan" className="flex-grow bg-white relative pt-10 pb-16 rounded-t-0 shadow-[0_-20px_60px_rgba(0,0,0,0.1)] -mt-10 z-20">
          <div className="container mx-auto px-4 md:px-8">
            
            {/* Header Section dengan Garis Rata Kiri */}
            <div className="flex flex-col md:flex-row items-end mb-10 gap-6 pt-10">
              
              {/* Bagian Judul */}
              <div>
                <span className="text-blue-600 font-bold tracking-widest text-xs uppercase mb-2 block">Monitoring</span>
                <h2 className="text-3xl md:text-4xl font-extrabold text-slate-800 leading-tight">
                  Daftar Survei & <br/>
                  <span className="text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-indigo-600">Kegiatan Terbaru</span>
                </h2>
              </div>

              {/* Garis Separator (Sekarang rata kiri, menempel setelah judul) */}
              <div className="hidden md:block flex-grow h-[2px] bg-gradient-to-r from-gray-200 to-transparent mb-2 max-w-xs"></div>
            </div>
            
            {/* Daftar Kegiatan Component */}
            <div className="bg-gray-50 rounded-3xl border border-gray-100 p-2 md:p-6 shadow-inner">
               <PartDaftarKegiatan />
            </div>

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