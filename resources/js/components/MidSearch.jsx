import { useEffect, useState } from "react";
import { useBookingManager } from "../hooks/useBookingManager";
import { Input } from "@/components/ui/input";
import { Card } from "@/components/ui/card";
import { Badge } from "@/components/ui/badge";
import { AlertCircle, CheckCircle, Lock } from "lucide-react";

export default function MidSearch({ value, onChange }) {
    const [keyword, setKeyword] = useState("");
    const [selectedMid, setSelectedMid] = useState(value || "");
    const [results, setResults] = useState([]);
    const [loading, setLoading] = useState(false);
    const { getSessionId } = useBookingManager();

    useEffect(() => {
        setSelectedMid(value || "");
    }, [value]);

    useEffect(() => {
        if (selectedMid || keyword.length < 2) {
            setResults([]);
            return;
        }

        setLoading(true);
        const delay = setTimeout(async () => {
            try {
                const res = await fetch(
                    `/api/purchase-requesition/getBarang/search?keyword=${keyword}`,
                    {
                        headers: {
                            "X-Session-Id": getSessionId(),
                            Accept: "application/json",
                        },
                    }
                );
                const data = await res.json();
                setResults(data.data || []);
            } catch (err) {
                console.error("Search error:", err);
                setResults([]);
            } finally {
                setLoading(false);
            }
        }, 400);

        return () => clearTimeout(delay);
    }, [keyword, selectedMid]);

    const getStockBadge = (item) => {
        if (!item.is_available) {
            return (
                <Badge variant="destructive" className="gap-1">
                    <Lock className="h-3 w-3" />
                    Terkunci
                </Badge>
            );
        }

        if (item.reserved_qty > 0) {
            return (
                <Badge
                    variant="warning"
                    className="gap-1 bg-yellow-100 text-yellow-800 border-yellow-300"
                >
                    <AlertCircle className="h-3 w-3" />
                    Tersisa {item.available_qty}
                </Badge>
            );
        }

        return (
            <Badge
                variant="success"
                className="gap-1 bg-green-100 text-green-800 border-green-300"
            >
                <CheckCircle className="h-3 w-3" />
                {item.available_qty} Tersedia
            </Badge>
        );
    };

    return (
        <div className="relative">
            <Input
                placeholder="Cari MID atau nama barang (min. 2 karakter)..."
                value={selectedMid || keyword}
                onChange={(e) => {
                    setSelectedMid("");
                    setKeyword(e.target.value);
                }}
                className={selectedMid ? "border-green-500" : ""}
            />

            {loading && (
                <div className="absolute right-3 top-3 text-muted-foreground text-sm">
                    Mencari...
                </div>
            )}

            {results.length > 0 && (
                <Card className="absolute z-50 w-full mt-1 divide-y max-h-[400px] overflow-y-auto shadow-lg">
                    {results.map((item) => {
                        const isDisabled = !item.is_available;

                        return (
                            <button
                                type="button"
                                key={item.barang.mid_barang}
                                className={`
                                    w-full text-left px-4 py-3 text-sm transition-colors
                                    ${
                                        isDisabled
                                            ? "opacity-50 cursor-not-allowed bg-gray-50"
                                            : "hover:bg-blue-50 cursor-pointer"
                                    }
                                `}
                                onClick={() => {
                                    if (isDisabled) return;

                                    setSelectedMid(item.barang.mid_barang);
                                    setKeyword("");
                                    setResults([]);
                                    onChange({
                                        mid: item.barang.mid_barang,
                                        nama_barang: item.barang.nama_barang,
                                        available_qty: item.available_qty,
                                        uom: item.barang.uom,
                                    });
                                }}
                                disabled={isDisabled}
                            >
                                <div className="flex items-start justify-between gap-3">
                                    <div className="flex-1 min-w-0">
                                        <div className="font-semibold text-base mb-1">
                                            {item.barang.mid_barang}
                                        </div>
                                        <div className="text-muted-foreground truncate">
                                            {item.barang.nama_barang}
                                        </div>
                                        <div className="text-xs text-muted-foreground mt-1">
                                            {item.availability_info}
                                        </div>
                                        {item.total_book_soh > 0 && (
                                            <div className="text-xs text-blue-600 font-semibold mt-1 flex items-center gap-1">
                                                <AlertCircle className="h-3 w-3" />
                                                Booked PR: {item.total_book_soh} {item.barang.uom}
                                            </div>
                                        )}
                                    </div>

                                    <div className="flex-shrink-0">
                                        {getStockBadge(item)}
                                    </div>
                                </div>

                                {isDisabled && (
                                    <div className="mt-2 text-xs text-red-600 flex items-center gap-1">
                                        <Lock className="h-3 w-3" />
                                        {item.booking_info}
                                    </div>
                                )}
                            </button>
                        );
                    })}
                </Card>
            )}

            {keyword.length >= 2 && !loading && results.length === 0 && (
                <Card className="absolute z-50 w-full mt-1 p-4 text-center text-muted-foreground text-sm">
                    Tidak ada hasil untuk "{keyword}"
                </Card>
            )}
        </div>
    );
}
