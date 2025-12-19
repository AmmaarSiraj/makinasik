import React, { useState, useEffect, useMemo } from 'react';
import axios from 'axios';
import Swal from 'sweetalert2'; 
import { 
  FaSearch, 
  FaUserPlus, 
  FaTimes, 
  FaIdCard, 
  FaBriefcase,
  FaExclamationCircle,
  FaBoxOpen,
  FaFilter,
  FaChartPie
} from 'react-icons/fa';

const API_URL = import.meta.env.VITE_API_URL || 'http://127.0.0.1:8000';
const getToken = () => localStorage.getItem('token');

const PopupTambahAnggotaPerencanaan = ({ 
  isOpen, 
  onClose, 
  id_perencanaan, // Menerima ID Perencanaan
  existingAnggotaIds, 
  onAnggotaAdded,
  targetYear,     
  idSubKegiatan   
}) => {
  const [allMitra, setAllMitra] = useState([]);
  const [availableJobs, setAvailableJobs] = useState([]); 
  const [currentTeamData, setCurrentTeamData] = useState([]); 
  
  const [selectedJob, setSelectedJob] = useState(''); 
  const [volume, setVolume] = useState(1); 
  const [searchTerm, setSearchTerm] = useState('');
  
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);

  useEffect(() => {
    if (isOpen && id_perencanaan && targetYear && idSubKegiatan) {
      const initData = async () => {
        setLoading(true);
        setError(null);
        try {
          const token = getToken();
          const headers = { Authorization: `Bearer ${token}` };

          // 1. Fetch data khusus untuk Perencanaan
          const [resAnggotaTim, resMitra, resHonor] = await Promise.all([
            axios.get(`${API_URL}/api/perencanaan/${id_perencanaan}/anggota`, { headers }),
            axios.get(`${API_URL}/api/mitra/aktif?tahun=${targetYear}`, { headers }),
            axios.get(`${API_URL}/api/honorarium`, { headers })
          ]);

          setCurrentTeamData(resAnggotaTim.data || []);

          let rawMitra = resMitra.data.data;
          if (rawMitra && !Array.isArray(rawMitra) && Array.isArray(rawMitra.data)) {
              rawMitra = rawMitra.data;
          }
          const mitraArray = Array.isArray(rawMitra) ? rawMitra : [];
          setAllMitra(mitraArray);

          const honorList = resHonor.data.data || [];
          const validHonors = honorList.filter(h => String(h.id_subkegiatan) === String(idSubKegiatan));
          
          const jobs = validHonors.map(h => ({
            kode: h.kode_jabatan,
            nama: h.jabatan ? h.jabatan.nama_jabatan : h.kode_jabatan, 
            tarif: h.tarif,
            satuan: h.nama_satuan || 'Kegiatan',
            basis_volume: Number(h.basis_volume) || 0 
          }));

          setAvailableJobs(jobs);

          if (jobs.length > 0) {
            setSelectedJob(jobs[0].kode);
          }
          setVolume(1);

        } catch (err) {
          console.error(err);
          setError('Gagal memuat data. Pastikan koneksi aman.');
        } finally {
          setLoading(false);
        }
      };

      initData();
    }
  }, [isOpen, id_perencanaan, targetYear, idSubKegiatan]);

  const quotaInfo = useMemo(() => {
    if (!selectedJob) return { sisa: 0, total: 0, terpakai: 0 };

    const jobInfo = availableJobs.find(j => j.kode === selectedJob);
    if (!jobInfo) return { sisa: 0, total: 0, terpakai: 0 };

    const totalQuota = jobInfo.basis_volume;
    
    const usedQuota = currentTeamData
      .filter(m => m.kode_jabatan === selectedJob)
      .reduce((acc, curr) => acc + (Number(curr.volume_tugas) || 0), 0);

    return {
        total: totalQuota,
        terpakai: usedQuota,
        sisa: Math.max(0, totalQuota - usedQuota)
    };
  }, [selectedJob, availableJobs, currentTeamData]);

  const availableMitra = useMemo(() => {
    if (!Array.isArray(allMitra)) return [];

    const excludedIds = new Set(
        (existingAnggotaIds || []).map(id => String(id))
    );

    const term = searchTerm.toLowerCase();

    return allMitra.filter(mitra => {
        const notInTeam = !excludedIds.has(String(mitra.id));
        const matchSearch =
        mitra.nama_lengkap.toLowerCase().includes(term) ||
        (mitra.nik && mitra.nik.includes(term));

        return notInTeam && matchSearch;
    });
  }, [allMitra, existingAnggotaIds, searchTerm]);

  const handleAddAnggota = async (id_mitra) => {
      if (!selectedJob) {
        Swal.fire('Peringatan', 'Harap pilih posisi/jabatan terlebih dahulu!', 'warning');
        return;
      }
      
      const finalVolume = (!volume || volume <= 0) ? 1 : volume;

      if (quotaInfo.total > 0 && finalVolume > quotaInfo.sisa) {
          Swal.fire({
              title: 'Kuota Tidak Cukup',
              text: `Sisa kuota: ${quotaInfo.sisa}. Anda input: ${finalVolume}.`,
              icon: 'warning'
          });
          return;
      }

      try {
        const token = getToken();
        
        // PAYLOAD DISESUAIKAN UNTUK PERENCANAAN
        const payload = { 
            id_perencanaan: id_perencanaan, // Key khusus perencanaan
            id_mitra: id_mitra,
            kode_jabatan: selectedJob,
            volume_tugas: finalVolume 
        };

        // POST KE ENDPOINT KELOMPOK PERENCANAAN
        await axios.post(`${API_URL}/api/kelompok-perencanaan`, payload, { 
          headers: { Authorization: `Bearer ${token}` } 
        });
        
        const mitraBaru = allMitra.find(m => m.id === id_mitra);
        setCurrentTeamData(prev => [
            ...prev, 
            { 
                id_mitra, 
                kode_jabatan: selectedJob, 
                volume_tugas: finalVolume,
                nama_lengkap: mitraBaru?.nama_lengkap 
            }
        ]);

        Swal.fire({
          title: 'Berhasil!',
          text: `${mitraBaru?.nama_lengkap} ditambahkan ke perencanaan.`,
          icon: 'success',
          timer: 1000,
          showConfirmButton: false,
          toast: true,
          position: 'top-end'
        });

        if (onAnggotaAdded) onAnggotaAdded();
        
      } catch (err) {
        console.error(err);
        const msg = err.response?.data?.message || err.response?.data?.error || 'Gagal menambahkan anggota.';
        Swal.fire('Gagal', msg, 'error');
      }
  };

  if (!isOpen) return null;

  return (
    <div className="fixed inset-0 bg-black/60 flex justify-center items-center z-50 p-4 backdrop-blur-sm transition-opacity">
      <div className="bg-white rounded-2xl shadow-2xl w-full max-w-lg max-h-[90vh] flex flex-col overflow-hidden border border-gray-200 animate-fade-in-up">
        
        <div className="flex justify-between items-center p-5 bg-teal-600 text-white">
          <h2 className="text-lg font-bold flex items-center gap-2">
            <FaUserPlus className="text-teal-200" /> Tambah Anggota Perencanaan
          </h2>
          <button 
            onClick={onClose} 
            className="text-white/70 hover:text-white hover:bg-white/10 p-2 rounded-full transition"
          >
            <FaTimes size={18} />
          </button>
        </div>

        <div className="px-5 pt-5 pb-3 bg-white space-y-4">
            <div>
                <label className="block text-xs font-bold text-gray-500 uppercase mb-2 flex items-center gap-2">
                    <FaBriefcase className="text-teal-600" /> Pilih Posisi
                </label>
                
                {loading ? (
                    <div className="h-10 bg-gray-100 rounded animate-pulse w-full"></div>
                ) : availableJobs.length === 0 ? (
                    <div className="p-3 bg-red-50 text-red-600 text-sm rounded border border-red-100 flex items-center gap-2">
                        <FaExclamationCircle /> 
                        <span>Tidak ada jabatan yang diatur.</span>
                    </div>
                ) : (
                    <select
                        value={selectedJob}
                        onChange={(e) => setSelectedJob(e.target.value)}
                        className="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-teal-600 outline-none text-sm font-medium bg-gray-50 focus:bg-white transition"
                    >
                        {availableJobs.map((job) => (
                            <option key={job.kode} value={job.kode}>
                                {job.nama} (Rp {Number(job.tarif).toLocaleString('id-ID')}) - Kuota: {job.basis_volume}
                            </option>
                        ))}
                    </select>
                )}
            </div>

            <div>
                <div className="flex justify-between items-center mb-2">
                    <label className="block text-xs font-bold text-gray-500 uppercase flex items-center gap-2">
                        <FaBoxOpen className="text-teal-600" /> Volume Tugas
                    </label>
                    {selectedJob && (
                        <div className={`text-[10px] px-2 py-0.5 rounded-full font-bold flex items-center gap-1 border ${quotaInfo.sisa === 0 ? 'bg-red-50 text-red-600 border-red-200' : 'bg-green-50 text-green-700 border-green-200'}`}>
                            <FaChartPie />
                            Sisa Kuota: {quotaInfo.sisa} / {quotaInfo.total}
                        </div>
                    )}
                </div>
                
                <div className="flex items-center">
                    <input 
                        type="number" 
                        min="1"
                        max={quotaInfo.total > 0 ? quotaInfo.sisa : undefined}
                        value={volume}
                        onChange={(e) => {
                            const val = e.target.value;
                            const intVal = parseInt(val);
                            if (val !== '' && quotaInfo.total > 0 && intVal > quotaInfo.sisa) {
                                return; 
                            }
                            setVolume(val === '' ? '' : intVal);
                        }}
                        className={`w-full px-4 py-2.5 border rounded-l-xl focus:ring-2 outline-none text-sm font-bold transition ${quotaInfo.sisa === 0 ? 'bg-gray-100 text-gray-400 border-gray-200' : 'bg-gray-50 focus:bg-white border-gray-300 focus:ring-teal-600'}`}
                        placeholder="1"
                        disabled={quotaInfo.sisa === 0 && quotaInfo.total > 0}
                    />
                    <div className="bg-gray-100 border border-l-0 border-gray-300 px-4 py-2.5 rounded-r-xl text-sm font-medium text-gray-500">
                        Unit
                    </div>
                </div>
            </div>
        </div>

        <div className="px-5 py-3 border-b border-t border-gray-100 bg-gray-50/50">
          {targetYear && (
            <div className="mb-2">
                <span className="inline-flex items-center gap-1 bg-teal-100 text-teal-800 text-[10px] px-2 py-1 rounded border border-teal-200 font-bold shadow-sm">
                    <FaFilter size={10} /> Mitra Aktif Thn {targetYear}
                </span>
            </div>
          )}

          <div className="relative">
            <span className="absolute left-4 top-3 text-gray-400">
                <FaSearch />
            </span>
            <input
                type="text"
                placeholder="Cari nama atau NIK mitra..."
                className="w-full pl-11 pr-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-teal-600 outline-none text-sm transition bg-white"
                value={searchTerm}
                onChange={(e) => setSearchTerm(e.target.value)}
            />
          </div>
        </div>

        <div className="overflow-y-auto flex-grow p-4 bg-gray-50 space-y-3">
          {loading && <p className="text-center py-8 text-gray-500 italic">Memuat data...</p>}
          {error && <p className="text-center py-8 text-red-500 text-sm">{error}</p>}
          
          {!loading && !error && (
            <>
                {availableMitra.length === 0 ? (
                    <div className="text-center py-10 text-gray-400 flex flex-col items-center">
                        <FaSearch size={30} className="mb-2 opacity-20" />
                        <p className="text-sm">Tidak ada mitra yang cocok.</p>
                    </div>
                ) : (
                    availableMitra.map(mitra => (
                        <div 
                            key={mitra.id} 
                            className="bg-white p-3 rounded-xl shadow-sm border border-gray-100 flex justify-between items-center hover:border-teal-300 transition-colors group"
                        >
                            <div className="flex items-center gap-3 overflow-hidden">
                                <div className="w-10 h-10 rounded-full bg-teal-50 flex-shrink-0 flex items-center justify-center text-teal-700 font-bold text-sm border border-teal-100">
                                    {mitra.nama_lengkap.charAt(0)}
                                </div>
                                <div className="min-w-0">
                                    <p className="font-bold text-gray-800 text-sm truncate group-hover:text-teal-700 transition-colors">
                                        {mitra.nama_lengkap}
                                    </p>
                                    <p className="text-xs text-gray-500 font-mono flex items-center gap-1">
                                        <FaIdCard className="text-gray-300" /> {mitra.nik}
                                    </p>
                                </div>
                            </div>
                            
                            <button
                                onClick={() => handleAddAnggota(mitra.id)}
                                disabled={!selectedJob || (quotaInfo.sisa === 0 && quotaInfo.total > 0)}
                                className={`text-xs font-bold py-2 px-4 rounded-lg shadow-sm flex items-center gap-2 flex-shrink-0 transition 
                                  ${selectedJob && (quotaInfo.sisa > 0 || quotaInfo.total === 0)
                                    ? 'bg-teal-600 hover:bg-teal-700 text-white hover:shadow' 
                                    : 'bg-gray-200 text-gray-400 cursor-not-allowed'}`}
                            >
                                <FaUserPlus /> Tambah
                            </button>
                        </div>
                    ))
                )}
            </>
          )}
        </div>
      </div>
    </div>
  );
};

export default PopupTambahAnggotaPerencanaan;