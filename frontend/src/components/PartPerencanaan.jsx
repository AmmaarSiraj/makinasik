import React, { useState, useEffect } from 'react';
import axios from 'axios';
import { useNavigate } from 'react-router-dom';
import { 
  FaChartPie, 
  FaCalendarAlt, 
  FaUserTie, 
  FaArrowRight, 
  FaHourglassHalf 
} from 'react-icons/fa';

const API_URL = import.meta.env.VITE_API_URL || 'http://127.0.0.1:8000';

const PartPerencanaan = () => {
  const [dataPerencanaan, setDataPerencanaan] = useState([]);
  const [loading, setLoading] = useState(true);
  const navigate = useNavigate();

  // --- 1. FETCH DATA ---
  useEffect(() => {
    const fetchData = async () => {
      try {
        const token = localStorage.getItem('token');
        const config = token ? { headers: { Authorization: `Bearer ${token}` } } : {};

        const response = await axios.get(`${API_URL}/api/perencanaan`, config);
        
        let fetchedData = [];
        if (response.data && Array.isArray(response.data.data)) {
            fetchedData = response.data.data;
        } else if (Array.isArray(response.data)) {
            fetchedData = response.data;
        }

        // Urutkan berdasarkan ID terbaru (descending) dan ambil 5 teratas
        const sortedData = fetchedData.sort((a, b) => b.id_perencanaan - a.id_perencanaan).slice(0, 5);
        setDataPerencanaan(sortedData);

      } catch (err) {
        console.error("Gagal memuat data perencanaan:", err);
      } finally {
        setLoading(false);
      }
    };

    fetchData();
  }, []);

  // --- 2. UI HELPERS ---
  const formatDate = (dateString) => {
    if (!dateString) return '-';
    return new Date(dateString).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
  };

  if (loading) return (
    <div className="flex flex-col items-center justify-center py-10 space-y-3">
        <div className="w-6 h-6 border-2 border-indigo-200 border-t-indigo-600 rounded-full animate-spin"></div>
        <p className="text-gray-400 text-xs font-medium">Memuat rencana...</p>
    </div>
  );

  return (
    <div className="w-full space-y-4">
      
      {/* Header Kecil untuk Part Ini */}
      <div className="flex items-center gap-2 mb-2 px-1">
        <div className="p-1.5 bg-indigo-50 text-indigo-600 rounded-lg">
            <FaChartPie size={14} />
        </div>
        <h3 className="font-bold text-gray-700 text-sm">Rencana Akan Datang</h3>
      </div>

      {/* List Content */}
      <div className="space-y-3">
        {dataPerencanaan.length === 0 ? (
            <div className="bg-white rounded-xl p-6 text-center border border-gray-100 border-dashed">
                <FaHourglassHalf className="mx-auto text-gray-300 mb-2" />
                <p className="text-gray-400 text-xs italic">Belum ada perencanaan kegiatan.</p>
            </div>
        ) : (
            dataPerencanaan.map((item) => (
                <div 
                    key={item.id_perencanaan} 
                    className="group bg-white rounded-xl p-4 border border-gray-100 shadow-sm hover:shadow-md hover:border-indigo-100 transition-all cursor-pointer relative overflow-hidden"
                    onClick={() => navigate(`/perencanaan`)} // Atau navigate ke detail jika user punya akses
                >
                    <div className="relative z-10 flex flex-col gap-2">
                        {/* Judul Sub Kegiatan */}
                        <div>
                            <h4 className="font-bold text-sm text-gray-800 group-hover:text-indigo-700 transition-colors line-clamp-1">
                                {item.nama_sub_kegiatan}
                            </h4>
                            <p className="text-[10px] text-gray-400 uppercase tracking-wide font-medium mt-0.5">
                                {item.nama_kegiatan}
                            </p>
                        </div>

                        {/* Info Tanggal & Pengawas */}
                        <div className="flex flex-wrap items-center gap-3 mt-1">
                            <div className="flex items-center gap-1.5 bg-gray-50 px-2 py-1 rounded text-[10px] text-gray-500 border border-gray-100">
                                <FaCalendarAlt className="text-indigo-400" />
                                <span>{formatDate(item.tanggal_mulai)}</span>
                            </div>
                            
                            <div className="flex items-center gap-1.5 px-1 text-[10px] text-gray-500">
                                <FaUserTie className="text-gray-300" />
                                <span className="truncate max-w-[100px]">{item.nama_pengawas}</span>
                            </div>
                        </div>
                    </div>

                    {/* Dekorasi Hover */}
                    <div className="absolute top-0 right-0 w-16 h-full bg-gradient-to-l from-indigo-50/50 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                </div>
            ))
        )}
      </div>

      {/* Footer Link */}
      {dataPerencanaan.length > 0 && (
        <div className="pt-2 text-right">
            <button 
                onClick={() => navigate('/perencanaan')}
                className="inline-flex items-center gap-1 text-xs font-bold text-indigo-600 hover:text-indigo-800 transition-colors group"
            >
                Lihat Semua
                <FaArrowRight className="group-hover:translate-x-1 transition-transform" size={10} />
            </button>
        </div>
      )}

    </div>
  );
};

export default PartPerencanaan;