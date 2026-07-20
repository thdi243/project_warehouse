import { useEffect, useState } from "react";
import MidSearch from "../components/MidSearch";
import Countdown from "../components/CountdownTimer";
import BookingSummary from "../components/BookingSummary";
import { useBookingManager } from "../hooks/useBookingManager";
import SignatureModal from "@/components/SignatureModal";
import Swal from "sweetalert2";
import { useAuth } from "@/context/AuthContext";

import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Card, CardHeader, CardContent, CardTitle } from "@/components/ui/card";
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from "@/components/ui/select";
import { CalendarDays, Upload, Info, AlertCircle } from "lucide-react";
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
    DialogFooter,
} from "@/components/ui/dialog";

export default function PurchaseRequisitionForm() {
    const { user } = useAuth();
    const {
        items,
        expiredAt,
        addItem,
        removeItem,
        handleExpired,
        clearItems,
        getSessionId,
    } = useBookingManager();

    const [showSignature, setShowSignature] = useState(false);
    const [showStockReview, setShowStockReview] = useState(false);
    const [stockReviewItems, setStockReviewItems] = useState([]);
    const [noStockItems, setNoStockItems] = useState([]);
    const [reviewError, setReviewError] = useState("");

    const [form, setForm] = useState({
        pr_date: "",
        requested_by: "",
        department: "",
        jenis: "Barang",
        detail_jenis: "",
        no_io: "",
    });

    const [currentItem, setCurrentItem] = useState({
        mid: "",
        nama_barang: "",
        qty: "",
        keterangan: "",
        uom: "",
        desc: "",
    });

    const [loading, setLoading] = useState(false);

    const getTodayDate = () => {
        const today = new Date();
        const y = today.getFullYear();
        const m = String(today.getMonth() + 1).padStart(2, "0");
        const d = String(today.getDate()).padStart(2, "0");
        return `${y}-${m}-${d}`;
    };

    const handleExcelUpload = async (e) => {
        const file = e.target.files[0];
        if (!file) return;

        e.target.value = '';

        Swal.fire({
            title: "Memproses File...",
            text: "Membaca dan memvalidasi stok barang",
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        const formData = new FormData();
        formData.append("file", file);

        try {
            const res = await fetch("/api/purchase-requesition/upload-excel", {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content"),
                    "X-Session-Id": getSessionId()
                },
                body: formData
            });

            const data = await res.json();
            if (!res.ok) throw new Error(data.message || "Gagal mengupload file");

            Swal.close();

            const allItems = data.items || [];
            
            const invalidItems = allItems.filter(item => item.error);
            if (invalidItems.length > 0) {
                await Swal.fire({
                    icon: "warning",
                    title: "Beberapa MID Tidak Valid",
                    html: `Ditemukan ${invalidItems.length} barang dengan MID tidak terdaftar. Barang-barang ini akan diabaikan:<br><br>
                    <div class="text-left text-xs bg-gray-100 p-2 rounded max-h-40 overflow-y-auto font-mono font-semibold">
                        ${invalidItems.map(item => `${item.mid}: ${item.error}`).join("<br>")}
                    </div>`,
                    confirmButtonText: "Mengerti",
                    confirmButtonColor: "#f59e0b"
                });
            }

            const validItems = allItems.filter(item => !item.error);
            if (validItems.length === 0) {
                Swal.fire({
                    icon: "info",
                    title: "Informasi",
                    text: "Tidak ada barang valid yang dapat ditambahkan dari Excel.",
                    confirmButtonText: "OK",
                    confirmButtonColor: "#3b82f6"
                });
                return;
            }

            const hasStock = validItems.filter(item => item.available_qty > 0);
            const noStock = validItems.filter(item => item.available_qty <= 0);

            const configuredStockItems = hasStock.map(item => ({
                ...item,
                action: 'pr',
                alasan: ""
            }));

            setNoStockItems(noStock);

            if (configuredStockItems.length > 0) {
                setStockReviewItems(configuredStockItems);
                setShowStockReview(true);
            } else {
                Swal.fire({
                    title: "Tambahkan Barang",
                    text: `Menambahkan ${noStock.length} barang (tidak memiliki stok di gudang) langsung ke daftar PR?`,
                    icon: "question",
                    showCancelButton: true,
                    confirmButtonColor: "#10b981",
                    cancelButtonColor: "#6c757d",
                    confirmButtonText: "Ya, Tambahkan",
                    cancelButtonText: "Batal"
                }).then(async (result) => {
                    if (result.isConfirmed) {
                        await addParsedItems(noStock, []);
                    }
                });
            }
        } catch (err) {
            Swal.fire({
                icon: "error",
                title: "Upload Gagal",
                text: err.message || "Terjadi kesalahan saat mengupload excel.",
                confirmButtonText: "OK",
                confirmButtonColor: "#ef4444"
            });
        }
    };

    const addParsedItems = async (itemsNoStock, itemsWithStockConfigured) => {
        Swal.fire({
            title: "Menambahkan Barang...",
            text: "Menyimpan booking dan mendaftarkan barang ke PR",
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        let itemsToAdd = [];

        for (const item of itemsNoStock) {
            itemsToAdd.push({
                mid: item.mid,
                nama_barang: item.nama_barang,
                qty: item.qty,
                keterangan: item.keterangan,
                uom: item.uom,
                desc: item.desc || "",
                jenis: "pr",
                alasan: "",
            });
        }

        for (const item of itemsWithStockConfigured) {
            if (item.action === 'exclude') {
                continue;
            }

            if (item.action === 'pr') {
                itemsToAdd.push({
                    mid: item.mid,
                    nama_barang: item.nama_barang,
                    qty: item.qty,
                    keterangan: item.keterangan,
                    uom: item.uom,
                    desc: item.desc || "",
                    jenis: "pr",
                    alasan: item.alasan || "Dibutuhkan operasional (Excel)",
                });
            } else if (item.action === 'both') {
                if (item.qty > item.available_qty) {
                    itemsToAdd.push({
                        mid: item.mid,
                        nama_barang: item.nama_barang,
                        qty: item.qty - item.available_qty,
                        keterangan: item.keterangan,
                        uom: item.uom,
                        desc: item.desc || "",
                        jenis: "pr",
                        alasan: item.alasan || "Dibutuhkan operasional (Excel)",
                    });
                }
                itemsToAdd.push({
                    mid: item.mid,
                    nama_barang: item.nama_barang,
                    qty: Math.min(item.qty, item.available_qty),
                    keterangan: item.keterangan,
                    uom: item.uom,
                    desc: item.desc || "",
                    jenis: "blocked",
                    alasan: "",
                });
            } else if (item.action === 'reserve') {
                itemsToAdd.push({
                    mid: item.mid,
                    nama_barang: item.nama_barang,
                    qty: Math.min(item.qty, item.available_qty),
                    keterangan: item.keterangan,
                    uom: item.uom,
                    desc: item.desc || "",
                    jenis: "blocked",
                    alasan: "",
                });
            }
        }

        if (itemsToAdd.length === 0) {
            Swal.close();
            Swal.fire({
                icon: "info",
                title: "Selesai",
                text: "Tidak ada barang yang ditambahkan ke list PR.",
                confirmButtonText: "OK",
                confirmButtonColor: "#3b82f6"
            });
            return;
        }

        let allSuccess = true;
        for (const itemToAdd of itemsToAdd) {
            const success = await addItem(itemToAdd, itemToAdd.jenis, false, false);
            if (!success) {
                allSuccess = false;
                break;
            }
        }

        Swal.close();

        if (allSuccess) {
            Swal.fire({
                icon: "success",
                title: "Berhasil!",
                text: `Berhasil menambahkan ${itemsToAdd.length} barang ke list PR.`,
                confirmButtonText: "OK",
                confirmButtonColor: "#10b981",
            });
        } else {
            Swal.fire({
                icon: "warning",
                title: "Selesai dengan Catatan",
                text: "Beberapa barang mungkin gagal ditambahkan karena kendala stok/booking.",
                confirmButtonText: "OK",
                confirmButtonColor: "#f59e0b",
            });
        }
    };

    const handleAddItem = async () => {
        const isJasa = form.jenis === "Jasa";

        if (isJasa) {
            if (!currentItem.desc || !currentItem.qty || !currentItem.keterangan) {
                Swal.fire({
                    icon: "error",
                    title: "Error!",
                    text: "Harap lengkapi semua field jasa sebelum menambahkan.",
                    confirmButtonText: "OK",
                    confirmButtonColor: "#ef4444",
                });
                return;
            }
        } else {
            if (!currentItem.mid || !currentItem.qty || !currentItem.keterangan) {
                Swal.fire({
                    icon: "error",
                    title: "Error!",
                    text: "Harap lengkapi semua field barang sebelum menambahkan.",
                    confirmButtonText: "OK",
                    confirmButtonColor: "#ef4444",
                });
                return;
            }
        }

        const requestedQty = parseInt(currentItem.qty) || 0;
        if (requestedQty <= 0) {
            Swal.fire({
                icon: "error",
                title: "Error!",
                text: "Jumlah (Qty) harus minimal 1.",
                confirmButtonText: "OK",
                confirmButtonColor: "#ef4444",
            });
            return;
        }
        const availableQty = parseInt(currentItem.available_qty) || 0;

        let itemsToAdd = [];

        if (isJasa) {
            itemsToAdd.push({
                ...currentItem,
                qty: requestedQty,
                jenis: "pr",
                alasan: "",
            });
        } else if (availableQty > 0) {
            if (requestedQty > availableQty) {
                const result = await Swal.fire({
                    title: "Stok Tersedia!",
                    html: `
                        <div class="mb-4">
                            Barang ini memiliki stok ${availableQty}, sedangkan Anda meminta ${requestedQty}. Pilih tindakan yang diinginkan:
                        </div>
                        <div class="flex flex-col gap-2">
                            <button id="btn-option-pr" type="button" class="w-full py-2.5 px-4 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-md transition duration-150">
                                Naikkan PR (Semua Qty)
                            </button>
                            <button id="btn-option-both" type="button" class="w-full py-2.5 px-4 text-sm font-medium text-white bg-amber-500 hover:bg-amber-600 rounded-md transition duration-150">
                                Naikkan PR+ Reservasi (SAP) 
                            </button>
                            <button id="btn-option-reserve" type="button" class="w-full py-2.5 px-4 text-sm font-medium text-white bg-red-600 hover:bg-red-700 rounded-md transition duration-150">
                                Hanya Reservasi (SAP)
                            </button>
                            <button id="btn-option-cancel" type="button" class="w-full py-2.5 px-4 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-md transition duration-150 mt-2">
                                Batal
                            </button>
                        </div>
                    `,
                    icon: "question",
                    showConfirmButton: false,
                    showDenyButton: false,
                    showCancelButton: false,
                    allowOutsideClick: false,
                    didOpen: () => {
                        const content = Swal.getHtmlContainer();
                        if (content) {
                            content.querySelector('#btn-option-pr').addEventListener('click', () => {
                                Swal.clickConfirm();
                            });
                            content.querySelector('#btn-option-both').addEventListener('click', () => {
                                Swal.clickDeny();
                            });
                            content.querySelector('#btn-option-reserve').addEventListener('click', () => {
                                Swal.clickCancel();
                            });
                            content.querySelector('#btn-option-cancel').addEventListener('click', () => {
                                Swal.close();
                            });
                        }
                    }
                });

                if (result.isConfirmed) {
                    // Option 1: Naikkan PR (Semua Qty)
                    const reasonResult = await Swal.fire({
                        title: "Alasan Naik PR",
                        input: "text",
                        inputPlaceholder: "Wajib mengisi alasan...",
                        inputValidator: (value) => {
                            if (!value) {
                                return "Alasan wajib diisi!";
                            }
                        },
                        allowOutsideClick: false,
                        showCancelButton: true,
                    });

                    if (reasonResult.isConfirmed && reasonResult.value) {
                        itemsToAdd.push({
                            ...currentItem,
                            qty: requestedQty,
                            jenis: "pr",
                            alasan: reasonResult.value,
                        });
                    } else {
                        return;
                    }
                } else if (result.isDenied) {
                    // Option 2: Reservasi + Naikkan PR
                    const reasonResult = await Swal.fire({
                        title: "Alasan Naik PR",
                        text: `Masukkan alasan PR untuk sisa barang (${requestedQty - availableQty} Qty):`,
                        input: "text",
                        inputPlaceholder: "Wajib mengisi alasan...",
                        inputValidator: (value) => {
                            if (!value) {
                                return "Alasan wajib diisi!";
                            }
                        },
                        allowOutsideClick: false,
                        showCancelButton: true,
                    });

                    if (reasonResult.isConfirmed && reasonResult.value) {
                        // PR part (sisa qty)
                        itemsToAdd.push({
                            ...currentItem,
                            qty: requestedQty - availableQty,
                            jenis: "pr",
                            alasan: reasonResult.value,
                        });
                        // Reservasi part (available stock)
                        itemsToAdd.push({
                            ...currentItem,
                            qty: availableQty,
                            jenis: "blocked",
                            alasan: "",
                        });
                    } else {
                        return;
                    }
                } else if (result.dismiss === Swal.DismissReason.cancel) {
                    // Option 3: Hanya Reservasi (Sesuai Stok) -> Book SOH
                    itemsToAdd.push({
                        ...currentItem,
                        qty: availableQty,
                        jenis: "blocked",
                        alasan: "",
                    });
                } else {
                    setCurrentItem({
                        mid: "",
                        nama_barang: "",
                        qty: "",
                        keterangan: "",
                        uom: "",
                        desc: "",
                        available_qty: 0,
                    });
                    return;
                }
            } else {
                // requestedQty <= availableQty
                const result = await Swal.fire({
                    title: "Stok Tersedia!",
                    html: `
                        <div class="mb-4">
                            Barang ini memiliki stok ${availableQty}. Apakah Anda ingin melanjutkan PR (Menaikkan PR) atau hanya Reservasi (ke SAP)?
                        </div>
                        <div class="flex flex-col gap-2">
                            <button id="btn-option-pr" type="button" class="w-full py-2.5 px-4 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-md transition duration-150">
                                Naikkan PR
                            </button>
                            <button id="btn-option-reserve" type="button" class="w-full py-2.5 px-4 text-sm font-medium text-white bg-red-600 hover:bg-red-700 rounded-md transition duration-150">
                                Reservasi
                            </button>
                            <button id="btn-option-cancel" type="button" class="w-full py-2.5 px-4 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-md transition duration-150 mt-2">
                                Batal
                            </button>
                        </div>
                    `,
                    icon: "question",
                    showConfirmButton: false,
                    showCancelButton: false,
                    allowOutsideClick: false,
                    didOpen: () => {
                        const content = Swal.getHtmlContainer();
                        if (content) {
                            content.querySelector('#btn-option-pr').addEventListener('click', () => {
                                Swal.clickConfirm();
                            });
                            content.querySelector('#btn-option-reserve').addEventListener('click', () => {
                                Swal.clickCancel();
                            });
                            content.querySelector('#btn-option-cancel').addEventListener('click', () => {
                                Swal.close();
                            });
                        }
                    }
                });

                if (result.isConfirmed) {
                    const reasonResult = await Swal.fire({
                        title: "Alasan Naik PR",
                        input: "text",
                        inputPlaceholder: "Wajib mengisi alasan...",
                        inputValidator: (value) => {
                            if (!value) {
                                return "Alasan wajib diisi!";
                            }
                        },
                        allowOutsideClick: false,
                        showCancelButton: true,
                    });

                    if (reasonResult.isConfirmed && reasonResult.value) {
                        itemsToAdd.push({
                            ...currentItem,
                            qty: requestedQty,
                            jenis: "pr",
                            alasan: reasonResult.value,
                        });
                    } else {
                        return;
                    }
                } else if (result.dismiss === Swal.DismissReason.cancel) {
                    // Hanya Reservasi -> Book SOH
                    itemsToAdd.push({
                        ...currentItem,
                        qty: requestedQty,
                        jenis: "blocked",
                        alasan: "",
                    });
                } else {
                    setCurrentItem({
                        mid: "",
                        nama_barang: "",
                        qty: "",
                        keterangan: "",
                        uom: "",
                        desc: "",
                        available_qty: 0,
                    });
                    return;
                }
            }
        } else {
            // availableQty <= 0
            itemsToAdd.push({
                ...currentItem,
                qty: requestedQty,
                jenis: "pr",
                alasan: "",
            });
        }

        let allSuccess = true;
        const showIndividualNotification = itemsToAdd.length === 1;
        for (const item of itemsToAdd) {
            const success = await addItem(item, item.jenis, isJasa, showIndividualNotification);
            if (!success) {
                allSuccess = false;
                break;
            }
        }

        if (allSuccess) {
            setCurrentItem({
                mid: "",
                nama_barang: "",
                qty: "",
                keterangan: "",
                uom: "",
                desc: "",
                available_qty: 0,
            });

            if (itemsToAdd.length > 1) {
                Swal.fire({
                    icon: "success",
                    title: "Berhasil!",
                    text: `${itemsToAdd.length} barang berhasil ditambahkan.`,
                    confirmButtonText: "OK",
                    confirmButtonColor: "#10b981",
                    timer: 2000,
                    timerProgressBar: true,
                });
            }
        }
    };

    const resetForm = () => {
        setForm({
            pr_date: getTodayDate(),
            requested_by: user?.nama_lengkap || "",
            department: user?.departemen || "",
            jenis: "Barang",
            detail_jenis: "",
            no_io: "",
        });

        setCurrentItem({
            mid: "",
            nama_barang: "",
            qty: "",
            keterangan: "",
            uom: "",
            desc: "",
        });

        clearItems();
    };

    const handleClearItems = () => {
        Swal.fire({
            title: "Hapus Semua Barang?",
            text: "Semua barang yang telah ditambahkan ke list akan dihapus. Tindakan ini tidak dapat dibatalkan.",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#ef4444",
            cancelButtonColor: "#6c757d",
            confirmButtonText: "Ya, Hapus Semua!",
            cancelButtonText: "Batal",
        }).then((result) => {
            if (result.isConfirmed) {
                clearItems();
                Swal.fire({
                    icon: "success",
                    title: "Berhasil!",
                    text: "Semua barang berhasil dihapus.",
                    timer: 1500,
                    showConfirmButton: false,
                });
            }
        });
    };

    const submitWithSignature = async (signatureBase64 = null) => {
        setLoading(true);

        try {
            const res = await fetch("/purchase-requesition/store", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    Accept: "application/json",
                    "X-CSRF-TOKEN": document
                        .querySelector('meta[name="csrf-token"]')
                        .getAttribute("content"),
                },
                body: JSON.stringify({
                    ...form,
                    ttd: signatureBase64,
                    items: items.filter(item => item.jenis === 'pr').map((item) => ({
                        mid: item.mid || null,
                        qty: item.qty,
                        keterangan: item.keterangan,
                        desc: item.desc || null,
                        reservation_id: item.reservation_id,
                        jenis: item.jenis,
                        alasan: item.alasan,
                    })),
                    session_id: getSessionId(),
                }),
            });

            const data = await res.json();
            if (!res.ok) throw new Error(data.message);

            await Swal.fire({
                icon: "success",
                title: "Berhasil!",
                html: `PR berhasil disubmit.<br><strong>No. Dokumen: ${data.no_doc}</strong>`,
                confirmButtonText: "OK",
                confirmButtonColor: "#10b981",
                allowOutsideClick: false,
            });

            resetForm();
        } catch (err) {
            Swal.fire({
                icon: "error",
                title: "Error!",
                text: err.message || "Terjadi kesalahan saat submit.",
                confirmButtonText: "OK",
                confirmButtonColor: "#ef4444",
            });
        } finally {
            setLoading(false);
        }
    };

    function formatDate(date) {
        if (!date) return "-";
        const [year, month, day] = date.split("-").map(Number);
        return new Intl.DateTimeFormat("id-ID", {
            weekday: "long",
            day: "2-digit",
            month: "long",
            year: "numeric",
        }).format(new Date(year, month - 1, day));
    }

    useEffect(() => {
        setForm((prev) => ({
            ...prev,
            pr_date: getTodayDate(),
            department: user?.departemen || prev.department,
            requested_by: user?.nama_lengkap || prev.requested_by,
        }));
    }, [user]);

    return (
        <form
            onSubmit={(e) => {
                e.preventDefault();
                submitWithSignature(null);
            }}
            className="space-y-6"
        >
            {expiredAt && (
                <Countdown expiredAt={expiredAt} onExpired={handleExpired} />
            )}

            <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {/* LEFT SECTION */}
                <div className="lg:col-span-2 space-y-6">
                    {/* Form Informasi PR */}
                    <Card>
                        <CardHeader>
                            <CardTitle>Informasi PR</CardTitle>
                        </CardHeader>
                        <CardContent className="grid md:grid-cols-2 gap-4">
                            <Field label="Tanggal PR" required>
                                <Button
                                    type="button"
                                    variant="outline"
                                    disabled
                                    className="w-full justify-start text-left font-normal cursor-not-allowed"
                                >
                                    <CalendarDays className="mr-2 h-4 w-4 text-muted-foreground" />
                                    {formatDate(form.pr_date)}
                                </Button>
                            </Field>

                            <Field label="User" required>
                                <Input
                                    value={form.requested_by}
                                    onChange={(e) =>
                                        setForm({
                                            ...form,
                                            requested_by: e.target.value,
                                        })
                                    }
                                    disabled
                                    className="bg-gray-100 dark:bg-gray-800 cursor-not-allowed"
                                />
                            </Field>

                            <Field label="Departemen" required>
                                <Input
                                    value={(form.department || "").replace(/_/g, " ").toUpperCase()}
                                    disabled
                                    className="bg-gray-100 dark:bg-gray-800 cursor-not-allowed"
                                />
                            </Field>

                            <Field label="Jenis" required>
                                <Select
                                    value={form.jenis}
                                    onValueChange={(value) => {
                                        if (items.length > 0) {
                                            Swal.fire({
                                                title: "Ubah Jenis PR?",
                                                text: "Mengubah jenis PR akan menghapus semua barang/jasa yang telah ditambahkan. Lanjutkan?",
                                                icon: "warning",
                                                showCancelButton: true,
                                                confirmButtonColor: "#ef4444",
                                                cancelButtonColor: "#6c757d",
                                                confirmButtonText: "Ya, Ubah!",
                                                cancelButtonText: "Batal",
                                            }).then((result) => {
                                                if (result.isConfirmed) {
                                                    clearItems();
                                                    setCurrentItem({
                                                        mid: "",
                                                        nama_barang: "",
                                                        qty: "",
                                                        keterangan: "",
                                                        uom: "",
                                                        desc: "",
                                                    });
                                                    setForm({ ...form, jenis: value });
                                                }
                                            });
                                        } else {
                                            setCurrentItem({
                                                mid: "",
                                                nama_barang: "",
                                                qty: "",
                                                keterangan: "",
                                                uom: "",
                                                desc: "",
                                            });
                                            setForm({ ...form, jenis: value });
                                        }
                                    }}
                                >
                                    <SelectTrigger>
                                        <SelectValue placeholder="Pilih jenis" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="Barang">
                                            Barang
                                        </SelectItem>
                                        <SelectItem value="Jasa">
                                            Jasa
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                            </Field>

                            <Field label="Detail Jenis">
                                <Select
                                    value={form.detail_jenis}
                                    onValueChange={(value) =>
                                        setForm({
                                            ...form,
                                            detail_jenis: value,
                                        })
                                    }
                                >
                                    <SelectTrigger>
                                        <SelectValue placeholder="Pilih jenis" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="Project">
                                            Project
                                        </SelectItem>
                                        <SelectItem value="Asset">
                                            Asset
                                        </SelectItem>
                                        <SelectItem value="Consumable">
                                            Consumable
                                        </SelectItem>
                                        <SelectItem value="Cost Center">
                                            Cost Center
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                            </Field>

                            <Field label="No IO">
                                <Input
                                    value={form.no_io}
                                    onChange={(e) =>
                                        setForm({
                                            ...form,
                                            no_io: e.target.value,
                                        })
                                    }
                                />
                            </Field>
                        </CardContent>
                    </Card>

                    {/* Form Tambah Barang / Jasa */}
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-3">
                            <CardTitle>{form.jenis === "Jasa" ? "Tambah Jasa" : "Tambah Barang"}</CardTitle>
                            {form.jenis !== "Jasa" && (
                                <div className="flex items-center gap-2">
                                    <input
                                        type="file"
                                        id="excel-file-input"
                                        accept=".xlsx, .xls, .csv"
                                        className="hidden"
                                        onChange={handleExcelUpload}
                                    />
                                    <a
                                        href="/assets/templates/excel/template_upload_purchase_requesition.xlsx"
                                        download="template_upload_purchase_requesition.xlsx"
                                        className="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 border border-input bg-background hover:bg-accent hover:text-accent-foreground h-9 px-3"
                                    >
                                        Download Template
                                    </a>
                                    <Button
                                        type="button"
                                        variant="default"
                                        size="sm"
                                        className="flex items-center gap-1.5"
                                        onClick={() => document.getElementById("excel-file-input").click()}
                                    >
                                        <Upload className="h-4 w-4" />
                                        Import Excel
                                    </Button>
                                </div>
                            )}
                        </CardHeader>
                        <CardContent className="space-y-4">
                            {form.jenis !== "Jasa" && (
                                <Field label="Search Material" required>
                                    <MidSearch
                                        value={currentItem.mid}
                                        namaBarang={currentItem.nama_barang}
                                        onChange={(item) =>
                                            setCurrentItem({
                                                ...currentItem,
                                                ...item,
                                            })
                                        }
                                    />
                                </Field>
                            )}

                            {form.jenis === "Jasa" && (
                                <Field label="Desc" required>
                                    <Input
                                        value={currentItem.desc || ""}
                                        onChange={(e) =>
                                            setCurrentItem({
                                                ...currentItem,
                                                desc: e.target.value,
                                            })
                                        }
                                        placeholder="Deskripsi jasa"
                                    />
                                </Field>
                            )}

                            {form.jenis !== "Jasa" && (
                                <Field label="UoM">
                                    <Input
                                        value={currentItem.uom || ""}
                                        disabled
                                        placeholder="Satuan barang"
                                        className="bg-gray-100 dark:bg-gray-800 cursor-not-allowed"
                                    />
                                </Field>
                            )}

                            <Field label="Qty" required>
                                <Input
                                    type="number"
                                    min="1"
                                    value={currentItem.qty}
                                    onChange={(e) => {
                                        const val = e.target.value;
                                        if (val === "" || parseInt(val) > 0) {
                                            setCurrentItem({
                                                ...currentItem,
                                                qty: val,
                                            });
                                        }
                                    }}
                                    placeholder="Masukkan jumlah"
                                />
                            </Field>

                            <Field label="Keterangan" required>
                                <Input
                                    value={currentItem.keterangan}
                                    onChange={(e) =>
                                        setCurrentItem({
                                            ...currentItem,
                                            keterangan: e.target.value,
                                        })
                                    }
                                    placeholder={form.jenis === "Jasa" ? "Keterangan jasa" : "Keterangan barang"}
                                />
                            </Field>

                            <Button
                                type="button"
                                variant="default"
                                onClick={handleAddItem}
                                className="w-full"
                            >
                                {form.jenis === "Jasa" ? "+ Tambah Jasa" : "+ Tambah Barang"}
                            </Button>
                        </CardContent>
                    </Card>
                </div>

                {/* RIGHT SECTION - Summary */}
                <BookingSummary
                    form={form}
                    items={items}
                    loading={loading}
                    expiredAt={expiredAt}
                    onRemoveItem={removeItem}
                    onClearItems={handleClearItems}
                    formatDate={formatDate}
                />


                <Dialog open={showStockReview} onOpenChange={(open) => { setShowStockReview(open); if(!open) setReviewError(""); }}>
                    <DialogContent className="max-w-5xl max-h-[90vh] flex flex-col p-6">
                        <DialogHeader>
                            <DialogTitle className="text-xl font-bold flex items-center gap-2">
                                <Info className="h-5 w-5 text-blue-500" />
                                Review Barang dengan Stok Gudang
                            </DialogTitle>
                            <div className="text-sm text-muted-foreground mt-1">
                                Ditemukan <span className="font-semibold text-blue-600">{stockReviewItems.length} barang</span> yang memiliki stok di gudang. Silakan tentukan tindakan untuk masing-masing barang.
                            </div>
                        </DialogHeader>

                        {/* Bulk Action Controls */}
                        <div className="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 bg-blue-50/50 dark:bg-blue-950/20 p-3 rounded-lg border border-blue-100 dark:border-blue-900/50 mt-2">
                            <div className="text-sm">
                                <span className="font-semibold text-blue-800 dark:text-blue-300">Tindakan Massal:</span> Setel semua barang dengan pilihan tindakan yang sama.
                            </div>
                            <Select
                                onValueChange={(val) => {
                                    setStockReviewItems(prev =>
                                        prev.map(item => ({
                                            ...item,
                                            action: val
                                        }))
                                    );
                                    setReviewError("");
                                }}
                            >
                                <SelectTrigger className="w-[260px] bg-white dark:bg-gray-900">
                                    <SelectValue placeholder="Pilih tindakan massal..." />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="pr">Naikkan PR (Semua Qty)</SelectItem>
                                    <SelectItem value="both">PR + Reservasi (SAP)</SelectItem>
                                    <SelectItem value="reserve">Hanya Reservasi (SAP)</SelectItem>
                                    <SelectItem value="exclude">Jangan Tambahkan</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>

                        {/* Table Area */}
                        <div className="flex-1 overflow-y-auto my-4 border rounded-lg max-h-[50vh]">
                            <table className="w-full text-sm text-left border-collapse">
                                <thead className="bg-gray-50 dark:bg-gray-800 text-gray-700 dark:text-gray-300 uppercase text-xs sticky top-0 border-b z-10">
                                    <tr>
                                        <th className="px-4 py-3 font-semibold">MID / Nama</th>
                                        <th className="px-4 py-3 font-semibold text-center w-24">Qty Minta</th>
                                        <th className="px-4 py-3 font-semibold text-center w-24">Stok Gudang</th>
                                        <th className="px-4 py-3 font-semibold w-[220px]">Tindakan</th>
                                        <th className="px-4 py-3 font-semibold">Alasan Naik PR (Wajib jika PR)</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y">
                                    {stockReviewItems.map((item, index) => {
                                        const needsReason = item.action === 'pr' || (item.action === 'both' && item.qty > item.available_qty);
                                        const isReasonInvalid = needsReason && !item.alasan?.trim();

                                        return (
                                            <tr key={index} className="hover:bg-gray-50/50 dark:hover:bg-gray-800/30 transition-colors">
                                                <td className="px-4 py-3.5">
                                                    <div className="font-semibold text-gray-900 dark:text-gray-100">{item.mid}</div>
                                                    <div className="text-xs text-muted-foreground truncate max-w-[250px]">{item.nama_barang}</div>
                                                </td>
                                                <td className="px-4 py-3.5 text-center font-medium">
                                                    {item.qty} <span className="text-xs text-muted-foreground">{item.uom}</span>
                                                </td>
                                                <td className="px-4 py-3.5 text-center font-medium text-green-600 dark:text-green-400">
                                                    {item.available_qty} <span className="text-xs text-muted-foreground">{item.uom}</span>
                                                </td>
                                                <td className="px-4 py-3.5">
                                                    <Select
                                                        value={item.action}
                                                        onValueChange={(val) => {
                                                            setStockReviewItems(prev =>
                                                                prev.map((x, idx) => idx === index ? { ...x, action: val } : x)
                                                            );
                                                            setReviewError("");
                                                        }}
                                                    >
                                                        <SelectTrigger className="w-full text-xs">
                                                            <SelectValue />
                                                        </SelectTrigger>
                                                        <SelectContent>
                                                            <SelectItem value="pr">Naikkan PR (Semua Qty)</SelectItem>
                                                            <SelectItem value="both">PR + Reservasi (SAP)</SelectItem>
                                                            <SelectItem value="reserve">Hanya Reservasi (SAP)</SelectItem>
                                                            <SelectItem value="exclude">Jangan Tambahkan</SelectItem>
                                                        </SelectContent>
                                                    </Select>
                                                </td>
                                                <td className="px-4 py-3.5">
                                                    {needsReason ? (
                                                        <Input
                                                            type="text"
                                                            value={item.alasan || ""}
                                                            placeholder="Wajib mengisi alasan..."
                                                            className={`text-xs h-9 ${isReasonInvalid ? "border-red-500 focus-visible:ring-red-500" : ""}`}
                                                            onChange={(e) => {
                                                                const val = e.target.value;
                                                                setStockReviewItems(prev =>
                                                                    prev.map((x, idx) => idx === index ? { ...x, alasan: val } : x)
                                                                );
                                                                setReviewError("");
                                                            }}
                                                        />
                                                    ) : (
                                                        <span className="text-xs text-muted-foreground italic">-</span>
                                                    )}
                                                </td>
                                            </tr>
                                        );
                                    })}
                                </tbody>
                            </table>
                        </div>

                        {reviewError && (
                            <div className="bg-red-50 dark:bg-red-950/20 border border-red-200 dark:border-red-900/50 text-red-700 dark:text-red-400 text-sm p-3 rounded-lg flex items-center gap-2 mb-4 animate-in fade-in slide-in-from-top-1 duration-200">
                                <AlertCircle className="h-4 w-4 shrink-0" />
                                <span className="font-medium">{reviewError}</span>
                            </div>
                        )}

                        <DialogFooter className="gap-2 sm:gap-0">
                            <Button
                                type="button"
                                variant="outline"
                                onClick={() => { setShowStockReview(false); setReviewError(""); }}
                            >
                                Batal
                            </Button>
                            <Button
                                type="button"
                                variant="default"
                                onClick={async () => {
                                    const missingReason = stockReviewItems.some(item => {
                                        const needsReason = item.action === 'pr' || (item.action === 'both' && item.qty > item.available_qty);
                                        return needsReason && !item.alasan?.trim();
                                    });

                                    if (missingReason) {
                                        setReviewError("Mohon isi alasan untuk semua barang yang dinaikkan ke PR.");
                                        return;
                                    }

                                    setShowStockReview(false);
                                    setReviewError("");
                                    await addParsedItems(noStockItems, stockReviewItems);
                                }}
                            >
                                Konfirmasi & Tambahkan ({stockReviewItems.length + noStockItems.length} Barang)
                            </Button>
                        </DialogFooter>
                    </DialogContent>
                </Dialog>
            </div>
        </form>
    );
}

function Field({ label, children, required = false }) {
    return (
        <div className="space-y-1">
            <Label>
                {label}
                {required && <span className="text-red-500 ml-1">*</span>}
            </Label>

            {children}
        </div>
    );
}
