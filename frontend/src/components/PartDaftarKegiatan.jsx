// src/components/PartDaftarKegiatan.jsx
import React, { useState, useEffect } from 'react';
import axios from 'axios';
import { useNavigate } from 'react-router-dom';
import { 
  FaChevronDown, 
  FaClipboardList, 
  FaCalendarAlt, 
  FaCheckCircle,
  FaBriefcase
} from 'react-icons/fa';

const API_URL = import.meta.env.VITE_API_URL || 'http://127.0.0.1:8000';

const PartDaftarKegiatan = () => {
  const [kegiatanList, setKegiatanList] = useState([]);
  const [userTasks, setUserTasks] = useState(new Set());
  const [mitraData, setMitraData] = useState(null);
  
  const [loading, setLoading] = useState(true);
  const [processingId, setProcessingId] = useState(null);
  const [expandedRow, setExpandedRow] = useState(null);

  const navigate = useNavigate();

  // --- 1. FETCH DATA ---
  useEffect(() => {
    const fetchData = async () => {
      setLoading(true);
      try {
        const token = localStorage.getItem('token');
        const config = token ? { headers: { Authorization: `Bearer ${token}` } } : {};

        // Ambil Data Kegiatan & Sub-Kegiatan
        const [resKegiatan, resSubKegiatan] = await Promise.all([
          axios.get(`${API_URL}/api/kegiatan`, config).catch(() => ({ data: [] })),
          axios.get(`${API_URL}/api/subkegiatan`, config).catch(() => ({ data: [] }))
        ]);

        const getList = (response) => {
            if (Array.isArray(response.data)) {
                return response.data;
            } else if (response.data && Array.isArray(response.data.data)) {
                return response.data.data;
            }
            return [];
        };

        const allInduk = getList(resKegiatan);
        const allAnak = getList(resSubKegiatan);

        // Gabungkan
        const sortedInduk = [...allInduk].sort((a, b) => b.id - a.id); 

        const mergedData = sortedInduk.map(induk => {
          const mySubs = allAnak.filter(sub => sub.id_kegiatan === induk.id);
          return { ...induk, sub_list: mySubs };
        });

        setKegiatanList(mergedData);

        // Cek User & Mitra
        const storedUser = localStorage.getItem('user');
        if (storedUser) {
          const user = JSON.parse(storedUser);
          try {
            const resMitra = await axios.get(`${API_URL}/api/mitra`, config); 
            const allMitra = getList(resMitra);
            const myMitra = allMitra.find(m => m.user_id === user.id || m.id_user === user.id);

            if (myMitra) {
              setMitraData(myMitra);
              const resKelompok = await axios.get(`${API_URL}/api/kelompok-penugasan`, config);
              const allKelompok = getList(resKelompok);

              const myTasks = new Set(
                allKelompok
                  .filter(kp => kp.id_mitra === myMitra.id)
                  .map(kp => kp.id_penugasan)
              );
              setUserTasks(myTasks);
            }
          } catch (err) {
            console.log("Info: User belum terdaftar sebagai mitra.");
          }
        }
      } catch (err) {
        console.error("Gagal memuat data kegiatan:", err);
      } finally {
        setLoading(false);
      }
    };

    fetchData();
  }, []);

  // --- 2. HANDLERS ---
  const handleRowClick = (id) => {
    setExpandedRow(prev => (prev === id ? null : id));
  };

  const handleAmbilTugas = async (e, subItem) => {
    e.stopPropagation();

    if (!subItem.id_penugasan) {
      alert("Kegiatan ini belum dibuka penugasannya oleh Admin.");
      return;
    }

    if (!mitraData) {
      if(confirm("Anda harus terdaftar sebagai Mitra untuk mengambil tugas. Lengkapi profil sekarang?")) {
          navigate('/lengkapi-profile');
      }
      return;
    }

    if (!confirm(`Daftar ke kegiatan "${subItem.nama_sub_kegiatan}"?`)) return;

    setProcessingId(subItem.id_penugasan);
    try {
      const token = localStorage.getItem('token');
      await axios.post(`${API_URL}/api/kelompok-penugasan`, {
        id_penugasan: subItem.id_penugasan,
        id_mitra: mitraData.id,
      }, {
        headers: { Authorization: `Bearer ${token}` }
      });

      alert("Berhasil mendaftar!");
      setUserTasks(prev => new Set(prev).add(subItem.id_penugasan));
    } catch (err) {
      alert(err.response?.data?.message || err.response?.data?.error || "Gagal mengambil pekerjaan.");
    } finally {
      setProcessingId(null);
    }
  };

  // --- 3. UI HELPERS ---
  const formatDate = (date) => {
    if (!date) return '-';
    return new Date(date).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
  };

  const getStatusBadge = (start, end) => {
    const now = new Date();
    const startDate = new Date(start);
    const endDate = new Date(end);

    if (now < startDate) 
      return <span className="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-blue-50 text-blue-600 border border-blue-100">AKAN DATANG</span>;
    if (now > endDate) 
      return <span className="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-gray-100 text-gray-500 border border-gray-200">SELESAI</span>;
    return <span className="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-600 border border-emerald-100 animate-pulse">SEDANG BERJALAN</span>;
  };

  // Limit Display (Hanya 5 teratas)
  const displayedList = kegiatanList.slice(0, 5);

  if (loading) return (
    <div className="flex flex-col items-center justify-center py-12 space-y-4">
        <div className="w-8 h-8 border-4 border-blue-200 border-t-blue-600 rounded-full animate-spin"></div>
        <p className="text-gray-400 text-sm font-medium">Memuat daftar kegiatan...</p>
    </div>
  );

  return (
    <div className="w-full space-y-4">
      
      {displayedList.length === 0 ? (
        <div className="bg-white rounded-2xl p-8 text-center border border-gray-100 shadow-sm">
          <div className="mx-auto w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mb-4">
            <FaClipboardList className="text-gray-300 text-2xl" />
          </div>
          <p className="text-gray-500 text-sm">Belum ada kegiatan yang tersedia saat ini.</p>
        </div>
      ) : (
        displayedList.map((induk) => {
          const isExpanded = expandedRow === induk.id;
          const subCount = induk.sub_list ? induk.sub_list.length : 0;

          return (
            <div 
                key={induk.id} 
                className={`bg-white rounded-2xl border transition-all duration-300 overflow-hidden ${isExpanded ? 'border-blue-200 shadow-md ring-1 ring-blue-50' : 'border-gray-100 hover:border-blue-100 hover:shadow-sm'}`}
            >
              
              {/* --- Parent Header --- */}
              <div 
                onClick={() => handleRowClick(induk.id)} 
                className="p-5 cursor-pointer flex justify-between items-center group select-none"
              >
                <div className="flex items-start gap-4">
                  {/* Icon Box */}
                  <div className={`mt-1 p-3 rounded-xl transition-colors duration-300 ${isExpanded ? 'bg-gradient-to-br from-blue-500 to-indigo-600 text-white shadow-lg shadow-blue-200' : 'bg-gray-50 text-gray-400 group-hover:bg-blue-50 group-hover:text-blue-500'}`}>
                    <FaBriefcase size={18} />
                  </div>

                  <div>
                    <h3 className={`font-bold text-base transition-colors ${isExpanded ? 'text-gray-800' : 'text-gray-700 group-hover:text-blue-600'}`}>
                      {induk.nama_kegiatan}
                    </h3>
                    <p className="text-sm text-gray-400 mt-1 line-clamp-1">
                      {induk.deskripsi || 'Kegiatan Statistik Badan Pusat Statistik'}
                    </p>
                    
                    <div className="flex items-center gap-3 mt-2">
                        <span className="text-[11px] font-medium text-gray-500 bg-gray-100 px-2.5 py-1 rounded-md">
                            {subCount} Sub-Kegiatan
                        </span>
                        {isExpanded && (
                            <span className="text-[10px] text-blue-500 font-semibold animate-fade-in">
                                Sedang Dilihat
                            </span>
                        )}
                    </div>
                  </div>
                </div>

                <div className={`transform transition-transform duration-300 text-gray-300 group-hover:text-blue-400 ${isExpanded ? 'rotate-180' : ''}`}>
                  <FaChevronDown />
                </div>
              </div>

              {/* --- Expanded Child List --- */}
              <div className={`transition-all duration-500 ease-in-out ${isExpanded ? 'max-h-[1000px] opacity-100' : 'max-h-0 opacity-0'}`}>
                <div className="bg-gray-50/50 border-t border-gray-100 p-4 space-y-3">
                  
                  {subCount > 0 ? (
                    induk.sub_list.map((sub) => {
                       const isTerdaftar = userTasks.has(sub.id_penugasan);
                       
                       return (
                        <div key={sub.id} className="bg-white rounded-xl p-4 border border-gray-100 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4 hover:border-blue-200 transition-colors">
                            
                            {/* Left Info */}
                            <div className="flex-1">
                                <div className="flex items-center gap-2 mb-1">
                                    <h4 className="font-semibold text-sm text-gray-700">{sub.nama_sub_kegiatan}</h4>
                                    {getStatusBadge(sub.tanggal_mulai, sub.tanggal_selesai)}
                                </div>
                                <div className="flex items-center gap-4 text-xs text-gray-500">
                                    <div className="flex items-center gap-1.5">
                                        <FaCalendarAlt className="text-gray-300" />
                                        <span>{formatDate(sub.tanggal_mulai)} - {formatDate(sub.tanggal_selesai)}</span>
                                    </div>
                                </div>
                            </div>

                            {/* Right Action */}
                            <div className="flex items-center justify-end">
                                {!mitraData ? (
                                    <button 
                                        onClick={(e) => { e.stopPropagation(); navigate('/lengkapi-profile'); }}
                                        className="text-xs font-medium text-gray-500 bg-gray-100 hover:bg-gray-200 px-4 py-2 rounded-lg transition"
                                    >
                                        Daftar Mitra
                                    </button>
                                ) : !sub.id_penugasan ? (
                                    <span className="px-3 py-1.5 bg-gray-100 text-gray-400 text-[10px] font-bold rounded-lg uppercase tracking-wider">
                                        Belum Dibuka
                                    </span>
                                ) : isTerdaftar ? (
                                    <div className="flex items-center gap-2 px-4 py-2 bg-green-50 text-green-700 rounded-lg border border-green-100">
                                        <FaCheckCircle />
                                        <span className="text-xs font-bold">Terdaftar</span>
                                    </div>
                                ) : (
                                    <button
                                        onClick={(e) => handleAmbilTugas(e, sub)}
                                        disabled={processingId === sub.id_penugasan}
                                        className="relative overflow-hidden group bg-gradient-to-r from-blue-600 to-indigo-600 text-white text-xs font-bold px-5 py-2.5 rounded-lg shadow-md hover:shadow-lg hover:translate-y-[-1px] transition-all disabled:opacity-50 disabled:hover:translate-y-0"
                                    >
                                        <span className="relative z-10">
                                            {processingId === sub.id_penugasan ? 'Memproses...' : 'Ambil Tugas'}
                                        </span>
                                        <div className="absolute inset-0 bg-white/20 translate-y-full group-hover:translate-y-0 transition-transform duration-300"></div>
                                    </button>
                                )}
                            </div>
                        </div>
                    )})
                  ) : (
                    <div className="text-center py-4 text-gray-400 text-xs italic">
                      Tidak ada sub-kegiatan aktif saat ini.
                    </div>
                  )}
                  
                </div>
              </div>

            </div>
          );
        })
      )}

    </div>
  );
};

export default PartDaftarKegiatan;