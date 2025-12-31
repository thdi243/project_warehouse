import { useEffect, useState } from "react";
import { data, useParams } from "react-router-dom";
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Textarea } from "@/components/ui/textarea";
import { Badge } from "@/components/ui/badge";
import SignatureModal from "@/components/SignatureModal";
import Swal from "sweetalert2";

export default function ApprovalPR() {
    const { id } = useParams();

    const [pr, setPr] = useState(null);
    const [comment, setComment] = useState("");
    const [loading, setLoading] = useState(false);

    const [showSignature, setShowSignature] = useState(false);
    const [pendingStatus, setPendingStatus] = useState(null);

    useEffect(() => {
        fetch(`/api/purchase-requesition/pr-data/approval/${id}`)
            .then((res) => res.json())
            .then((data) => setPr(data.data));
    }, [id]);

    const handleReject = async () => {
        if (!comment || comment.trim() === "") {
            Swal.fire({
                icon: "warning",
                title: "Alasan wajib diisi",
                text: "Harap tulis alasan penolakan PR sebelum melanjutkan.",
                confirmButtonText: "OK",
                confirmButtonColor: "#ef4444",
            });
            return;
        }

        const confirm = await Swal.fire({
            title: "Tolak PR Ini?",
            text: "PR akan ditolak dan tidak bisa diubah lagi.",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#ef4444",
            cancelButtonColor: "#6b7280",
            confirmButtonText: "Ya, Tolak",
            cancelButtonText: "Batal",
        });

        if (!confirm.isConfirmed) return;

        setPendingStatus("rejected");
        setLoading(true);

        try {
            const res = await fetch(
                `/api/purchase-requesition/approval-pr/action/${id}`,
                {
                    method: "POST",
                    credentials: "include",
                    headers: {
                        "Content-Type": "application/json",
                        Accept: "application/json",
                        "X-CSRF-TOKEN": document
                            .querySelector('meta[name="csrf-token"]')
                            ?.getAttribute("content"),
                    },
                    body: JSON.stringify({
                        status: "rejected",
                        comment: comment,
                        ttd: null,
                    }),
                }
            );

            const data = await res.json();

            if (!res.ok) {
                throw new Error(data.message || "Gagal menolak PR");
            }

            await Swal.fire({
                icon: "success",
                title: "PR Berhasil Ditolak!",
                text: "Alasan penolakan telah dikirim ke pemohon.",
                confirmButtonText: "OK",
                confirmButtonColor: "#10b981",
                timer: 4000,
                timerProgressBar: true,
            });

            await refreshPrData();

            setComment(""); // reset komentar
        } catch (err) {
            Swal.fire({
                icon: "error",
                title: "Gagal Menolak PR",
                text: err.message || "Terjadi kesalahan saat memproses.",
                confirmButtonText: "OK",
                confirmButtonColor: "#ef4444",
            });
        } finally {
            setLoading(false);
            setPendingStatus(null);
        }
    };

    const refreshPrData = async () => {
        const refreshRes = await fetch(
            `/api/purchase-requesition/pr-data/approval/${id}`
        );
        const freshData = await refreshRes.json();
        if (freshData.status === true) {
            setPr(freshData.data);
        }
    };

    if (!pr) return <div>Loading...</div>;

    return (
        <div className="space-y-6 max-w-5xl mx-auto">
            {/* HEADER */}
            <div className="flex justify-between items-center">
                <div>
                    <h1 className="text-xl font-bold">
                        Approval Purchase Requesition
                    </h1>
                    <p className="text-sm text-muted-foreground">
                        {pr.pr_number}
                    </p>
                </div>

                <Badge
                    variant={
                        pr.status === "pending"
                            ? "warning"
                            : pr.status === "approved"
                            ? "success"
                            : pr.status === "rejected"
                            ? "destructive"
                            : "default"
                    }
                >
                    {pr.status.toUpperCase()}
                </Badge>
            </div>

            {/* INFO PR */}
            <Card>
                <CardHeader>
                    <CardTitle>Informasi PR</CardTitle>
                </CardHeader>
                <CardContent className="grid grid-cols-2 gap-4">
                    <Info label="Pengaju" value={pr.requested_by} />
                    <Info label="Departemen" value={pr.department} />
                    <Info label="Tanggal PR" value={pr.pr_date} />
                    <Info label="Jenis" value={pr.jenis} />
                    <Info label="No IO" value={pr.no_io || "-"} />
                </CardContent>
            </Card>

            {/* ITEMS */}
            <Card>
                <CardHeader>
                    <CardTitle>Daftar Barang</CardTitle>
                </CardHeader>
                <CardContent>
                    <table className="w-full text-sm border">
                        <thead>
                            <tr className="bg-muted">
                                <th className="p-2 border">MID</th>
                                <th className="p-2 border">Desc</th>
                                <th className="p-2 border text-center">Qty</th>
                                <th className="p-2 border">Keterangan</th>
                            </tr>
                        </thead>
                        <tbody>
                            {pr.items.map((item) => (
                                <tr key={item.id}>
                                    <td className="p-2 border">
                                        {item.barang.mid_barang}
                                    </td>
                                    <td className="p-2 border">
                                        {item.barang.nama_barang}
                                    </td>
                                    <td className="p-2 border text-center">
                                        {item.qty}
                                    </td>
                                    <td className="p-2 border">
                                        {item.keterangan || "-"}
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </CardContent>
            </Card>

            {/* KOMENTAR + AKSI APPROVAL - SATU CARD TANPA HEADER */}
            {pr.can_approve && (
                <Card className="mt-6">
                    <CardContent className="pt-6">
                        {" "}
                        {/* pt-6 biar ada jarak atas tanpa header */}
                        {/* Bagian Komentar */}
                        <div className="mb-6">
                            <label className="text-sm font-medium mb-2 block">
                                Komentar{" "}
                                <span className="text-muted-foreground">
                                    (opsional)
                                </span>
                            </label>
                            <Textarea
                                placeholder="Tulis komentar Anda di sini..."
                                value={comment}
                                onChange={(e) => setComment(e.target.value)}
                                className="resize-none"
                                rows={4}
                            />
                        </div>
                        {/* Bagian Tombol Aksi */}
                        <div className="grid grid-cols-2 gap-4">
                            <Button
                                type="button"
                                variant="destructive"
                                size="lg"
                                className="h-14 text-lg font-semibold"
                                disabled={loading}
                                onClick={() => handleReject()}
                            >
                                {loading && pendingStatus === "rejected"
                                    ? "Memproses..."
                                    : "Reject"}
                            </Button>

                            <Button
                                type="button"
                                size="lg"
                                className="h-14 text-lg font-semibold bg-green-600 hover:bg-green-700"
                                disabled={loading}
                                onClick={() => {
                                    setPendingStatus("approved");
                                    setShowSignature(true);
                                }}
                            >
                                {loading && pendingStatus === "approved"
                                    ? "Memproses..."
                                    : "Approve"}
                            </Button>
                        </div>
                        {/* Loading indicator */}
                        {loading && (
                            <div className="text-center mt-6 text-muted-foreground font-medium">
                                Sedang memproses aksi approval...
                            </div>
                        )}
                    </CardContent>
                </Card>
            )}

            {/* STATUS ACTION ANDA - JIKA SUDAH PERNAH ACTION */}
            {!pr.can_approve && pr.user_has_acted && pr.user_action && (
                <Card className="mt-6 border-gray-200">
                    <CardContent className="pt-5 pb-6">
                        <div className="space-y-4">
                            {/* Status */}
                            <div className="flex items-center justify-between">
                                <h3 className="text-sm font-semibold">
                                    Status Persetujuan Anda
                                </h3>

                                <span
                                    className={`px-3 py-1 text-xs font-medium rounded-full ${
                                        pr.user_action.status === "approved"
                                            ? "bg-green-100 text-green-700"
                                            : "bg-red-100 text-red-700"
                                    }`}
                                >
                                    {pr.user_action.status === "approved"
                                        ? "Disetujui"
                                        : "Ditolak"}
                                </span>
                            </div>

                            {/* Detail */}
                            <div className="text-sm text-gray-600 space-y-1">
                                <p>
                                    <span>Oleh:</span>{" "}
                                    <span className="font-medium">
                                        {pr.user_action.approver
                                            ?.nama_lengkap || "Unknown"}
                                    </span>
                                </p>
                                <p>
                                    <span className="text-gray-500">
                                        Waktu:
                                    </span>{" "}
                                    {pr.user_action.action_at
                                        ? new Date(
                                              pr.user_action.action_at
                                          ).toLocaleString("id-ID", {
                                              dateStyle: "medium",
                                              timeStyle: "short",
                                          })
                                        : "-"}
                                </p>
                            </div>

                            {/* Komentar */}
                            {pr.user_action.catatan && (
                                <div className="border-l-4 border-gray-200 pl-4 text-sm text-gray-700">
                                    <p className="text-gray-500 mb-1">
                                        Catatan
                                    </p>
                                    <p className="italic">
                                        {pr.user_action.catatan}
                                    </p>
                                </div>
                            )}
                        </div>
                    </CardContent>
                </Card>
            )}

            {/* JIKA USER BUKAN APPROVER SAMA SEKALI */}
            {!pr.can_approve && !pr.user_has_acted && (
                <Card className="mt-8">
                    <CardContent className="pt-10 pb-12 text-center">
                        <p className="text-lg text-muted-foreground">
                            Anda tidak termasuk dalam alur approval untuk PR
                            ini.
                        </p>
                    </CardContent>
                </Card>
            )}

            {/* Modal Tanda Tangan untuk Approval */}
            <SignatureModal
                open={showSignature}
                onClose={() => {
                    setShowSignature(false);
                    setPendingStatus(null); // reset status kalau batal
                }}
                onSave={async (signatureBase64) => {
                    // Validasi wajib tanda tangan
                    if (!signatureBase64) {
                        Swal.fire({
                            icon: "warning",
                            title: "Warning!",
                            text: "Tanda tangan wajib diisi untuk melanjutkan.",
                            confirmButtonText: "OK",
                            confirmButtonColor: "#ef4444",
                        });
                        return;
                    }

                    setLoading(true);
                    try {
                        const res = await fetch(
                            `/api/purchase-requesition/approval-pr/action/${id}`,
                            {
                                method: "POST",
                                credentials: "include",
                                headers: {
                                    "Content-Type": "application/json",
                                    Accept: "application/json",
                                    "X-CSRF-TOKEN": document
                                        .querySelector(
                                            'meta[name="csrf-token"]'
                                        )
                                        .getAttribute("content"),
                                },
                                body: JSON.stringify({
                                    status: pendingStatus,
                                    comment: comment,
                                    ttd: signatureBase64,
                                }),
                            }
                        );

                        const data = await res.json();

                        if (!res.ok) {
                            throw new Error(
                                data.message || "Gagal menyimpan approval"
                            );
                        }

                        Swal.fire({
                            icon: "success",
                            title: "Berhasil!",
                            text: `PR berhasil di-${
                                pendingStatus === "approved"
                                    ? "disetujui"
                                    : "ditolak"
                            }`,
                            confirmButtonText: "OK",
                            confirmButtonColor: "#10b981",
                            timer: 4000,
                            timerProgressBar: true,
                        });

                        const refreshRes = await fetch(
                            `/api/purchase-requesition/pr-data/approval/${id}`
                        );
                        const freshData = await refreshRes.json();

                        if (freshData.status === true) {
                            setPr(freshData.data); // ini pasti fresh, lengkap, dan akurat
                        }

                        // Reset form
                        setComment("");
                    } catch (err) {
                        Swal.fire({
                            icon: "error",
                            title: "Error!",
                            text:
                                err.message ||
                                "Terjadi kesalahan saat memproses.",
                            confirmButtonText: "OK",
                            confirmButtonColor: "#ef4444",
                        });
                    } finally {
                        setLoading(false);
                        setShowSignature(false);
                        setPendingStatus(null);
                    }
                }}
            />
        </div>
    );
}

function Info({ label, value }) {
    return (
        <div>
            <p className="text-xs text-muted-foreground">{label}</p>
            <p className="font-medium">{value}</p>
        </div>
    );
}
