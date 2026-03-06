import React, { useEffect, useState } from "react";
import axios from "axios";
import jsPDF from "jspdf";
import autoTable from "jspdf-autotable";
import { HiDocumentDownload, HiRefresh } from "react-icons/hi";
import { Card, CardHeader, CardTitle, CardContent } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Badge } from "@/components/ui/badge";
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from "@/components/ui/table";
import { Separator } from "@/components/ui/separator";

const StockOnHandTable = () => {
    const [data, setData] = useState([]);
    const [filteredData, setFilteredData] = useState([]);
    const [loading, setLoading] = useState(true);
    const [search, setSearch] = useState("");
    const [currentPage, setCurrentPage] = useState(1);

    const rowsPerPage = 20;

    const fetchData = async () => {
        setLoading(true);
        try {
            const res = await axios.get("/api/wsp/stock-on-hand/getData");
            if (res.data.success) {
                setData(res.data.data);
                setFilteredData(res.data.data);
            }
        } catch (error) {
            console.error("Error:", error);
        }
        setLoading(false);
    };

    useEffect(() => {
        fetchData();
    }, []);

    // SEARCH
    useEffect(() => {
        const filtered = data.filter(
            (item) =>
                item.nama_barang
                    ?.toLowerCase()
                    .includes(search.toLowerCase()) ||
                String(item.mid_barang)?.includes(search),
        );
        setFilteredData(filtered);
        setCurrentPage(1);
    }, [search, data]);

    // PAGINATION
    const indexOfLastRow = currentPage * rowsPerPage;
    const indexOfFirstRow = indexOfLastRow - rowsPerPage;
    const currentRows = filteredData.slice(indexOfFirstRow, indexOfLastRow);
    const totalPages = Math.ceil(filteredData.length / rowsPerPage);

    const getFormattedDate = () => {
        const d = new Date();
        const pad = (n) => String(n).padStart(2, "0");

        return (
            d.getFullYear() +
            "-" +
            pad(d.getMonth() + 1) +
            "-" +
            pad(d.getDate())
        );
    };

    const exportPDF = () => {
        const doc = new jsPDF("landscape");

        // Header
        doc.setFontSize(16);
        doc.text("Stock On Hand Report", 14, 15);

        // Sub Info
        doc.setFontSize(10);
        doc.text(`Exported: ${new Date().toLocaleString()}`, 14, 22);
        doc.text(`Total Data: ${filteredData.length}`, 14, 28);

        // Prepare table
        const tableColumn = [
            "#",
            "MID",
            "Nama Barang",
            "UoM",
            "Stock Gudang",
            "Last Update",
        ];
        const tableRows = [];

        filteredData.forEach((item, index) => {
            tableRows.push([
                index + 1,
                item.mid_barang,
                item.nama_barang,
                item.uom,
                item.qty_soh,
                item.last_update,
            ]);
        });

        autoTable(doc, {
            head: [tableColumn],
            body: tableRows,
            startY: 35,
            styles: { fontSize: 9 },
            headStyles: { fillColor: [52, 152, 219] }, // biru soft
        });

        // Footer
        const pageCount = doc.internal.getNumberOfPages();
        doc.setFontSize(10);
        doc.text(
            `Generated automatically by system`,
            14,
            doc.internal.pageSize.height - 10,
        );
        doc.text(
            `Page 1 of ${pageCount}`,
            260,
            doc.internal.pageSize.height - 10,
        );

        const filename = `Stock_On_Hand_${getFormattedDate()}.pdf`;
        doc.save(filename);
    };

    return (
        <Card className="w-full">
            <CardHeader className="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                <div>
                    <CardTitle>Stock On Hand</CardTitle>
                    <p className="text-sm text-muted-foreground">
                        Stock fisik di gudang berdasarkan update terakhir sistem
                        WMS
                    </p>
                </div>

                <div className="flex gap-2">
                    <Button
                        variant="destructive"
                        onClick={exportPDF}
                        disabled={loading}
                    >
                        <HiDocumentDownload className="mr-2 h-4 w-4" />
                        Export PDF
                    </Button>

                    <Button
                        variant="info"
                        onClick={fetchData}
                        disabled={loading}
                    >
                        <HiRefresh className="mr-2 h-4 w-4" />
                        {loading ? "Refreshing..." : "Refresh"}
                    </Button>
                </div>
            </CardHeader>

            <Separator />

            <CardContent className="space-y-4">
                {/* Search */}
                <Input
                    className="mt-4"
                    placeholder="Cari nama barang atau MID..."
                    value={search}
                    onChange={(e) => setSearch(e.target.value)}
                />

                {/* Info */}
                <p className="text-sm text-muted-foreground">
                    Total Data:{" "}
                    <span className="font-medium">{filteredData.length}</span>
                </p>

                {/* Table */}
                <div className="-mx-4 md:mx-0 overflow-x-auto">
                    <div className="min-w-[720px] md:min-w-0 rounded-md border">
                        <Table>
                            <TableHeader className="sticky top-0 bg-background">
                                <TableRow>
                                    <TableHead>#</TableHead>
                                    <TableHead>MID</TableHead>
                                    <TableHead>Nama Barang</TableHead>
                                    <TableHead>UoM</TableHead>
                                    <TableHead>Stock</TableHead>
                                    <TableHead>Last Updated</TableHead>
                                </TableRow>
                            </TableHeader>

                            <TableBody>
                                {loading ? (
                                    [...Array(6)].map((_, i) => (
                                        <TableRow key={i}>
                                            <TableCell colSpan={6}>
                                                <div className="h-4 w-full animate-pulse rounded bg-muted" />
                                            </TableCell>
                                        </TableRow>
                                    ))
                                ) : currentRows.length === 0 ? (
                                    <TableRow>
                                        <TableCell
                                            colSpan={6}
                                            className="text-center text-muted-foreground"
                                        >
                                            Data tidak ditemukan
                                        </TableCell>
                                    </TableRow>
                                ) : (
                                    currentRows.map((item, index) => (
                                        <TableRow key={item.id}>
                                            <TableCell>
                                                {indexOfFirstRow + index + 1}
                                            </TableCell>
                                            <TableCell className="font-medium">
                                                {item.mid_barang}
                                            </TableCell>
                                            <TableCell>
                                                {item.nama_barang}
                                            </TableCell>
                                            <TableCell>{item.uom}</TableCell>
                                            <TableCell>
                                                <Badge
                                                    variant={
                                                        item.qty_soh > 0
                                                            ? "info"
                                                            : "soft_destructive"
                                                    }
                                                >
                                                    {item.qty_soh}
                                                </Badge>
                                            </TableCell>
                                            <TableCell>
                                                {item.last_update}
                                            </TableCell>
                                        </TableRow>
                                    ))
                                )}
                            </TableBody>
                        </Table>
                    </div>
                </div>

                {/* Pagination */}
                <div className="flex items-center justify-center gap-4 pt-4">
                    <Button
                        variant="outline"
                        size="sm"
                        disabled={currentPage === 1}
                        onClick={() => setCurrentPage(currentPage - 1)}
                    >
                        Prev
                    </Button>

                    <span className="text-sm">
                        Page <b>{currentPage}</b> of <b>{totalPages}</b>
                    </span>

                    <Button
                        variant="outline"
                        size="sm"
                        disabled={currentPage === totalPages}
                        onClick={() => setCurrentPage(currentPage + 1)}
                    >
                        Next
                    </Button>
                </div>
            </CardContent>
        </Card>
    );
};

export default StockOnHandTable;
