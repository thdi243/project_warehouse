import { Button } from "@/components/ui/button";
import { Card, CardHeader, CardContent, CardTitle } from "@/components/ui/card";
import { Separator } from "@/components/ui/separator";
import { HiTrash } from "react-icons/hi";
import { Clock } from "lucide-react";
import { MiniCountdownTimer } from "../components/MiniCountdownTimer";

export default function BookingSummary({
    form,
    items,
    loading,
    expiredAt,
    onRemoveItem,
    formatDate,
}) {
    return (
        <Card className="h-fit sticky top-0">
            <CardHeader>
                <CardTitle className="font-bold flex items-center justify-between">
                    <span>Summary</span>
                    <MiniCountdownTimer expiredAt={expiredAt} />
                </CardTitle>
            </CardHeader>

            <CardContent className="space-y-5 text-sm">
                {/* Informasi PR */}
                <div className="grid grid-cols-2 gap-y-2">
                    <span className="text-muted-foreground">Tanggal</span>
                    <span className="font-medium">
                        {form.pr_date ? formatDate(form.pr_date) : "-"}
                    </span>

                    <span className="text-muted-foreground">User</span>
                    <span className="font-medium">
                        {form.requested_by || "-"}
                    </span>

                    <span className="text-muted-foreground">Departemen</span>
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

                {/* Daftar Items */}
                {items.length === 0 ? (
                    <div className="text-center py-8 text-muted-foreground">
                        <Clock className="h-12 w-12 mx-auto mb-2 opacity-20" />
                        <p>Belum ada barang ditambahkan</p>
                    </div>
                ) : (
                    <div className="space-y-3">
                        {items.map((item, i) => (
                            <div
                                key={i}
                                className="flex justify-between gap-3 rounded-md border p-3 hover:bg-muted/50 transition-colors"
                            >
                                <div className="flex-1 min-w-0">
                                    <p className="font-medium truncate">
                                        {item.mid} – {item.nama_barang}
                                    </p>
                                    <p className="text-xs text-muted-foreground">
                                        Qty: {item.qty}
                                    </p>
                                    {item.keterangan && (
                                        <p className="text-xs text-muted-foreground truncate">
                                            Ket: {item.keterangan}
                                        </p>
                                    )}
                                </div>

                                <Button
                                    type="button"
                                    size="icon"
                                    variant="ghost"
                                    className="text-red-500 hover:bg-red-50 flex-shrink-0"
                                    onClick={() => onRemoveItem(i)}
                                >
                                    <HiTrash className="h-4 w-4" />
                                </Button>
                            </div>
                        ))}
                    </div>
                )}

                <Separator />

                {/* Submit Button */}
                <Button
                    type="submit"
                    className="w-full"
                    disabled={loading || items.length === 0}
                >
                    {loading ? "Menyimpan..." : "Submit PR"}
                </Button>

                {/* Info Countdown */}
                {expiredAt && items.length > 0 && (
                    <p className="text-xs text-center text-muted-foreground">
                        Booking akan expired dalam 15 menit
                    </p>
                )}
            </CardContent>
        </Card>
    );
}
