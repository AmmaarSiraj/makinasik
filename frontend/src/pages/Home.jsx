// src/pages/Home.jsx
import React, { useEffect, useState } from 'react';
import axios from 'axios';
import { Link } from 'react-router-dom';
import { 
  FaClipboardList, FaUsers, FaArrowDown, FaChartLine, FaArrowRight,
  FaPoll, FaBriefcase, FaFileSignature, FaChartPie, FaExchangeAlt,
  FaCalendarAlt, FaCheckCircle, FaDatabase, FaMoneyBillWave, FaFileAlt
} from 'react-icons/fa';

// Components
import Footer from '../components/Footer';

const API_URL = import.meta.env.VITE_API_URL || 'http://127.0.0.1:8000';

const Home = () => {
  // --- STATE MANAGEMENT ---
  const [dashboardData, setDashboardData] = useState({
    raw: {
        kegiatan: [],
        penugasan: [],
        mitra: [],
        transaksi: [],
        templateSpk: []
    },
    counts: {
        kegiatanTotal: 0, kegiatanAktif: 0,
        perencanaanTotal: 0,
        penugasanTotal: 0, penugasanBulanIni: 0,
        mitraTotal: 0, mitraAktif: 0,
        transaksiTotal: 0,
        spkTotal: 0
    },
    tables: {
        topKegiatan: [],
        topPenugasan: [],
        topMitra: [],
        topTransaksi: [],
        topTemplateSPK: []
    }
  });

  const [isLoading, setIsLoading] = useState(true);

  const scrollToContent = () => {
    const element = document.getElementById('content-area');
    if (element) {
      element.scrollIntoView({ behavior: 'smooth' });
    }
  };

  // Helper Format Data
  const formatDate = (dateString) => {
    if (!dateString) return '-';
    return new Date(dateString).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: '2-digit' });
  };

  const formatRupiah = (number) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(number);
  };

  // --- FETCH & PROCESS DATA ---
  useEffect(() => {
    const fetchData = async () => {
      try {
        const token = localStorage.getItem('token');
        const config = token ? { headers: { Authorization: `Bearer ${token}` } } : {};

        // Request API Paralel
        const responses = await Promise.all([
          axios.get(`${API_URL}/api/kegiatan`, config).catch(() => ({ data: { data: [] } })),
          axios.get(`${API_URL}/api/perencanaan`, config).catch(() => ({ data: { data: [] } })),
          axios.get(`${API_URL}/api/penugasan`, config).catch(() => ({ data: { data: [] } })),
          axios.get(`${API_URL}/api/kelompok-penugasan`, config).catch(() => ({ data: { data: [] } })),
          axios.get(`${API_URL}/api/mitra`, config).catch(() => ({ data: { data: [] } })),
          axios.get(`${API_URL}/api/transaksi`, config).catch(() => ({ data: { data: [] } })),
          axios.get(`${API_URL}/api/template-spk`, config).catch(() => ({ data: { data: [] } }))
        ]);

        const [resKeg, resRencana, resTugas, resKelompok, resMitra, resTrans, resSpk] = responses;
        
        const rawKeg = resKeg.data.data || [];
        const rawTugas = resTugas.data.data || [];
        const rawMitraData = resMitra.data.data || [];
        const rawKelompok = resKelompok.data.data || [];
        const rawTrans = resTrans.data.data || [];
        const rawSpk = resSpk.data.data || [];

        // --- MAP DATA HELPERS ---
        // Buat Map ID Mitra ke Nama untuk lookup cepat di tabel transaksi
        const mitraMap = rawMitraData.reduce((acc, curr) => {
            acc[curr.id] = curr.nama;
            return acc;
        }, {});

        // --- LOGIKA STATISTIK ---
        const today = new Date();
        const startOfMonth = new Date(today.getFullYear(), today.getMonth(), 1);
        const endOfMonth = new Date(today.getFullYear(), today.getMonth() + 1, 0);

        // 1. Kegiatan Aktif
        const kegAktif = rawKeg.filter(k => {
            const start = new Date(k.tanggal_mulai);
            const end = new Date(k.tanggal_selesai);
            return start <= endOfMonth && end >= startOfMonth;
        }).length;

        // 2. Penugasan & Mitra Aktif
        const activeTaskIds = new Set();
        const tugasBulanIni = rawTugas.filter(t => {
             const start = new Date(t.tanggal_mulai);
             const end = new Date(t.tanggal_selesai);
             const isActive = start <= endOfMonth && end >= startOfMonth;
             if(isActive) activeTaskIds.add(t.id_penugasan);
             return isActive;
        }).length;

        const mitraAktifSet = new Set();
        rawKelompok.forEach(k => {
            if (activeTaskIds.has(k.id_penugasan)) mitraAktifSet.add(k.id_mitra);
        });

        // --- SORTING & SLICING (TOP 5) ---
        const sortedKegiatan = [...rawKeg].sort((a, b) => new Date(b.tanggal_mulai) - new Date(a.tanggal_mulai)).slice(0, 5);
        const sortedPenugasan = [...rawTugas].sort((a, b) => new Date(b.created_at) - new Date(a.created_at)).slice(0, 5);
        const sortedMitra = [...rawMitraData].sort((a, b) => b.id - a.id).slice(0, 5);
        const sortedTemplate = [...rawSpk].slice(0, 5); // Default sort
        
        // Enrich Transaksi dengan Nama Mitra
        const enrichedTransaksi = rawTrans.map(t => ({
            ...t,
            nama_mitra: mitraMap[t.id_mitra] || 'Mitra Tidak Dikenal'
        })).sort((a, b) => new Date(b.created_at) - new Date(a.created_at)).slice(0, 5);

        setDashboardData({
            raw: { kegiatan: rawKeg, penugasan: rawTugas, mitra: rawMitraData, transaksi: rawTrans, templateSpk: rawSpk },
            counts: {
                kegiatanTotal: rawKeg.length, kegiatanAktif: kegAktif,
                perencanaanTotal: resRencana.data.data?.length || 0,
                penugasanTotal: rawTugas.length, penugasanBulanIni: tugasBulanIni,
                mitraTotal: rawMitraData.length, mitraAktif: mitraAktifSet.size,
                transaksiTotal: rawTrans.length,
                spkTotal: rawSpk.length
            },
            tables: {
                topKegiatan: sortedKegiatan,
                topPenugasan: sortedPenugasan,
                topMitra: sortedMitra,
                topTransaksi: enrichedTransaksi,
                topTemplateSPK: sortedTemplate
            }
        });

      } catch (error) {
        console.error("Gagal memuat data dashboard:", error);
      } finally {
        setIsLoading(false);
      }
    };

    fetchData();
  }, []);

  // --- COMPONENT: FEATURE CARD ---
  const features = [
    { title: "Survei & Kegiatan", desc: "Monitoring kegiatan statistik.", icon: FaPoll, color: "blue", path: "/daftar-kegiatan", stats: [{ label: "Total", value: dashboardData.counts.kegiatanTotal }, { label: "Aktif", value: dashboardData.counts.kegiatanAktif, highlight: true }] },
    { title: "Perencanaan", desc: "Matriks dan alokasi kebutuhan.", icon: FaClipboardList, color: "indigo", path: "/perencanaan", stats: [{ label: "Draft Rencana", value: dashboardData.counts.perencanaanTotal }, { label: "Status", value: "Ready" }] },
    { title: "Rekapitulasi", desc: "Analisis beban kerja & anggaran.", icon: FaChartPie, color: "purple", path: "/rekap", stats: [{ label: "Analisis", value: "Bulanan" }] },
    { title: "Penugasan", desc: "Distribusi beban kerja mitra.", icon: FaBriefcase, color: "emerald", path: "/penugasan", stats: [{ label: "Total", value: dashboardData.counts.penugasanTotal }, { label: "Bulan Ini", value: dashboardData.counts.penugasanBulanIni, highlight: true }] },
    { title: "Generate SPK", desc: "Pencetakan perjanjian kerja.", icon: FaFileSignature, color: "rose", path: "/spk", stats: [{ label: "Template", value: dashboardData.counts.spkTotal }] },
    { title: "Database Mitra", desc: "Pengelolaan data induk (Sobat).", icon: FaUsers, color: "amber", path: "/daftar-mitra", stats: [{ label: "Total", value: dashboardData.counts.mitraTotal }, { label: "Aktif", value: dashboardData.counts.mitraAktif, highlight: true }] },
    { title: "Riwayat Transaksi", desc: "Log pembayaran honorarium.", icon: FaExchangeAlt, color: "orange", path: "/transaksi-mitra", stats: [{ label: "Total Log", value: dashboardData.counts.transaksiTotal }] }
  ];

  const FeatureCard = ({ item }) => {
    const Icon = item.icon;
    const colorClasses = {
        blue: "bg-blue-50 text-blue-600 group-hover:bg-blue-600 group-hover:text-white border-blue-100",
        indigo: "bg-indigo-50 text-indigo-600 group-hover:bg-indigo-600 group-hover:text-white border-indigo-100",
        purple: "bg-purple-50 text-purple-600 group-hover:bg-purple-600 group-hover:text-white border-purple-100",
        emerald: "bg-emerald-50 text-emerald-600 group-hover:bg-emerald-600 group-hover:text-white border-emerald-100",
        rose: "bg-rose-50 text-rose-600 group-hover:bg-rose-600 group-hover:text-white border-rose-100",
        amber: "bg-amber-50 text-amber-600 group-hover:bg-amber-600 group-hover:text-white border-amber-100",
        orange: "bg-orange-50 text-orange-600 group-hover:bg-orange-600 group-hover:text-white border-orange-100",
    };
    const styleClass = colorClasses[item.color] || colorClasses.blue;

    return (
        <Link to={item.path} className="group relative flex flex-col h-full bg-white rounded-2xl p-5 border border-gray-100 shadow-sm hover:shadow-xl transition-all duration-300 hover:-translate-y-1 overflow-hidden">
            <div className="flex items-start justify-between mb-3">
                <div className={`w-10 h-10 rounded-lg flex items-center justify-center transition-colors duration-300 ${styleClass}`}>
                    <Icon size={18} />
                </div>
                <FaArrowRight size={12} className="text-gray-300 group-hover:text-blue-600 transition-colors" />
            </div>
            <h3 className="text-base font-bold text-gray-800 mb-1 group-hover:text-blue-700 transition-colors">{item.title}</h3>
            <p className="text-xs text-gray-500 leading-relaxed mb-4 h-8 line-clamp-2">{item.desc}</p>
            <div className="mt-auto pt-3 border-t border-gray-50 grid grid-cols-2 gap-2">
                {item.stats && item.stats.map((stat, idx) => (
                    <div key={idx} className="flex flex-col">
                        <span className="text-[10px] uppercase font-semibold text-gray-400 tracking-wider">{stat.label}</span>
                        <span className={`text-sm font-bold ${stat.highlight ? 'text-blue-600' : 'text-gray-700'}`}>{isLoading ? '...' : stat.value}</span>
                    </div>
                ))}
            </div>
        </Link>
    );
  };

  return (
    <div className="relative w-full min-h-screen font-sans bg-gray-50 overflow-x-hidden">
      
      <style>{`
        @keyframes float { 0% { transform: translateY(0px); } 50% { transform: translateY(-15px); } 100% { transform: translateY(0px); } }
        .animate-float { animation: float 6s ease-in-out infinite; }
      `}</style>

      {/* --- BACKGROUND --- */}
      <div className="fixed inset-0 z-0">
        <img src="https://images.unsplash.com/photo-1551288049-bebda4e38f71?q=80&w=2070&auto=format&fit=crop" alt="Background" className="w-full h-full object-cover"/>
        <div className="absolute inset-0 bg-gradient-to-b from-slate-900/90 via-slate-900/80 to-blue-900/80"></div>
        <div className="absolute inset-0 bg-[url('https://grainy-gradients.vercel.app/noise.svg')] opacity-20 mix-blend-soft-light"></div>
      </div>

      {/* --- CONTENT --- */}
      <div className="relative z-10 flex flex-col min-h-screen">

        {/* HERO */}
        <div className="relative min-h-[75vh] flex flex-col justify-center items-center px-4 text-center pb-32">
          <div className="space-y-6 max-w-4xl mx-auto">
            <div className="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-white/10 border border-white/20 backdrop-blur-md shadow-lg">
              <img src="/src/assets/bpslogo.png" alt="BPS" className="h-4 w-auto opacity-90" />
              <span className="text-blue-100 text-[10px] md:text-xs font-bold tracking-widest uppercase">Badan Pusat Statistik</span>
            </div>
            <h1 className="text-5xl md:text-7xl font-black text-white tracking-tighter leading-none drop-shadow-2xl">
              MAKIN<span className="text-transparent bg-clip-text bg-gradient-to-r from-yellow-400 to-yellow-200">ASIK</span>
            </h1>
            <p className="text-base md:text-xl text-gray-200 max-w-2xl mx-auto font-light leading-relaxed drop-shadow-md">
              Sistem Informasi Manajemen Kinerja dan Administrasi <br className="hidden md:block"/> Mitra Statistik Terintegrasi
            </p>
            <div className="pt-8 animate-float">
              <button onClick={scrollToContent} className="group relative inline-flex items-center gap-3 px-8 py-3 bg-gradient-to-r from-blue-600 to-blue-700 text-white text-sm font-bold uppercase tracking-widest rounded-full hover:from-blue-500 hover:to-blue-600 transition-all duration-300 shadow-[0_0_40px_rgba(37,99,235,0.4)] border border-white/20">
                Akses Dashboard <FaArrowDown className="group-hover:translate-y-1 transition-transform" />
              </button>
            </div>
          </div>
        </div>

        {/* FLOATING STATS */}
        <div className="relative container mx-auto px-4 md:px-8 -mt-32 mb-12 z-20">
          <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div className="bg-white/90 backdrop-blur-xl border border-white/50 p-6 rounded-3xl shadow-2xl flex flex-col relative overflow-hidden group hover:-translate-y-1 transition-all">
                 <div className="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity"><FaClipboardList className="text-6xl text-blue-600" /></div>
                 <span className="text-sm font-bold text-blue-600 uppercase tracking-wider mb-2">Kegiatan Bulan Ini</span>
                 <div className="flex items-baseline gap-2"><span className="text-5xl font-black text-gray-800">{isLoading ? '-' : dashboardData.counts.kegiatanAktif}</span><span className="text-gray-500 font-medium">Aktif</span></div>
            </div>
            <div className="bg-white/90 backdrop-blur-xl border border-white/50 p-6 rounded-3xl shadow-2xl flex flex-col relative overflow-hidden group hover:-translate-y-1 transition-all">
                 <div className="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity"><FaUsers className="text-6xl text-yellow-500" /></div>
                 <span className="text-sm font-bold text-yellow-600 uppercase tracking-wider mb-2">Total Database Mitra</span>
                 <div className="flex items-baseline gap-2"><span className="text-5xl font-black text-gray-800">{isLoading ? '-' : dashboardData.counts.mitraTotal}</span><span className="text-gray-500 font-medium">Orang</span></div>
            </div>
            <div className="bg-white/90 backdrop-blur-xl border border-white/50 p-6 rounded-3xl shadow-2xl flex flex-col relative overflow-hidden group hover:-translate-y-1 transition-all">
                 <div className="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity"><FaChartLine className="text-6xl text-emerald-600" /></div>
                 <span className="text-sm font-bold text-emerald-600 uppercase tracking-wider mb-2">Mitra Aktif Bulan Ini</span>
                 <div className="flex items-baseline gap-2"><span className="text-5xl font-black text-gray-800">{isLoading ? '-' : dashboardData.counts.mitraAktif}</span><span className="text-gray-500 font-medium">Orang</span></div>
            </div>
          </div>
        </div>

        {/* MAIN DATA SECTION */}
        <div id="content-area" className="relative z-10 bg-white pt-20 pb-24 shadow-[0_-20px_60px_rgba(0,0,0,0.05)] -mt-16">
          <div className="container mx-auto px-4 md:px-8 space-y-12">
            
            {/* 1. GRID FITUR */}
            <div>
                <div className="text-center max-w-2xl mx-auto mb-10">
                    <span className="text-blue-600 font-bold tracking-widest text-xs uppercase mb-2 block">Modul Aplikasi</span>
                    <h2 className="text-3xl font-black text-slate-800">Akses Fitur Unggulan</h2>
                </div>
                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                    {features.map((item, index) => (
                        <FeatureCard key={index} item={item} />
                    ))}
                </div>
            </div>

            {/* 2. DATA TABLES */}
            <div>
                <div className="flex items-center gap-3 mb-8">
                    <div className="h-1 w-10 bg-blue-600 rounded-full"></div>
                    <h2 className="text-2xl font-black text-slate-800">Ringkasan Data Terbaru</h2>
                </div>

                {/* ROW 1: KEGIATAN */}
                <div className="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mb-8">
                    <div className="p-5 border-b border-gray-100 bg-gray-50/80 flex justify-between items-center">
                        <h3 className="font-bold text-gray-700 flex items-center gap-2"><FaCalendarAlt className="text-blue-500"/> Monitoring Kegiatan Terkini</h3>
                        <Link to="/daftar-kegiatan" className="text-xs font-bold text-blue-600 uppercase tracking-wider hover:underline">Lihat Semua</Link>
                    </div>
                    <table className="w-full text-sm text-left text-gray-600">
                        <thead className="bg-gray-50 text-xs uppercase font-semibold text-gray-500">
                            <tr><th className="px-6 py-3">Nama Kegiatan</th><th className="px-6 py-3">Periode</th></tr>
                        </thead>
                        <tbody className="divide-y divide-gray-100">
                            {isLoading ? <tr><td colSpan="2" className="px-6 py-4 text-center">Loading...</td></tr> :
                             dashboardData.tables.topKegiatan.length === 0 ? <tr><td colSpan="2" className="px-6 py-4 text-center italic">Kosong</td></tr> :
                             dashboardData.tables.topKegiatan.map((item, idx) => (
                                <tr key={idx} className="hover:bg-blue-50/20"><td className="px-6 py-4 font-medium text-gray-800">{item.nama_kegiatan}</td><td className="px-6 py-4 text-xs">{formatDate(item.tanggal_mulai)} - {formatDate(item.tanggal_selesai)}</td></tr>
                            ))}
                        </tbody>
                    </table>
                </div>

                {/* ROW 2: PENUGASAN & MITRA */}
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
                    {/* Penugasan */}
                    <div className="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden flex flex-col">
                        <div className="p-5 border-b border-gray-100 bg-gray-50/80 flex justify-between items-center">
                             <h3 className="font-bold text-gray-700 flex items-center gap-2"><FaCheckCircle className="text-emerald-500"/> Penugasan Baru</h3>
                             <Link to="/penugasan" className="text-xs font-bold text-emerald-600 uppercase tracking-wider hover:underline">Log Penugasan</Link>
                        </div>
                        <table className="w-full text-sm text-left text-gray-600 flex-grow">
                            <thead className="bg-gray-50 text-xs uppercase font-semibold text-gray-500"><tr><th className="px-5 py-3">Kegiatan</th><th className="px-5 py-3">Dibuat</th></tr></thead>
                            <tbody className="divide-y divide-gray-100">
                                 {isLoading ? <tr><td colSpan="2" className="px-5 py-4 text-center">Loading...</td></tr> :
                                  dashboardData.tables.topPenugasan.length === 0 ? <tr><td colSpan="2" className="px-5 py-4 text-center italic">Kosong</td></tr> :
                                  dashboardData.tables.topPenugasan.map((p, idx) => (
                                    <tr key={idx} className="hover:bg-emerald-50/20"><td className="px-5 py-3 truncate max-w-[180px]" title={p.nama_kegiatan}>{p.nama_kegiatan || '-'}</td><td className="px-5 py-3 text-xs">{formatDate(p.created_at)}</td></tr>
                                 ))}
                            </tbody>
                        </table>
                    </div>
                    {/* Mitra */}
                    <div className="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden flex flex-col">
                        <div className="p-5 border-b border-gray-100 bg-gray-50/80 flex justify-between items-center">
                             <h3 className="font-bold text-gray-700 flex items-center gap-2"><FaDatabase className="text-yellow-500"/> Mitra Terbaru</h3>
                             <Link to="/daftar-mitra" className="text-xs font-bold text-yellow-600 uppercase tracking-wider hover:underline">Semua Mitra</Link>
                        </div>
                        <table className="w-full text-sm text-left text-gray-600 flex-grow">
                            <thead className="bg-gray-50 text-xs uppercase font-semibold text-gray-500"><tr><th className="px-5 py-3">ID Sobat</th><th className="px-5 py-3">Nama Lengkap</th></tr></thead>
                            <tbody className="divide-y divide-gray-100">
                                 {isLoading ? <tr><td colSpan="2" className="px-5 py-4 text-center">Loading...</td></tr> :
                                  dashboardData.tables.topMitra.length === 0 ? <tr><td colSpan="2" className="px-5 py-4 text-center italic">Kosong</td></tr> :
                                  dashboardData.tables.topMitra.map((m, idx) => (
                                    <tr key={idx} className="hover:bg-yellow-50/20"><td className="px-5 py-3 font-mono text-xs font-bold text-blue-600">{m.sobat_id}</td><td className="px-5 py-3 font-medium">{m.nama}</td></tr>
                                 ))}
                            </tbody>
                        </table>
                    </div>
                </div>

                {/* ROW 3: TRANSAKSI & SPK */}
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    {/* Transaksi */}
                    <div className="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden flex flex-col">
                        <div className="p-5 border-b border-gray-100 bg-gray-50/80 flex justify-between items-center">
                             <h3 className="font-bold text-gray-700 flex items-center gap-2"><FaMoneyBillWave className="text-orange-500"/> Riwayat Transaksi</h3>
                             <Link to="/transaksi-mitra" className="text-xs font-bold text-orange-600 uppercase tracking-wider hover:underline">Lihat Transaksi</Link>
                        </div>
                        <table className="w-full text-sm text-left text-gray-600 flex-grow">
                            <thead className="bg-gray-50 text-xs uppercase font-semibold text-gray-500"><tr><th className="px-5 py-3">Mitra</th><th className="px-5 py-3">Nominal (Net)</th></tr></thead>
                            <tbody className="divide-y divide-gray-100">
                                 {isLoading ? <tr><td colSpan="2" className="px-5 py-4 text-center">Loading...</td></tr> :
                                  dashboardData.tables.topTransaksi.length === 0 ? <tr><td colSpan="2" className="px-5 py-4 text-center italic">Belum ada transaksi</td></tr> :
                                  dashboardData.tables.topTransaksi.map((t, idx) => (
                                    <tr key={idx} className="hover:bg-orange-50/20">
                                        <td className="px-5 py-3">
                                            <div className="font-medium text-gray-800">{t.nama_mitra}</div>
                                            <div className="text-[10px] text-gray-400">{formatDate(t.created_at)}</div>
                                        </td>
                                        <td className="px-5 py-3 font-bold text-gray-700">{formatRupiah(t.total_net)}</td>
                                    </tr>
                                 ))}
                            </tbody>
                        </table>
                    </div>
                    {/* Template SPK */}
                    <div className="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden flex flex-col">
                        <div className="p-5 border-b border-gray-100 bg-gray-50/80 flex justify-between items-center">
                             <h3 className="font-bold text-gray-700 flex items-center gap-2"><FaFileAlt className="text-rose-500"/> Daftar Template SPK</h3>
                             <Link to="/spk" className="text-xs font-bold text-rose-600 uppercase tracking-wider hover:underline">Manajemen SPK</Link>
                        </div>
                        <table className="w-full text-sm text-left text-gray-600 flex-grow">
                            <thead className="bg-gray-50 text-xs uppercase font-semibold text-gray-500"><tr><th className="px-5 py-3">Nama Template</th><th className="px-5 py-3">Nomor Surat</th></tr></thead>
                            <tbody className="divide-y divide-gray-100">
                                 {isLoading ? <tr><td colSpan="2" className="px-5 py-4 text-center">Loading...</td></tr> :
                                  dashboardData.tables.topTemplateSPK.length === 0 ? <tr><td colSpan="2" className="px-5 py-4 text-center italic">Template Kosong</td></tr> :
                                  dashboardData.tables.topTemplateSPK.map((s, idx) => (
                                    <tr key={idx} className="hover:bg-rose-50/20">
                                        <td className="px-5 py-3 font-medium text-gray-800">{s.nama_template}</td>
                                        <td className="px-5 py-3 font-mono text-xs text-gray-500 truncate max-w-[150px]">{s.nomor_surat || '-'}</td>
                                    </tr>
                                 ))}
                            </tbody>
                        </table>
                    </div>
                </div>

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