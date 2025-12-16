import { useEffect, useState } from "react";
import MidSearch from "../components/MidSearch";
import { useToast } from "@/hooks/use-toast";
import { HiTrash } from "react-icons/hi";

import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Card, CardHeader, CardContent, CardTitle } from "@/components/ui/card";
import { Separator } from "@/components/ui/separator";
import { Calendar } from "@/components/ui/calendar";
import {
    Popover,
    PopoverContent,
    PopoverTrigger,
} from "@/components/ui/popover";
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from "@/components/ui/select";
import { CalendarDays } from "lucide-react";

export default function PurchaseRequisitionForm() {
    const [user, setUser] = useState(null);
    const [loadingUser, setLoadingUser] = useState(true);
    const { toast } = useToast();

    const [form, setForm] = useState({
        pr_date: "",
        requested_by: "",
        department: "",
        jenis: "",
        detail_jenis: "",
    });

    const [currentItem, setCurrentItem] = useState({
        mid: "",
        nama_barang: "",
        qty: "",
        keterangan: "",
    });

    const [items, setItems] = useState([]);
    const [loading, setLoading] = useState(false);

    const addItem = () => {
        if (!currentItem.mid || !currentItem.qty || !currentItem.keterangan) {
            toast({
                variant: "destructive",
                title: "Error!",
                description:
                    "Harap lengkapi semua field barang sebelum menambahkan.",
            });
            return;
        }

        setItems([...items, currentItem]);
        setCurrentItem({
            mid: "",
            nama_barang: "",
            qty: "",
            keterangan: "",
        });
    };

    const resetForm = () => {
        setForm({
            pr_date: "",
            requested_by: "",
            department: "",
            jenis: "",
            detail_jenis: "",
        });

        setItems([]);
        setCurrentItem({
            mid: "",
            nama_barang: "",
            qty: "",
            keterangan: "",
        });
    };

    const removeItem = (index) => {
        setItems(items.filter((_, i) => i !== index));
    };

    const handleSubmit = async (e) => {
        e.preventDefault();

        if (items.length === 0) {
            toast({
                variant: "destructive",
                title: "Error!",
                description: "Harap tambahkan minimal satu barang.",
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
                credentials: "same-origin",
                body: JSON.stringify({ ...form, items }),
            });

            const data = await res.json();
            if (!res.ok) throw new Error(data.message);
            toast({
                title: "Berhasil!",
                description: "Purchase Requesition berhasil disubmit.",
                variant: "success",
            });
            resetForm();
            setItems([]);
        } catch (err) {
            toast({
                variant: "destructive",
                title: "Error!",
                description: err.message || "Terjadi kesalahan tak terduga.",
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

    return (
        <form
            onSubmit={handleSubmit}
            className="grid grid-cols-1 lg:grid-cols-3 gap-6"
        >
            {/* LEFT */}
            <div className="lg:col-span-2 space-y-6">
                <Card>
                    <CardHeader>
                        <CardTitle>Informasi PR</CardTitle>
                    </CardHeader>
                    <CardContent className="grid md:grid-cols-2 gap-4">
                        <Field label="Tanggal PR">
                            <Popover>
                                <PopoverTrigger asChild>
                                    <Button
                                        variant="outline"
                                        className="w-full justify-start text-left font-normal"
                                    >
                                        <CalendarDays className="mr-2 h-4 w-4 text-muted-foreground" />
                                        {form.pr_date ? (
                                            formatDate(form.pr_date)
                                        ) : (
                                            <span className="text-muted-foreground">
                                                Pilih tanggal
                                            </span>
                                        )}
                                    </Button>
                                </PopoverTrigger>

                                <PopoverContent className="p-0">
                                    <Calendar
                                        mode="single"
                                        selected={
                                            form.pr_date
                                                ? new Date(form.pr_date)
                                                : undefined
                                        }
                                        onSelect={(date) => {
                                            if (!date) return;

                                            const y = date.getFullYear();
                                            const m = String(
                                                date.getMonth() + 1
                                            ).padStart(2, "0");
                                            const d = String(
                                                date.getDate()
                                            ).padStart(2, "0");

                                            setForm({
                                                ...form,
                                                pr_date: `${y}-${m}-${d}`,
                                            });
                                        }}
                                    />
                                </PopoverContent>
                            </Popover>
                        </Field>

                        <Field label="User">
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

                        <Field label="Departemen">
                            <Input
                                value={form.department}
                                onChange={(e) =>
                                    setForm({
                                        ...form,
                                        department: e.target.value,
                                    })
                                }
                            />
                        </Field>

                        <Field label="Jenis">
                            <Select
                                value={form.jenis}
                                onValueChange={(value) =>
                                    setForm({
                                        ...form,
                                        jenis: value,
                                    })
                                }
                            >
                                <SelectTrigger>
                                    <SelectValue placeholder="Pilih jenis" />
                                </SelectTrigger>

                                <SelectContent>
                                    <SelectItem value="Barang">
                                        Barang
                                    </SelectItem>
                                    <SelectItem value="Jasa">Jasa</SelectItem>
                                </SelectContent>
                            </Select>
                        </Field>

                        <Field label="Detail Jenis">
                            <Input
                                value={form.detail_jenis}
                                onChange={(e) =>
                                    setForm({
                                        ...form,
                                        detail_jenis: e.target.value,
                                    })
                                }
                            />
                        </Field>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>Tambah Barang</CardTitle>
                    </CardHeader>
                    <CardContent className="space-y-4">
                        <MidSearch
                            value={currentItem.mid}
                            onChange={(item) =>
                                setCurrentItem({
                                    ...currentItem,
                                    ...item,
                                })
                            }
                        />

                        <Field label="Qty">
                            <Input
                                type="number"
                                value={currentItem.qty}
                                onChange={(e) =>
                                    setCurrentItem({
                                        ...currentItem,
                                        qty: e.target.value,
                                    })
                                }
                            />
                        </Field>

                        <Field label="Keterangan">
                            <Input
                                value={currentItem.keterangan}
                                onChange={(e) =>
                                    setCurrentItem({
                                        ...currentItem,
                                        keterangan: e.target.value,
                                    })
                                }
                            />
                        </Field>

                        <Button
                            type="button"
                            variant="default"
                            onClick={addItem}
                        >
                            + Tambah Barang
                        </Button>
                    </CardContent>
                </Card>
            </div>

            {/* RIGHT */}
            <Card className="h-fit sticky top-0 ">
                <CardHeader>
                    <CardTitle>Ringkasan PR</CardTitle>
                </CardHeader>

                <CardContent className="space-y-5 text-sm">
                    {/* Informasi */}
                    <div className="grid grid-cols-2 gap-y-2">
                        <span className="text-muted-foreground">Tanggal</span>
                        <span className="font-medium">
                            {form.pr_date ? formatDate(form.pr_date) : "-"}
                        </span>

                        <span className="text-muted-foreground">User</span>
                        <span className="font-medium">
                            {form.requested_by || "-"}
                        </span>

                        <span className="text-muted-foreground">
                            Departemen
                        </span>
                        <span className="font-medium">
                            {form.department || "-"}
                        </span>

                        <span className="text-muted-foreground">Jenis</span>
                        <span className="font-medium">{form.jenis || "-"}</span>

                        <span className="text-muted-foreground">Detail</span>
                        <span className="font-medium">
                            {form.detail_jenis || "-"}
                        </span>
                    </div>

                    <Separator />

                    {/* Items */}
                    {items.length === 0 ? (
                        <p className="text-muted-foreground">
                            Belum ada barang ditambahkan
                        </p>
                    ) : (
                        <div className="space-y-3">
                            {items.map((item, i) => (
                                <div
                                    key={i}
                                    className="flex justify-between gap-3 rounded-md border p-3"
                                >
                                    <div>
                                        <p className="font-medium">
                                            {item.mid} – {item.nama_barang}
                                        </p>
                                        <p className="text-xs text-muted-foreground">
                                            Qty: {item.qty}
                                        </p>
                                        {item.keterangan && (
                                            <p className="text-xs text-muted-foreground">
                                                Ket: {item.keterangan}
                                            </p>
                                        )}
                                    </div>

                                    <Button
                                        size="icon"
                                        variant="ghost"
                                        className="text-red-500 hover:bg-red-50"
                                        onClick={() => removeItem(i)}
                                    >
                                        <HiTrash className="h-4 w-4" />
                                    </Button>
                                </div>
                            ))}
                        </div>
                    )}

                    <Separator />

                    <Button type="submit" className="w-full" disabled={loading}>
                        {loading ? "Menyimpan..." : "Submit PR"}
                    </Button>
                </CardContent>
            </Card>
        </form>
    );
}

function Field({ label, children }) {
    return (
        <div className="space-y-1">
            <Label>{label}</Label>
            {children}
        </div>
    );
}
