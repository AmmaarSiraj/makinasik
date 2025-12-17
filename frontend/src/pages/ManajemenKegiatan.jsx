// src/pages/ManajemenKegiatan.jsx
import React, { useState, useEffect, useMemo } from 'react';
import { useNavigate } from 'react-router-dom';
import axios from 'axios';
import { 
  FaChevronDown, 
  FaChevronUp,
  FaInfoCircle,
  FaCalendarAlt,
  FaSearch,
  FaFilter,
  FaLayerGroup,
  FaClipboardList
} from 'react-icons/fa';

const API_URL = import.meta.env.VITE_API_URL || 'http://127.0.0.1:8000';

const ManajemenKegiatan = () => {
  const [kegiatan, setKegiatan] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  
  // State untuk Expand/Collapse
  const [expandedRow, setExpandedRow] = useState(null); 
  
  // State untuk Filter & Search
  const [searchTerm, setSearchTerm] = useState('');
  const [filterYear, setFilterYear] = useState('');

  const navigate = useNavigate();

  // --- HELPER EKSTRAKSI DATA ---
  const getList = (response) => {
    if (response?.data) {
        if (Array.isArray(response.data)) return response.data;
        if (Array.isArray(response.data.data)) return response.data.data;
    }
    return [];
  };

  // 1. FETCH DATA
  const fetchKegiatan = async () => {
    setLoading(true);
    try {
      const token = localStorage.getItem('token'); 
      const config = token ? { headers: { Authorization: `Bearer ${token}` } } : {};

      const [resKeg, resSub] = await Promise.all([
        axios.get(`${API_URL}/api/kegiatan`, config).catch(() => ({ data: [] })),
        axios.get(`${API_URL}/api/subkegiatan`, config).catch(() => ({ data: [] }))
      ]);

      const allKegiatan = getList(resKeg);
      const allSubs = getList(resSub);
      
      // Mapping Data Induk ke Anak
      const mergedData = allKegiatan.map(k => {
         // Filter sub kegiatan milik induk ini
         const mySubs = allSubs.filter(sub => sub.id_kegiatan === k.id);
         
         // Hitung tahun aktif untuk opsi filter
         const activeYears = new Set();
         mySubs.forEach(sub => {
            if (sub.tanggal_mulai) {
                const y = new Date(sub.tanggal_mulai).getFullYear();
                if (!isNaN(y)) activeYears.add(y.toString());
            }
         });

         if (activeYears.size === 0 && k.created_at) {
             const createdYear = new Date(k.created_at).getFullYear();
             if (!isNaN(createdYear)) activeYears.add(createdYear.toString());
         }

         return {
             ...k,
             active_years: Array.from(activeYears),
             sub_list: mySubs 
         };
      });

      mergedData.sort((a, b) => b.id - a.id);
      setKegiatan(mergedData);
      setError(null);
    } catch (err) {
      console.error(err);
      setError("Gagal memuat daftar kegiatan.");
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchKegiatan();
  }, []);

  // --- LOGIKA FILTER & SEARCH (DIPERBARUI) ---
  const filteredKegiatan = useMemo(() => {
    return kegiatan
      .map(item => {
        // 1. Clone sub_list agar tidak memutasi state asli
        let filteredSubs = [...(item.sub_list || [])];

        // 2. Filter Sub-Kegiatan Berdasarkan Tahun (STRICT)
        if (filterYear) {
          filteredSubs = filteredSubs.filter(sub => {
             if (!sub.tanggal_mulai) return false;
             return new Date(sub.tanggal_mulai).getFullYear().toString() === filterYear;
          });
        }

        // 3. Filter Sub-Kegiatan Berdasarkan Search
        if (searchTerm) {
           const term = searchTerm.toLowerCase();
           const parentMatches = item.nama_kegiatan && item.nama_kegiatan.toLowerCase().includes(term);
           
           // Jika Parent TIDAK match, maka sub-kegiatan harus match keyword
           if (!parentMatches) {
              filteredSubs = filteredSubs.filter(sub => 
                 sub.nama_sub_kegiatan && sub.nama_sub_kegiatan.toLowerCase().includes(term)
              );
           }
           // Jika Parent MATCH, kita biarkan semua sub (yang sudah lolos filter tahun) tetap tampil
        }

        // Kembalikan item baru dengan sub_list yang sudah disaring
        return { ...item, sub_list: filteredSubs };
      })
      .filter(item => {
         // Tentukan apakah Induk/Survei ini harus tampil di daftar utama?

         // Syarat 1: Search Match
         const term = searchTerm.toLowerCase();
         const parentMatches = item.nama_kegiatan && item.nama_kegiatan.toLowerCase().includes(term);
         
         // Syarat 2: Punya Sub-Kegiatan (setelah difilter di atas)
         const hasSubs = item.sub_list && item.sub_list.length > 0;

         // Jika sedang Search:
         if (searchTerm) {
            // Tampil jika Induknya match ATAU salah satu anaknya match
            return parentMatches || hasSubs;
         }

         // Jika sedang Filter Tahun (tanpa search):
         if (filterYear) {
            // Tampil HANYA jika punya anak di tahun tersebut
            return hasSubs;
         }

         return true; // Tampilkan semua jika tidak ada filter
      });
  }, [kegiatan, searchTerm, filterYear]);

  // List Tahun untuk Dropdown (Berdasarkan data yang ada)
  const availableYears = useMemo(() => {
    const years = new Set();
    kegiatan.forEach(item => {
        const yrs = item.active_years || [];
        yrs.forEach(y => years.add(y));
    });
    return [...years].sort((a, b) => b - a);
  }, [kegiatan]);

  // --- HANDLERS ---
  const handleRowClick = (id) => {
    if (expandedRow === id) {
      setExpandedRow(null); 
    } else {
      setExpandedRow(id); 
    }
  };

  // --- HELPER UI ---
  const formatDateRange = (startDate, endDate) => {
    if (!startDate || !endDate) return '-';
    const options = { day: 'numeric', month: 'short', year: 'numeric' };
    const start = new Date(startDate).toLocaleDateString('id-ID', options);
    const end = new Date(endDate).toLocaleDateString('id-ID', options);
    return `${start} - ${end}`;
  };

  const getComputedStatus = (startDate, endDate) => {
    if (!startDate || !endDate) return { label: 'Jadwal Belum Lengkap', className: 'bg-gray-100 text-gray-500' };
    const now = new Date();
    const start = new Date(startDate);
    const end = new Date(endDate);
    
    now.setHours(0, 0, 0, 0);
    start.setHours(0, 0, 0, 0);
    end.setHours(23, 59, 59, 999);

    if (now < start) return { label: 'Akan Datang', className: 'bg-blue-100 text-blue-700' };
    if (now > end) return { label: 'Selesai', className: 'bg-green-100 text-green-700' };
    return { label: 'Sedang Proses', className: 'bg-yellow-100 text-yellow-700' };
  };

  if (loading) return <div className="text-center py-20 text-gray-500 animate-pulse">Memuat data...</div>;
  if (error) return <div className="text-center py-20 text-red-600">{error}</div>;

  return (
    <div className="w-full pt-8 px-4 container mx-auto max-w-6xl pb-20">
      
      {/* HEADER */}
      <div className="mb-8 animate-fade-in-up">
        <h1 className="text-2xl font-bold text-gray-800 flex items-center gap-2">
            <FaLayerGroup className="text-[#1A2A80]" /> Daftar Survei & Sensus
        </h1>
        <p className="text-gray-500 text-sm mt-1">
            Informasi lengkap mengenai kegiatan statistik yang tersedia.
        </p>
      </div>

      {/* FILTER & SEARCH */}
      <div className="bg-white p-4 rounded-xl shadow-sm border border-gray-100 mb-6 flex flex-col md:flex-row gap-4 items-center justify-between">
         <div className="relative w-full md:w-1/2">
            <FaSearch className="absolute left-3 top-3 text-gray-400" />
            <input
              type="text"
              placeholder="Cari Survei/Sensus atau Kegiatan..."
              className="w-full pl-10 pr-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-[#1A2A80] outline-none text-sm transition bg-gray-50 focus:bg-white"
              value={searchTerm}
              onChange={(e) => setSearchTerm(e.target.value)}
            />
         </div>
         <div className="flex items-center gap-3 w-full md:w-auto">
            <div className="flex items-center gap-2 text-gray-500 text-sm font-bold"><FaFilter /> Tahun:</div>
            <select
               className="px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-[#1A2A80] outline-none text-sm bg-white cursor-pointer"
               value={filterYear}
               onChange={(e) => setFilterYear(e.target.value)}
            >
               <option value="">Semua</option>
               {availableYears.map(year => <option key={year} value={year}>{year}</option>)}
            </select>
         </div>
      </div>

      {/* LIST SURVEI/SENSUS */}
      <div className="space-y-4">
        {kegiatan.length === 0 ? (
          <div className="text-center py-12 bg-gray-50 rounded-xl border border-dashed border-gray-300">
            <p className="text-gray-500 font-medium">Belum ada data Survei/Sensus.</p>
          </div>
        ) : filteredKegiatan.length === 0 ? (
          <div className="text-center py-12 bg-gray-50 rounded-xl border border-dashed border-gray-300">
            <p className="text-gray-500 font-medium">Tidak ditemukan data yang cocok.</p>
            <button onClick={() => {setSearchTerm(''); setFilterYear('')}} className="mt-2 text-[#1A2A80] text-sm underline hover:text-blue-800">Reset Filter</button>
          </div>
        ) : (
          filteredKegiatan.map((item) => {
            const isExpanded = expandedRow === item.id;
            
            return (
              <div key={item.id} className={`bg-white rounded-xl shadow-sm border transition-all duration-200 overflow-hidden ${isExpanded ? 'border-blue-300 ring-1 ring-blue-100' : 'border-gray-100 hover:border-blue-200'}`}>
                
                {/* Header Row (Induk) */}
                <div onClick={() => handleRowClick(item.id)} className={`px-6 py-4 cursor-pointer flex flex-col md:flex-row justify-between items-start md:items-center gap-4 transition-colors ${isExpanded ? 'bg-blue-50/30' : 'bg-white hover:bg-gray-50'}`}>
                  <div className="flex items-center gap-4 flex-1">
                    <div className={`p-2 rounded-full transition-transform duration-200 ${isExpanded ? 'bg-blue-100 text-[#1A2A80]' : 'text-gray-400 bg-gray-100'}`}>
                       {isExpanded ? <FaChevronUp size={12} /> : <FaChevronDown size={12} />}
                    </div>
                    <div>
                      <h3 className={`text-base font-bold transition-colors ${isExpanded ? 'text-[#1A2A80]' : 'text-gray-800'}`}>{item.nama_kegiatan}</h3>
                      <p className="text-xs text-gray-500 mt-0.5 line-clamp-1">{item.deskripsi || 'Tidak ada deskripsi.'}</p>
                    </div>
                  </div>
                  {/* Badge Jumlah Kegiatan (Update sesuai filter) */}
                  <div className="flex items-center">
                     <span className={`text-xs font-medium px-3 py-1 rounded-full ${isExpanded ? 'bg-blue-200 text-blue-800' : 'bg-gray-100 text-gray-500'}`}>
                        {item.sub_list ? item.sub_list.length : 0} Kegiatan
                     </span>
                  </div>
                </div>

                {/* Sub Kegiatan List (Accordion Content) */}
                {isExpanded && (
                  <div className="bg-gray-50/50 border-t border-gray-100 animate-fade-in-down">
                    {item.sub_list && item.sub_list.length > 0 ? (
                      <div className="overflow-x-auto p-4">
                        <table className="w-full text-left text-sm bg-white rounded-lg border border-gray-200 overflow-hidden">
                          <thead className="bg-gray-50 text-gray-500 uppercase text-xs font-bold border-b border-gray-200">
                            <tr>
                              <th className="px-4 py-3 w-1/3">Nama Kegiatan</th>
                              <th className="px-4 py-3">Jadwal Pelaksanaan</th>
                              <th className="px-4 py-3 text-center">Status</th>
                              <th className="px-4 py-3 text-right">Detail</th>
                            </tr>
                          </thead>
                          <tbody className="divide-y divide-gray-100">
                            {item.sub_list.map((sub) => {
                              const statusObj = getComputedStatus(sub.tanggal_mulai, sub.tanggal_selesai);
                              return (
                                <tr 
                                  key={sub.id} 
                                  className="hover:bg-blue-50 transition-colors group cursor-pointer"
                                  onClick={() => navigate(`/kegiatan/${sub.id}`)}
                                >
                                  <td className="px-4 py-3 font-medium text-gray-800">
                                    <div className="flex items-center gap-2">
                                        <FaClipboardList className="text-gray-300 group-hover:text-[#1A2A80] transition-colors" />
                                        {sub.nama_sub_kegiatan}
                                    </div>
                                  </td>
                                  <td className="px-4 py-3 text-gray-600 whitespace-nowrap text-xs">
                                    <div className="flex items-center gap-2">
                                        <FaCalendarAlt className="text-gray-400"/> 
                                        {formatDateRange(sub.tanggal_mulai, sub.tanggal_selesai)}
                                    </div>
                                  </td>
                                  <td className="px-4 py-3 text-center">
                                    <span className={`px-2.5 py-1 text-[10px] font-bold rounded-full uppercase tracking-wide shadow-sm ${statusObj.className}`}>{statusObj.label}</span>
                                  </td>
                                  <td className="px-4 py-3 text-right">
                                    <button 
                                        className="inline-flex items-center gap-1 text-[#1A2A80] bg-blue-50 hover:bg-blue-100 hover:text-blue-900 px-3 py-1.5 rounded-lg transition text-xs font-bold border border-blue-100"
                                    >
                                        <FaInfoCircle /> Info
                                    </button>
                                  </td>
                                </tr>
                              );
                            })}
                          </tbody>
                        </table>
                      </div>
                    ) : (
                      <div className="p-8 text-center text-gray-400 italic text-sm">
                        {filterYear ? `Tidak ada kegiatan di tahun ${filterYear}.` : 'Tidak ada kegiatan di bawah survei/sensus ini.'}
                      </div>
                    )}
                  </div>
                )}
              </div>
            );
          })
        )}
      </div>

    </div>
  );
};

export default ManajemenKegiatan;