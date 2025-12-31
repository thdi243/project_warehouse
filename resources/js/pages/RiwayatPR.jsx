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
import { Eye, Printer } from "lucide-react";

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
            "_blank"
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
                                        {selectedPr.department}
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
                                            <TableHead>UoM</TableHead>
                                            <TableHead>Qty</TableHead>
                                            <TableHead>Keterangan</TableHead>
                                        </TableRow>
                                    </TableHeader>
                                    <TableBody>
                                        {selectedPr.items.map((item) => (
                                            <TableRow key={item.id}>
                                                <TableCell>
                                                    {item.barang.mid_barang}
                                                </TableCell>
                                                <TableCell>
                                                    {item.barang.nama_barang}
                                                </TableCell>
                                                <TableCell>
                                                    {item.barang.uom}
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
                    <p className="text-sm text-muted-foreground">Loading...</p>
                ) : (
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Tanggal</TableHead>
                                <TableHead>No PR</TableHead>
                                <TableHead>Departemen</TableHead>
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
                                        colSpan={5}
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
                                            {pr.pr_number ?? "-"}
                                        </TableCell>
                                        <TableCell>
                                            {pr.department ?? "-"}
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
