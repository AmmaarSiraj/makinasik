// src/pages/Penugasan.jsx
import React, { useState, useEffect, useMemo } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import axios from 'axios';
import Swal from 'sweetalert2'; 
import * as XLSX from 'xlsx'; 
import { 
  FaDownload, 
  FaFileUpload, 
  FaPlus, 
  FaChevronDown, 
  FaUsers, 
  FaArrowRight,
  FaClipboardList,
  FaEdit,   
  FaTrash,
  FaSearch, 
  FaFilter,
  FaCheckCircle,
  FaUndoAlt,
  FaTimes,
  FaLayerGroup,
  FaCalendarAlt,
  FaBriefcase
} from 'react-icons/fa';

const API_URL = import.meta.env.VITE_API_URL || 'http://127.0.0.1:8000';
const getToken = () => localStorage.getItem('token');

const Penugasan = () => {
  const navigate = useNavigate();

  const [allPenugasan, setAllPenugasan] = useState([]); 
  const [isLoading, setIsLoading] = useState(true);

  const [searchTerm, setSearchTerm] = useState('');
  const [filterYear, setFilterYear] = useState('');

  const [expandedTaskId, setExpandedTaskId] = useState(null); 
  const [membersCache, setMembersCache] = useState({});
  const [loadingMembers, setLoadingMembers] = useState(false);

  const [processingGroup, setProcessingGroup] = useState(null);
  
  const [showImportModal, setShowImportModal] = useState(false);
  const [importKegiatanList, setImportKegiatanList] = useState([]);
  const [importSubList, setImportSubList] = useState([]);
  
  const [selectedKegiatanId, setSelectedKegiatanId] = useState('');
  const [selectedSubId, setSelectedSubId] = useState('');
  const [importFile, setImportFile] = useState(null);
  const [isPreviewing, setIsPreviewing] = useState(false);

  // --- HELPERS ---
  const formatDate = (dateString) => {
    if (!dateString) return '-';
    return new Date(dateString).toLocaleDateString('id-ID', { 
      day: 'numeric', 
      month: 'short', 
      year: 'numeric' 
    });
  };

  // --- API CALLS ---
  const fetchPenugasan = async () => {
    setIsLoading(true);
    try {
        const token = getToken();
        const config = { headers: { Authorization: `Bearer ${token}` } };
        
        const [resPenugasan, resKelompok] = await Promise.all([
            axios.get(`${API_URL}/api/penugasan`, config),
            axios.get(`${API_URL}/api/kelompok-penugasan`, config)
        ]);
        
        setAllPenugasan(resPenugasan.data.data); 

        const membersMap = {};
        const rawKelompok = resKelompok.data.data || resKelompok.data;

        if (Array.isArray(rawKelompok)) {
            rawKelompok.forEach(member => {
                const cleanMember = {
                    ...member,
                    nama_lengkap: member.nama_lengkap || member.nama_mitra, 
                };
                const pId = member.id_penugasan;
                if (!membersMap[pId]) {
                    membersMap[pId] = [];
                }
                membersMap[pId].push(cleanMember);
            });
        }
        setMembersCache(membersMap);

    } catch (err) {
        console.error("Gagal load data:", err);
    } finally {
        setIsLoading(false);
    }
  };

  useEffect(() => {
    fetchPenugasan();
  }, []);

  useEffect(() => {
    if (showImportModal) {
        axios.get(`${API_URL}/api/kegiatan`, { headers: { Authorization: `Bearer ${getToken()}` } })
            .then(res => setImportKegiatanList(res.data.data || res.data))
            .catch(err => console.error(err));
    }
  }, [showImportModal]);

  // --- HANDLERS ---
  const handleKegiatanChange = async (e) => {
    const kId = e.target.value;
    setSelectedKegiatanId(kId);
    setSelectedSubId('');
    setImportSubList([]);
    
    if (kId) {
        try {
            const res = await axios.get(`${API_URL}/api/subkegiatan/kegiatan/${kId}`, { 
                headers: { Authorization: `Bearer ${getToken()}` } 
            });
            setImportSubList(res.data.data || res.data);
        } catch (err) {
            console.error(err);
        }
    }
  };

  const handleProcessImport = async () => {
    if (!selectedSubId || !importFile) {
        Swal.fire('Error', 'Pilih kegiatan dan file terlebih dahulu!', 'error');
        return;
    }

    const formData = new FormData();
    formData.append('file', importFile);
    formData.append('id_subkegiatan', selectedSubId);

    setIsPreviewing(true);
    try {
        const token = getToken();
        const res = await axios.post(`${API_URL}/api/penugasan/preview-import`, formData, {
            headers: { 
                'Content-Type': 'multipart/form-data',
                Authorization: `Bearer ${token}` 
            }
        });

        const { valid_data, warnings, subkegiatan } = res.data;

        setShowImportModal(false);

        if (warnings.length > 0) {
            await Swal.fire({
                title: 'Peringatan Validasi Import',
                html: `
                    <div style="text-align:left; max-height: 200px; overflow-y:auto; font-size:12px;">
                        <p class="font-bold mb-2 text-red-600">${warnings.length} Baris Data Ditolak:</p>
                        <ul class="list-disc pl-4 text-gray-700">
                            ${warnings.map(w => `<li>${w}</li>`).join('')}
                        </ul>
                        <p class="mt-4 font-bold text-green-600">
                            ${valid_data.length} data valid siap diproses. Lanjutkan?
                        </p>
                    </div>
                `,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, Lanjutkan',
                cancelButtonText: 'Batal',
                width: '600px'
            }).then((result) => {
                if (result.isConfirmed && valid_data.length > 0) {
                    goToTambahPenugasan(subkegiatan, valid_data);
                }
            });
        } else {
            if (valid_data.length > 0) {
                goToTambahPenugasan(subkegiatan, valid_data);
            } else {
                Swal.fire('Info', 'Tidak ada data valid yang ditemukan dalam file.', 'info');
            }
        }

    } catch (err) {
        console.error(err);
        Swal.fire('Gagal', err.response?.data?.message || 'Terjadi kesalahan saat memproses file.', 'error');
    } finally {
        setIsPreviewing(false);
    }
  };

  const goToTambahPenugasan = (subkegiatanData, importedMembers) => {
    navigate('/penugasan/tambah', { 
        state: {
            preSelectedSubKegiatan: subkegiatanData,
            importedMembers: importedMembers
        }
    });
  };

  const availableYears = useMemo(() => {
    const years = new Set();
    allPenugasan.forEach(item => {
      if (item.tanggal_mulai) {
        const y = new Date(item.tanggal_mulai).getFullYear();
        if (!isNaN(y)) years.add(y);
      }
    });
    return [...years].sort((a, b) => b - a);
  }, [allPenugasan]);

  const groupedPenugasan = useMemo(() => {
    const filtered = allPenugasan.filter(item => {
      const term = searchTerm.toLowerCase();
      const matchSearch = 
        (item.nama_kegiatan || '').toLowerCase().includes(term) ||
        (item.nama_sub_kegiatan || '').toLowerCase().includes(term) ||
        (item.nama_pengawas || '').toLowerCase().includes(term);

      let matchYear = true;
      if (filterYear) {
        if (item.tanggal_mulai) {
          const y = new Date(item.tanggal_mulai).getFullYear();
          matchYear = y.toString() === filterYear.toString();
        } else {
          matchYear = false;
        }
      }
      return matchSearch && matchYear;
    });

    return filtered.reduce((acc, item) => {
      const key = item.nama_kegiatan;
      if (!acc[key]) acc[key] = [];
      acc[key].push(item);
      return acc;
    }, {});

  }, [allPenugasan, searchTerm, filterYear]);

  const toggleRow = async (id_penugasan) => {
    if (expandedTaskId === id_penugasan) {
      setExpandedTaskId(null);
      return;
    }
    setExpandedTaskId(id_penugasan);

    if (!membersCache[id_penugasan]) {
      setLoadingMembers(true);
      try {
        const token = getToken();
        const res = await axios.get(`${API_URL}/api/penugasan/${id_penugasan}/anggota`, {
          headers: { Authorization: `Bearer ${token}` }
        });
        setMembersCache(prev => ({ ...prev, [id_penugasan]: res.data }));
      } catch (err) {
        setMembersCache(prev => ({ ...prev, [id_penugasan]: [] }));
      } finally {
        setLoadingMembers(false);
      }
    }
  };

  const handleStatusChange = async (e, id, currentStatus) => {
    e.stopPropagation();
    const newStatus = currentStatus === 'disetujui' ? 'menunggu' : 'disetujui';

    try {
      setAllPenugasan(prev => prev.map(p => 
        p.id_penugasan === id ? { ...p, status_penugasan: newStatus } : p
      ));

      const token = getToken();
      await axios.put(`${API_URL}/api/penugasan/${id}`, 
        { status_penugasan: newStatus }, 
        { headers: { Authorization: `Bearer ${token}` } }
      );
      
    } catch (err) {
      console.error(err);
      Swal.fire('Gagal', `Gagal mengubah status.`, 'error');
      fetchPenugasan();
    }
  };

  const handleGroupStatusChange = async (subItems, groupName) => {
    const allApproved = subItems.every(item => item.status_penugasan === 'disetujui');
    const targetStatus = allApproved ? 'menunggu' : 'disetujui';
    const actionText = targetStatus === 'disetujui' ? 'Menyetujui Semua' : 'Membatalkan Semua';

    const result = await Swal.fire({
        title: `${actionText}?`,
        text: `Akan mengubah status ${subItems.length} penugasan dalam grup ini menjadi '${targetStatus}'.`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: targetStatus === 'disetujui' ? '#10B981' : '#F59E0B',
        confirmButtonText: `Ya, ${actionText}`
    });

    if (result.isConfirmed) {
        setProcessingGroup(groupName); 
        try {
            const token = getToken();
            const config = { headers: { Authorization: `Bearer ${token}` } };
            
            const promises = subItems.map(item => {
                if (item.status_penugasan !== targetStatus) {
                    return axios.put(`${API_URL}/api/penugasan/${item.id_penugasan}`, {
                        status_penugasan: targetStatus
                    }, config);
                }
                return Promise.resolve();
            });

            await Promise.all(promises);

            Swal.fire('Sukses', 'Status grup berhasil diperbarui!', 'success');
            fetchPenugasan();

        } catch (err) {
            console.error(err);
            Swal.fire('Error', 'Terjadi kesalahan saat memproses grup.', 'error');
        } finally {
            setProcessingGroup(null); 
        }
    }
  };

  const handleDelete = async (e, id) => {
    e.stopPropagation();
    const result = await Swal.fire({
      title: 'Hapus Penugasan?',
      text: "Data anggota dan plotting mitra di dalamnya akan ikut terhapus permanen.",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#d33',
      cancelButtonColor: '#3085d6',
      confirmButtonText: 'Ya, Hapus!'
    });

    if (result.isConfirmed) {
      try {
        const token = getToken();
        await axios.delete(`${API_URL}/api/penugasan/${id}`, {
          headers: { Authorization: `Bearer ${token}` }
        });
        Swal.fire('Terhapus!', 'Penugasan berhasil dihapus.', 'success');
        fetchPenugasan();
      } catch (err) {
        Swal.fire('Gagal!', 'Terjadi kesalahan saat menghapus.', 'error');
      }
    }
  };

  const handleEdit = (e, id) => {
    e.stopPropagation();
    navigate(`/penugasan/edit/${id}`);
  };

  const handleDownloadTemplate = () => {
    const rows = [
      { sobat_id: "337322040034", nama_lengkap: "Trian Yunita Hestiarini", posisi: "Petugas Pendataan Lapangan (PPL Survei)" },
      { sobat_id: "337322040036", nama_lengkap: "TRIYANI WIDYASTUTI", posisi: "Petugas Pendataan Lapangan (PPL Survei)" }
    ];
    const worksheet = XLSX.utils.json_to_sheet(rows);
    const workbook = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(workbook, worksheet, "Template");
    XLSX.writeFile(workbook, "template_import_penugasan.xlsx");
  };

  if (isLoading) return (
    <div className="flex flex-col items-center justify-center min-h-[50vh]">
        <div className="w-8 h-8 border-4 border-blue-200 border-t-[#1A2A80] rounded-full animate-spin mb-4"></div>
        <p className="text-gray-500 font-medium">Memuat data penugasan...</p>
    </div>
  );

  return (
    <div className="w-full mx-auto max-w-6xl space-y-8 pt-8 px-4 sm:px-6 pb-20">
      
      {/* === HEADER SECTION === */}
      <div className="flex flex-col md:flex-row justify-between items-start md:items-end gap-6">
        <div>
           <h1 className="text-2xl md:text-3xl font-extrabold text-gray-900 flex items-center gap-3">
              <FaClipboardList className="text-[#1A2A80]" /> Manajemen Penugasan
           </h1>
           <p className="text-gray-600 mt-2 text-sm leading-relaxed max-w-2xl">
              Kelola tim kerja, alokasi mitra, dan monitoring status penugasan untuk setiap survei dan sensus.
           </p>
        </div>

        <div className="flex flex-wrap gap-2">
           <button 
             onClick={handleDownloadTemplate} 
             className="group inline-flex items-center gap-2 px-4 py-2.5 bg-white border border-gray-200 rounded-xl text-gray-600 hover:text-[#1A2A80] hover:border-blue-200 hover:bg-blue-50 transition-all shadow-sm text-sm font-bold"
           >
              <FaDownload className="text-gray-400 group-hover:text-[#1A2A80]" /> Template
           </button>
           
           <button 
             onClick={() => setShowImportModal(true)} 
             className="inline-flex items-center gap-2 px-4 py-2.5 bg-green-600 hover:bg-green-700 text-white rounded-xl shadow-md hover:shadow-lg transition-all text-sm font-bold"
           >
              <FaFileUpload /> Import
           </button>
           
           <Link 
             to="/penugasan/tambah" 
             className="inline-flex items-center gap-2 px-4 py-2.5 bg-[#1A2A80] hover:bg-blue-900 text-white rounded-xl shadow-md hover:shadow-lg transition-all text-sm font-bold"
           >
              <FaPlus /> Buat Baru
           </Link>
        </div>
      </div>

      {/* === FILTER & SEARCH CARD === */}
      <div className="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex flex-col md:flex-row gap-4 items-center justify-between">
         <div className="relative w-full md:w-1/2">
            <FaSearch className="absolute left-4 top-3.5 text-gray-400" />
            <input
              type="text"
              placeholder="Cari Survei, Kegiatan, atau Pengawas..."
              className="w-full pl-11 pr-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#1A2A80] focus:border-transparent outline-none text-sm transition bg-gray-50 focus:bg-white"
              value={searchTerm}
              onChange={(e) => setSearchTerm(e.target.value)}
            />
         </div>
         <div className="flex items-center gap-3 w-full md:w-auto bg-gray-50 p-1.5 rounded-xl border border-gray-200">
            <div className="flex items-center gap-2 text-gray-500 text-xs font-bold px-3 uppercase tracking-wider">
               <FaFilter /> Tahun
            </div>
            <select
               className="px-4 py-2 border-0 rounded-lg focus:ring-0 outline-none text-sm bg-white shadow-sm cursor-pointer min-w-[100px] font-medium text-gray-700 hover:bg-gray-50 transition"
               value={filterYear}
               onChange={(e) => setFilterYear(e.target.value)}
            >
               <option value="">Semua</option>
               {availableYears.map(year => <option key={year} value={year}>{year}</option>)}
            </select>
         </div>
      </div>

      {/* === DATA CONTENT === */}
      <div className="space-y-6">
        {Object.keys(groupedPenugasan).length === 0 ? (
          <div className="text-center py-16 bg-white rounded-2xl border border-dashed border-gray-300">
            <div className="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-4 text-gray-300">
                <FaClipboardList size={24} />
            </div>
            <p className="text-gray-500 font-medium">
               {searchTerm || filterYear ? 'Tidak ditemukan data yang sesuai filter.' : 'Belum ada data penugasan.'}
            </p>
          </div>
        ) : (
          Object.entries(groupedPenugasan).map(([kegiatanName, subItems]) => {
            const allApproved = subItems.length > 0 && subItems.every(i => i.status_penugasan === 'disetujui');
            const isThisGroupProcessing = processingGroup === kegiatanName;

            return (
              <div key={kegiatanName} className="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-shadow duration-300">
                
                {/* --- Group Header --- */}
                <div className="bg-gray-50/50 px-6 py-5 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                   <div className="flex items-center gap-4">
                      <div className="p-2.5 bg-white border border-gray-200 rounded-lg text-[#1A2A80] shadow-sm">
                          <FaBriefcase />
                      </div>
                      <div>
                          <h2 className="text-base font-bold text-gray-900">{kegiatanName}</h2>
                          <div className="flex items-center gap-2 mt-1">
                              <span className="text-xs text-gray-500 font-medium bg-white px-2 py-0.5 rounded border border-gray-200">
                                  {subItems.length} Tim Terbentuk
                              </span>
                          </div>
                      </div>
                   </div>

                   <button 
                      onClick={() => handleGroupStatusChange(subItems, kegiatanName)}
                      disabled={isThisGroupProcessing || processingGroup !== null}
                      className={`flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold transition-all shadow-sm border
                        ${allApproved 
                          ? 'bg-amber-50 text-amber-600 border-amber-200 hover:bg-amber-100' 
                          : 'bg-green-50 text-green-600 border-green-200 hover:bg-green-100'}
                        disabled:opacity-50 disabled:cursor-not-allowed
                      `}
                   >
                      {isThisGroupProcessing ? 'Memproses...' : (
                        allApproved ? <><FaUndoAlt /> Batalkan Semua</> : <><FaCheckCircle /> Setujui Semua</>
                      )}
                   </button>
                </div>

                {/* --- List Items --- */}
                <div className="divide-y divide-gray-50">
                  {subItems.map((task) => {
                    const isOpen = expandedTaskId === task.id_penugasan;
                    const members = membersCache[task.id_penugasan] || [];
                    const membersCount = members.length;
                    const isApproved = task.status_penugasan === 'disetujui';

                    return (
                      <div key={task.id_penugasan} className="group/item transition-colors bg-white hover:bg-blue-50/20">
                        
                        {/* --- Clickable Row --- */}
                        <div 
                          onClick={() => toggleRow(task.id_penugasan)} 
                          className="px-6 py-5 cursor-pointer flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4"
                        >
                          <div className="flex-1">
                            <div className="flex items-center gap-3 mb-2">
                              <div className={`p-1.5 rounded-full transition-transform duration-300 border border-gray-200 bg-white ${isOpen ? 'rotate-180 text-[#1A2A80] border-blue-200 shadow-sm' : 'text-gray-400'}`}>
                                  <FaChevronDown size={10} />
                              </div>
                              <h3 className={`font-bold text-sm transition-colors ${isOpen ? 'text-[#1A2A80]' : 'text-gray-800'}`}>
                                  {task.nama_sub_kegiatan}
                              </h3>
                              
                              <span className={`text-[10px] px-2.5 py-0.5 rounded-md font-bold border uppercase tracking-wide
                                ${isApproved 
                                  ? 'bg-green-50 text-green-700 border-green-100' 
                                  : 'bg-gray-100 text-gray-500 border-gray-200'}
                              `}>
                                {task.status_penugasan || 'Menunggu'}
                              </span>
                            </div>
                            
                            <div className="pl-9 flex flex-wrap gap-x-6 gap-y-2 text-xs text-gray-500">
                              <span className="flex items-center gap-1.5 bg-gray-50 px-2 py-1 rounded border border-gray-100">
                                <FaCalendarAlt className="text-gray-400" /> 
                                {formatDate(task.tanggal_mulai)} - {formatDate(task.tanggal_selesai)}
                              </span>
                              <span className="flex items-center gap-1.5 px-2 py-1">
                                <span className="text-gray-400">Pengawas:</span>
                                <span className="font-bold text-gray-700">{task.nama_pengawas}</span>
                              </span>
                            </div>
                          </div>

                          {/* --- Actions --- */}
                          <div className="flex items-center gap-6 min-w-fit pl-9 sm:pl-0">
                              <div className="text-xs font-bold text-gray-400 group-hover/item:text-[#1A2A80] transition-colors flex items-center gap-2">
                                  <FaUsers className="text-lg" /> {membersCount} Anggota
                              </div>

                              <div className="flex items-center gap-2 border-l pl-4 border-gray-200">
                                  <button 
                                    onClick={(e) => handleStatusChange(e, task.id_penugasan, task.status_penugasan)}
                                    className={`p-2 rounded-lg transition-all shadow-sm border ${isApproved 
                                        ? 'bg-white text-amber-500 border-gray-200 hover:bg-amber-50 hover:border-amber-200' 
                                        : 'bg-white text-green-600 border-gray-200 hover:bg-green-50 hover:border-green-200'}`}
                                    title={isApproved ? "Batalkan" : "Setujui"}
                                  >
                                    {isApproved ? <FaUndoAlt size={14} /> : <FaCheckCircle size={14} />}
                                  </button>

                                  <button 
                                    onClick={(e) => handleEdit(e, task.id_penugasan)} 
                                    className="p-2 bg-white text-indigo-500 border border-gray-200 hover:border-indigo-200 hover:bg-indigo-50 rounded-lg transition-all shadow-sm" 
                                    title="Edit"
                                  >
                                      <FaEdit size={14} />
                                  </button>
                                  
                                  <button 
                                    onClick={(e) => handleDelete(e, task.id_penugasan)} 
                                    className="p-2 bg-white text-red-500 border border-gray-200 hover:border-red-200 hover:bg-red-50 rounded-lg transition-all shadow-sm" 
                                    title="Hapus"
                                  >
                                      <FaTrash size={14} />
                                  </button>
                              </div>
                          </div>
                        </div>
                        
                        {/* --- Expanded Details (Members) --- */}
                        {isOpen && (
                          <div className="bg-gray-50 border-t border-gray-100 px-6 py-6 pl-6 sm:pl-16 transition-all duration-300 ease-in-out">
                             <div className="flex justify-between items-center mb-5">
                                  <h4 className="text-xs font-bold text-gray-500 uppercase tracking-wider flex items-center gap-2">
                                      <FaLayerGroup /> Daftar Anggota Tim
                                  </h4>
                                  <Link 
                                      to={`/penugasan/detail/${task.id_penugasan}`} 
                                      className="group/link text-[#1A2A80] font-bold text-xs hover:underline flex items-center gap-2 bg-white px-4 py-2 rounded-lg border border-gray-200 shadow-sm hover:border-blue-200 transition"
                                  >
                                      Detail & Cetak SPK 
                                      <FaArrowRight size={10} className="group-hover/link:translate-x-1 transition-transform" />
                                  </Link>
                             </div>

                             {loadingMembers ? (
                               <div className="text-center py-4 flex flex-col items-center">
                                  <div className="w-6 h-6 border-2 border-gray-300 border-t-[#1A2A80] rounded-full animate-spin mb-2"></div>
                                  <p className="text-gray-400 text-xs italic">Memuat anggota...</p>
                               </div>
                             ) : (
                               members.length === 0 ? (
                                 <div className="text-center py-8 bg-white rounded-xl border border-dashed border-gray-200 text-gray-400 text-xs">
                                   Belum ada anggota yang ditambahkan ke tim ini.
                                 </div>
                               ) : (
                                 <ul className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                   {members.map((m, idx) => (
                                     <li key={m.id_mitra || idx} className="flex items-center gap-4 bg-white px-4 py-3 rounded-xl border border-gray-200 shadow-sm hover:border-blue-200 transition-colors">
                                       <div className="w-10 h-10 rounded-full bg-gradient-to-br from-blue-50 to-indigo-50 border border-blue-100 flex items-center justify-center text-[#1A2A80] text-sm font-extrabold shadow-inner">
                                          {m.nama_lengkap ? m.nama_lengkap.charAt(0) : '?'}
                                       </div>
                                       <div className="overflow-hidden w-full">
                                          <div className="flex justify-between items-start w-full gap-2">
                                              <p className="text-gray-800 font-bold text-xs truncate" title={m.nama_lengkap}>
                                                  {m.nama_lengkap || m.nama_mitra || 'Nama Tidak Tersedia'}
                                              </p>
                                              {m.volume_tugas > 0 && (
                                                  <span className="text-[10px] font-bold bg-blue-50 text-[#1A2A80] px-1.5 py-0.5 rounded border border-blue-100 whitespace-nowrap">
                                                      Vol: {m.volume_tugas}
                                                  </span>
                                              )}
                                          </div>
                                          <p className="text-[11px] text-gray-400 truncate mt-0.5">
                                              {m.nama_jabatan || '-'}
                                          </p>
                                       </div>
                                     </li>
                                   ))}
                                 </ul>
                               )
                             )}
                          </div>
                        )}
                      </div>
                    )
                  })}
                </div>
              </div>
            );
          })
        )}
      </div>

      {/* === IMPORT MODAL === */}
      {showImportModal && (
        <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/50 backdrop-blur-sm transition-opacity">
            <div className="bg-white rounded-2xl shadow-2xl w-full max-w-lg overflow-hidden animate-fade-in-up">
                <div className="bg-[#1A2A80] px-6 py-4 flex justify-between items-center">
                    <h3 className="font-bold text-white text-lg flex items-center gap-2">
                        <FaFileUpload className="text-blue-200" /> Import Data
                    </h3>
                    <button onClick={() => setShowImportModal(false)} className="text-white/70 hover:text-white transition-colors">
                        <FaTimes size={18} />
                    </button>
                </div>

                <div className="p-6 space-y-5">
                    <div>
                        <label className="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Pilih Survei/Sensus (Induk)</label>
                        <select 
                            className="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-[#1A2A80] focus:border-transparent outline-none transition"
                            value={selectedKegiatanId}
                            onChange={handleKegiatanChange}
                        >
                            <option value="">-- Pilih Kegiatan Induk --</option>
                            {importKegiatanList.map(k => (
                                <option key={k.id} value={k.id}>{k.nama_kegiatan}</option>
                            ))}
                        </select>
                    </div>

                    <div>
                        <label className="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Pilih Kegiatan (Sub)</label>
                        <select 
                            className="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-[#1A2A80] focus:border-transparent outline-none transition disabled:bg-gray-100 disabled:text-gray-400"
                            value={selectedSubId}
                            onChange={(e) => setSelectedSubId(e.target.value)}
                            disabled={!selectedKegiatanId}
                        >
                            <option value="">-- Pilih Sub Kegiatan --</option>
                            {importSubList.map(sub => (
                                <option key={sub.id} value={sub.id}>{sub.nama_sub_kegiatan}</option>
                            ))}
                        </select>
                    </div>

                    <div className="border-2 border-dashed border-gray-300 rounded-xl p-8 text-center hover:bg-blue-50 hover:border-blue-300 transition-colors relative group">
                        <input 
                            type="file" 
                            accept=".csv, .xlsx, .xls"
                            onChange={(e) => setImportFile(e.target.files[0])}
                            className="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10"
                        />
                        <div className="space-y-3 pointer-events-none">
                            <div className="w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center mx-auto group-hover:bg-blue-100 transition-colors">
                                <FaFileUpload className="text-gray-400 text-xl group-hover:text-[#1A2A80]" />
                            </div>
                            {importFile ? (
                                <div>
                                    <p className="text-sm font-bold text-[#1A2A80]">{importFile.name}</p>
                                    <p className="text-xs text-green-600 mt-1">File siap diunggah</p>
                                </div>
                            ) : (
                                <div>
                                    <p className="text-sm font-medium text-gray-600">Klik atau tarik file Excel/CSV</p>
                                    <p className="text-xs text-gray-400 mt-1">Format: .xlsx, .xls, .csv</p>
                                </div>
                            )}
                        </div>
                    </div>
                </div>

                <div className="p-5 bg-gray-50 flex justify-end gap-3 border-t border-gray-100">
                    <button 
                        onClick={() => setShowImportModal(false)} 
                        className="px-5 py-2.5 rounded-xl text-gray-600 hover:bg-gray-200 text-sm font-bold transition-colors"
                    >
                        Batal
                    </button>
                    <button 
                        onClick={handleProcessImport} 
                        disabled={isPreviewing || !selectedSubId || !importFile}
                        className="px-5 py-2.5 rounded-xl bg-[#1A2A80] text-white hover:bg-blue-900 text-sm font-bold disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2 shadow-md transition-all"
                    >
                        {isPreviewing && <span className="animate-spin w-4 h-4 border-2 border-white/30 border-t-white rounded-full"></span>}
                        {isPreviewing ? 'Memproses...' : 'Proses Import'}
                    </button>
                </div>
            </div>
        </div>
      )}

    </div>
  );
};

export default Penugasan;