import { useEffect, useState } from "react";
import { Input } from "@/components/ui/input";
import { Card } from "@/components/ui/card";

export default function MidSearch({ value, onChange }) {
    const [keyword, setKeyword] = useState("");
    const [selectedMid, setSelectedMid] = useState(value || "");
    const [results, setResults] = useState([]);

    useEffect(() => {
        setSelectedMid(value || "");
    }, [value]);

    useEffect(() => {
        if (selectedMid || keyword.length < 2) {
            setResults([]);
            return;
        }

        const delay = setTimeout(async () => {
            const res = await fetch(
                `/api/purchase-requesition/getBarang/search?keyword=${keyword}`
            );
            const data = await res.json();
            setResults(data.data || []);
        }, 400);

        return () => clearTimeout(delay);
    }, [keyword, selectedMid]);

    return (
        <div className="relative">
            <Input
                placeholder="Cari MID…"
                value={selectedMid || keyword}
                onChange={(e) => {
                    setSelectedMid("");
                    setKeyword(e.target.value);
                }}
            />

            {results.length > 0 && (
                <Card className="absolute z-50 w-full mt-1 divide-y">
                    {results.map((item) => (
                        <button
                            type="button"
                            key={item.mid_barang}
                            className="w-full text-left px-3 py-2 hover:bg-muted text-sm"
                            onClick={() => {
                                setSelectedMid(item.mid_barang);
                                setKeyword("");
                                setResults([]);
                                onChange({
                                    mid: item.mid_barang,
                                    nama_barang: item.nama_barang,
                                });
                            }}
                        >
                            <b>{item.mid_barang}</b> — {item.nama_barang}
                        </button>
                    ))}
                </Card>
            )}
        </div>
    );
}
