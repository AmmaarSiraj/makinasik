import React, { useState, useEffect } from 'react';
import axios from 'axios';
import { useNavigate } from 'react-router-dom';
import { FaChartLine, FaExclamationTriangle, FaCheckCircle, FaArrowRight } from 'react-icons/fa';

const API_URL = import.meta.env.VITE_API_URL || 'http://127.0.0.1:8000';

const PartRekapPerencanaan = () => {
  const [dataBulan, setDataBulan] = useState([]);
  const [loading, setLoading] = useState(true);
  const navigate = useNavigate();
  const currentYear = new Date().getFullYear();

  useEffect(() => {
    const fetchData = async () => {
      try {
        const token = localStorage.getItem('token');
        const config = token ? { headers: { Authorization: `Bearer ${token}` } } : {};
        
        // Ambil data rekap bulanan tahun ini
        const response = await axios.get(`${API_URL}/api/rekap/bulanan`, {
            ...config,
            params: { year: currentYear }
        });
        
        // Ambil data dari response
        const rawData = response.data.data || [];
        // Filter hanya bulan yang ada datanya (total_honor > 0)
        const activeMonths = rawData.filter(m => Number(m.total_honor) > 0);
        
        setDataBulan(activeMonths);
      } catch (err) {
        console.error("Gagal memuat rekap:", err);
      } finally {
        setLoading(false);
      }
    };

    fetchData();
  }, []);

  const formatRupiah = (num) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(num);
  };

  if (loading) return <div className="text-center py-8 text-gray-400 text-xs">Memuat data rekap...</div>;

  return (
    <div className="w-full">
      {dataBulan.length === 0 ? (
        <div className="bg-white rounded-2xl p-8 text-center border border-gray-100 border-dashed">
            <FaChartLine className="mx-auto text-gray-300 mb-3 text-2xl" />
            <p className="text-gray-500 text-sm font-medium">Belum ada realisasi anggaran tahun {currentYear}.</p>
        </div>
      ) : (
        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            {dataBulan.slice(0, 4).map((bulan) => (
                <div key={bulan.bulan_angka} className="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition-all group relative overflow-hidden">
                    <div className="flex justify-between items-start mb-3">
                        <span className="text-sm font-bold text-gray-600 bg-gray-50 px-3 py-1 rounded-lg group-hover:bg-blue-50 group-hover:text-blue-600 transition-colors">
                            {bulan.bulan_nama}
                        </span>
                        {bulan.status === 'Aman' ? (
                            <FaCheckCircle className="text-emerald-400" />
                        ) : (
                            <FaExclamationTriangle className="text-amber-400" />
                        )}
                    </div>
                    
                    <div className="space-y-1">
                        <p className="text-xs text-gray-400 uppercase tracking-wider font-semibold">Realisasi</p>
                        <p className="text-lg font-extrabold text-slate-800">
                            {formatRupiah(bulan.total_honor)}
                        </p>
                    </div>

                    <div className="mt-4 pt-3 border-t border-gray-50 flex items-center justify-between text-xs">
                        <span className="text-gray-500">{bulan.mitra_count} Mitra</span>
                        <span className={`font-bold ${bulan.status === 'Aman' ? 'text-emerald-600' : 'text-amber-600'}`}>
                            {bulan.status}
                        </span>
                    </div>
                    
                    {/* Decorative Gradient */}
                    <div className="absolute bottom-0 left-0 w-full h-1 bg-gradient-to-r from-blue-500 to-indigo-500 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                </div>
            ))}
        </div>
      )}
      
      {dataBulan.length > 0 && (
        <div className="mt-6 text-center">
             <button 
                onClick={() => navigate('/rekap-perencanaan')}
                className="inline-flex items-center gap-2 text-sm font-bold text-blue-600 hover:text-blue-800 transition-colors group"
             >
                Lihat Detail Rekapitulasi
                <FaArrowRight className="group-hover:translate-x-1 transition-transform" />
             </button>
        </div>
      )}
    </div>
  );
};

export default PartRekapPerencanaan;