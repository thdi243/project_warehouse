import { useEffect, useState } from "react";
import MidSearch from "../components/MidSearch";
import Countdown from "../components/CountdownTimer";
import BookingSummary from "../components/BookingSummary";
import { useBookingManager } from "../hooks/useBookingManager";
import SignatureModal from "@/components/SignatureModal";
import Swal from "sweetalert2";

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
        jenis: "",
        detail_jenis: "",
        no_io: "",
    });

    const [currentItem, setCurrentItem] = useState({
        mid: "",
        nama_barang: "",
        qty: "",
        keterangan: "",
        uom: "",
    });

    const [loading, setLoading] = useState(false);

    const getTodayDate = () => {
        const today = new Date();
        const y = today.getFullYear();
        const m = String(today.getMonth() + 1).padStart(2, "0");
        const d = String(today.getDate()).padStart(2, "0");
        return `${y}-${m}-${d}`;
    };

    const DEPARTEMEN_OPTIONS = [
        "ITE",
        "Engineering",
        "Warehouse",
        "HRGA",
        "Produksi",
        "Quality Control",
    ];

    const handleAddItem = async () => {
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

        let type = "pr";
        let alasan = "";
        
        if (currentItem.available_qty > 0) {
            const result = await Swal.fire({
                title: "Stok Tersedia!",
                text: `Barang ini memiliki stok ${currentItem.available_qty}. Apakah Anda ingin melanjutkan PR (Menaikkan PR) atau hanya Block Stok?`,
                icon: "question",
                showCancelButton: true,
                confirmButtonText: "Naikkan PR",
                cancelButtonText: "Reservasi",
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#10b981",
                allowOutsideClick: false,
            });

            if (result.isConfirmed) {
                type = "pr";
            } else if (result.dismiss === Swal.DismissReason.cancel) {
                type = "blocked";
            } else {
                return; // User clicked outside or closed (though allowOutsideClick: false)
            }

            if (type === "pr") {
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
                    alasan = reasonResult.value;
                } else {
                    return; // User canceled reason input
                }
            }
        }

        const itemToPass = {
            ...currentItem,
            jenis: type,
            alasan: alasan,
        };

        const success = await addItem(itemToPass, type);
        if (success) {
            setCurrentItem({
                mid: "",
                nama_barang: "",
                qty: "",
                keterangan: "",
                uom: "",
                available_qty: 0,
            });
        }
    };

    const resetForm = () => {
        setForm({
            pr_date: getTodayDate(),
            requested_by: "",
            department: "",
            jenis: "",
            detail_jenis: "",
            no_io: "",
        });

        setCurrentItem({
            mid: "",
            nama_barang: "",
            qty: "",
            keterangan: "",
            uom: "",
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
                        mid: item.mid,
                        qty: item.qty,
                        keterangan: item.keterangan,
                        reservation_id: item.reservation_id,
                        jenis: item.jenis,
                        alasan: item.alasan,
                    })),
                    session_id: getSessionId(),
                }),
            });

            const data = await res.json();
            if (!res.ok) throw new Error(data.message);

            Swal.fire({
                icon: "success",
                title: "Berhasil!",
                text: "PR berhasil disubmit.",
                confirmButtonText: "OK",
                confirmButtonColor: "#10b981",
                timer: 4000,
                timerProgressBar: true,
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
        }));
    }, []);

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
                                />
                            </Field>

                            <Field label="Departemen" required>
                                <Select
                                    value={form.department}
                                    onValueChange={(value) =>
                                        setForm({ ...form, department: value })
                                    }
                                >
                                    <SelectTrigger className="w-full">
                                        <SelectValue placeholder="Pilih departemen" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {DEPARTEMEN_OPTIONS.map((dept) => (
                                            <SelectItem key={dept} value={dept}>
                                                {dept}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </Field>

                            <Field label="Jenis" required>
                                <Select
                                    value={form.jenis}
                                    onValueChange={(value) =>
                                        setForm({ ...form, jenis: value })
                                    }
                                >
                                    <SelectTrigger>
                                        <SelectValue placeholder="Pilih jenis" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="barang">
                                            Barang
                                        </SelectItem>
                                        <SelectItem value="jasa">
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
                                        <SelectItem value="project">
                                            Project
                                        </SelectItem>
                                        <SelectItem value="asset">
                                            Asset
                                        </SelectItem>
                                        <SelectItem value="consumable">
                                            Consumable
                                        </SelectItem>
                                        <SelectItem value="cost_center">
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

                    {/* Form Tambah Barang */}
                    <Card>
                        <CardHeader>
                            <CardTitle>Tambah Barang</CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <Field label="Search MID" required>
                                <MidSearch
                                    value={currentItem.mid}
                                    onChange={(item) =>
                                        setCurrentItem({
                                            ...currentItem,
                                            ...item,
                                        })
                                    }
                                />
                            </Field>

                            <Field label="UoM">
                                <Input
                                    value={currentItem.uom || ""}
                                    disabled
                                    placeholder="Satuan barang"
                                    className="bg-gray-100 dark:bg-gray-800 cursor-not-allowed"
                                />
                            </Field>

                            <Field label="Qty" required>
                                <Input
                                    type="number"
                                    value={currentItem.qty}
                                    onChange={(e) =>
                                        setCurrentItem({
                                            ...currentItem,
                                            qty: e.target.value,
                                        })
                                    }
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
                                    placeholder="Keterangan barang"
                                />
                            </Field>

                            <Button
                                type="button"
                                variant="default"
                                onClick={handleAddItem}
                                className="w-full"
                            >
                                + Tambah Barang
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
