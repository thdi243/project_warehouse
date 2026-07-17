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
import { CalendarDays } from "lucide-react";

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
                                Naikkan PR+ Reservasi (Potong Stok) 
                            </button>
                            <button id="btn-option-reserve" type="button" class="w-full py-2.5 px-4 text-sm font-medium text-white bg-red-600 hover:bg-red-700 rounded-md transition duration-150">
                                Hanya Reservasi (Potong Stok)
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
                        // Hanya naikkan PR untuk sisa qty (requestedQty - availableQty), reservasi dibatalkan
                        itemsToAdd.push({
                            ...currentItem,
                            qty: requestedQty - availableQty,
                            jenis: "pr",
                            alasan: reasonResult.value,
                        });
                    } else {
                        return;
                    }
                } else if (result.dismiss === Swal.DismissReason.cancel) {
                    // Option 3: Hanya Reservasi (Sesuai Stok) -> Reset Form Tambah Barang & Batal
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
                            Barang ini memiliki stok ${availableQty}. Apakah Anda ingin melanjutkan PR (Menaikkan PR) atau hanya Potong Stok?
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
                    // Hanya Reservasi -> Reset Form Tambah Barang & Batal
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
        for (const item of itemsToAdd) {
            const success = await addItem(item, item.jenis, isJasa);
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

    const submitWithSignature = async (signatureBase64) => {
        if (!signatureBase64) {
            Swal.fire({
                icon: "error",
                title: "Error!",
                text: "Tanda Tangan wajib diisi.",
                confirmButtonText: "OK",
                confirmButtonColor: "#ef4444",
            });
            return;
        }

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
                    items: items.map((item) => ({
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
                setShowSignature(true);
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
                        <CardHeader>
                            <CardTitle>{form.jenis === "Jasa" ? "Tambah Jasa" : "Tambah Barang"}</CardTitle>
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
                    formatDate={formatDate}
                />
                <SignatureModal
                    open={showSignature}
                    onClose={() => setShowSignature(false)}
                    onSave={submitWithSignature}
                />
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
