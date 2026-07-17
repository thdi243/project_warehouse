import { useEffect, useState } from "react";
import { Badge } from "@/components/ui/badge";
import { Button } from "@/components/ui/button";
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
    DialogDescription,
} from "@/components/ui/dialog";
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from "@/components/ui/table";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Eye, Printer, Inbox } from "lucide-react";

const statusVariant = {
    pending: "warning",
    approved: "success",
    rejected: "destructive",
};

export default function RiwayatPR() {
    const [openDetail, setOpenDetail] = useState(false);
    const [selectedPr, setSelectedPr] = useState(null);
    const [data, setData] = useState([]);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        fetch("/purchase-requesition/getRiwayat", {
            credentials: "include",
            headers: {
                Accept: "application/json",
            },
        })
            .then((res) => {
                if (res.status === 401) throw new Error("Unauthenticated");
                return res.json();
            })
            .then((res) => setData(res.data))
            .catch((err) => console.error(err))
            .finally(() => setLoading(false)); // ✅ ini penting
    }, []);

    const handleDetail = (pr) => {
        setSelectedPr(pr);
        setOpenDetail(true);
    };

    const handlePrintPdf = (pr) => {
        window.open(
            `/api/purchase-requesition/print-riwayat/${pr.id}`,
            "_blank",
        );
    };

    return (
        <Card>
            <Dialog open={openDetail} onOpenChange={setOpenDetail}>
                <DialogContent className="max-w-4xl">
                    <DialogTitle>Detail Purchase Requesition</DialogTitle>
                    <DialogDescription>
                        Informasi detail Purchase Requesition beserta daftar
                        barang.
                    </DialogDescription>

                    {selectedPr && (
                        <div className="space-y-4 text-sm">
                            {/* HEADER */}
                            <div className="grid grid-cols-2 gap-3">
                                <div>
                                    <p className="text-muted-foreground">
                                        No PR
                                    </p>
                                    <p className="font-medium">
                                        {selectedPr.pr_number}
                                    </p>
                                </div>
                                <div>
                                    <p className="text-muted-foreground">
                                        Tanggal
                                    </p>
                                    <p className="font-medium">
                                        {selectedPr.pr_date}
                                    </p>
                                </div>
                                <div>
                                    <p className="text-muted-foreground">
                                        User
                                    </p>
                                    <p className="font-medium">
                                        {selectedPr.requested_by}
                                    </p>
                                </div>
                                <div>
                                    <p className="text-muted-foreground">
                                        Departemen
                                    </p>
                                    <p className="font-medium">
                                        {(selectedPr.department || "").replace(/_/g, " ").toUpperCase()}
                                    </p>
                                </div>
                                <div>
                                    <p className="text-muted-foreground">
                                        Jenis
                                    </p>
                                    <p className="font-medium">
                                        {selectedPr.jenis} /
                                        {selectedPr.detail_jenis ?? "-"}
                                    </p>
                                </div>
                                <div>
                                    <p className="text-muted-foreground">
                                        No IO
                                    </p>
                                    <p className="font-medium">
                                        {selectedPr.no_io ?? "-"}
                                    </p>
                                </div>
                                <div>
                                    <p className="text-muted-foreground">
                                        Status
                                    </p>
                                    <Badge
                                        variant={
                                            statusVariant[selectedPr.status]
                                        }
                                    >
                                        {selectedPr.status}
                                    </Badge>
                                </div>
                            </div>

                            {/* ITEMS */}
                            <div>
                                <p className="font-semibold mb-2">
                                    Daftar Barang
                                </p>

                                <Table>
                                    <TableHeader>
                                        <TableRow>
                                            <TableHead>MID</TableHead>
                                            <TableHead>Nama Barang</TableHead>
                                            <TableHead>Jenis</TableHead>
                                            <TableHead>UoM</TableHead>
                                            <TableHead>Qty</TableHead>
                                            <TableHead>Keterangan</TableHead>
                                        </TableRow>
                                    </TableHeader>
                                    <TableBody>
                                        {selectedPr.items.map((item) => (
                                            <TableRow key={item.id}>
                                                <TableCell>
                                                    {item.barang?.mid_barang ?? "-"}
                                                </TableCell>
                                                <TableCell>
                                                    {item.barang?.nama_barang ?? item.desc ?? "-"}
                                                </TableCell>
                                                <TableCell>
                                                    {item.jenis === "blocked" ? (
                                                        <span className="inline-flex items-center rounded-md bg-emerald-50 dark:bg-emerald-950 px-2 py-0.5 text-xs font-medium text-emerald-700 dark:text-emerald-300 ring-1 ring-inset ring-emerald-600/20">
                                                            Reservasi
                                                        </span>
                                                    ) : (
                                                        <span className="inline-flex items-center rounded-md bg-blue-50 dark:bg-blue-950 px-2 py-0.5 text-xs font-medium text-blue-700 dark:text-blue-300 ring-1 ring-inset ring-blue-700/10">
                                                            Menaikkan PR
                                                        </span>
                                                    )}
                                                </TableCell>
                                                <TableCell>
                                                    {item.barang?.uom ?? "-"}
                                                </TableCell>
                                                <TableCell>
                                                    {item.qty}
                                                </TableCell>
                                                <TableCell>
                                                    {item.keterangan ?? "-"}
                                                </TableCell>
                                            </TableRow>
                                        ))}
                                    </TableBody>
                                </Table>
                            </div>
                        </div>
                    )}
                </DialogContent>
            </Dialog>

            <CardHeader>
                <CardTitle className="font-bold text-xl">
                    Riwayat Purchase Requesition Anda
                </CardTitle>
                <p>Data diurutkan berdasarkan pengajuan terbaru.</p>
            </CardHeader>

            <CardContent>
                {loading ? (
                    <div className="flex justify-center py-10 text-sm text-muted-foreground">
                        Loading data...
                    </div>
                ) : data.length === 0 ? (
                    <div className="flex flex-col items-center justify-center py-16 text-center">
                        <Inbox className="h-12 w-12 text-muted-foreground mb-4" />

                        <h3 className="text-lg font-semibold">Belum ada PR</h3>

                        <p className="text-sm text-muted-foreground mt-1">
                            Anda belum pernah membuat Purchase Requisition.
                        </p>
                    </div>
                ) : (
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Tanggal</TableHead>
                                <TableHead>No Doc</TableHead>
                                <TableHead>No PR</TableHead>
                                <TableHead>Departemen</TableHead>
                                <TableHead>Jenis</TableHead>
                                <TableHead>Status</TableHead>
                                <TableHead className="text-right">
                                    Aksi
                                </TableHead>
                            </TableRow>
                        </TableHeader>

                        <TableBody>
                            {data.length === 0 ? (
                                <TableRow>
                                    <TableCell
                                        colSpan={7}
                                        className="text-center text-muted-foreground"
                                    >
                                        Belum ada PR
                                    </TableCell>
                                </TableRow>
                            ) : (
                                data.map((pr) => (
                                    <TableRow key={pr.id}>
                                        <TableCell>
                                            {pr.pr_date ?? "-"}
                                        </TableCell>
                                        <TableCell>
                                            {pr.no_doc ?? "-"}
                                        </TableCell>
                                        <TableCell>
                                            {pr.pr_number ?? "-"}
                                        </TableCell>
                                        <TableCell>
                                            {(pr.department ?? "").replace(/_/g, " ").toUpperCase() || "-"}
                                        </TableCell>
                                        <TableCell className="capitalize">
                                            {pr.jenis ?? "-"}
                                            {pr.detail_jenis && (
                                                <span className="text-xs text-muted-foreground block">
                                                    {pr.detail_jenis.replace("_", " ")}
                                                </span>
                                            )}
                                        </TableCell>
                                        <TableCell>
                                            <Badge
                                                variant={
                                                    statusVariant[pr.status]
                                                }
                                            >
                                                {pr.status}
                                            </Badge>
                                        </TableCell>
                                        <TableCell className="text-right flex gap-2 justify-end">
                                            {/* BUTTON DETAIL */}
                                            <Button
                                                variant="info"
                                                size="icon"
                                                className="text-blue-900"
                                                onClick={() => handleDetail(pr)}
                                                title="Detail"
                                            >
                                                <Eye className="h-4 w-4" />
                                            </Button>

                                            {/* BUTTON PRINT PDF */}
                                            <Button
                                                variant="destructive"
                                                size="icon"
                                                className="text-white"
                                                onClick={() =>
                                                    handlePrintPdf(pr)
                                                }
                                                title="Print PDF"
                                            >
                                                <Printer className="h-4 w-4" />
                                            </Button>
                                        </TableCell>
                                    </TableRow>
                                ))
                            )}
                        </TableBody>
                    </Table>
                )}
            </CardContent>
        </Card>
    );
}
