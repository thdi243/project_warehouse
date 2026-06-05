import { useState, useEffect } from "react";
import { useToast } from "@/hooks/use-toast";
import Swal from "sweetalert2";

export function useBookingManager() {
    const [items, setItems] = useState([]);
    const [expiredAt, setExpiredAt] = useState(null);
    const { toast } = useToast();

    // Helper: Get or create session ID
    const getOrCreateSessionId = () => {
        let sessionId = sessionStorage.getItem("pr_session_id");
        if (!sessionId) {
            sessionId = `PR-${Date.now()}-${Math.random().toString(36).substr(2, 9)}`;
            sessionStorage.setItem("pr_session_id", sessionId);
        }
        return sessionId;
    };

    // Add item dengan booking
    const addItem = async (currentItem, type = "pr") => {
        if (!currentItem.mid || !currentItem.qty || !currentItem.keterangan) {
            Swal.fire({
                icon: "error",
                title: "Error!",
                text: "Harap lengkapi semua field barang sebelum menambahkan.",
                confirmButtonText: "OK",
                confirmButtonColor: "#ef4444",
            });
            return false;
        }

        try {
            const reserveRes = await fetch("/purchase-requesition/reserved", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": document
                        .querySelector('meta[name="csrf-token"]')
                        .getAttribute("content"),
                },
                body: JSON.stringify({
                    mid: currentItem.mid,
                    qty: currentItem.qty,
                    type: type,
                    session_id: getOrCreateSessionId(),
                }),
            });

            const reserveData = await reserveRes.json();

            if (!reserveRes.ok) {
                throw new Error(reserveData.message || "Gagal menambahkan barang");
            }

            // Set expired time dari response (hanya untuk item pertama)
            if (reserveData.expired_at && items.length === 0) {
                setExpiredAt(reserveData.expired_at);
            }

            // Tambahkan item ke list
            setItems((prev) => [
                ...prev,
                {
                    ...currentItem,
                    reservation_id: reserveData.reservation_id,
                },
            ]);

            Swal.fire({
                icon: "success",
                title: "Berhasil!",
                text: `${currentItem.nama_barang} berhasil ditambahkan`,
                confirmButtonText: "OK",
                confirmButtonColor: "#10b981",
                timer: 2000,
                timerProgressBar: true,
            });

            return true;
        } catch (err) {
            Swal.fire({
                icon: "error",
                title: "Error!",
                text: err.message || "Terjadi kesalahan saat menambahkan barang.",
                confirmButtonText: "OK",
                confirmButtonColor: "#ef4444",
            });
            return false;
        }
    };

    // Remove item
    const removeItem = async (index) => {
        const item = items[index];

        try {
            await fetch(`/purchase-requesition/release/${item.reservation_id}`, {
                method: "DELETE",
                headers: {
                    "X-CSRF-TOKEN": document
                        .querySelector('meta[name="csrf-token"]')
                        .getAttribute("content"),
                },
            });

            const newItems = items.filter((_, i) => i !== index);
            setItems(newItems);

            // Reset countdown jika tidak ada items lagi
            if (newItems.length === 0) {
                setExpiredAt(null);
                sessionStorage.removeItem("pr_session_id");
            }

            Swal.fire({
                icon: "success",
                title: "Item dihapus!",
                text: "Barang dihapus dari booking",
                confirmButtonText: "OK",
                confirmButtonColor: "#10b981",
                timer: 1000,
                timerProgressBar: true,
            });
        } catch (err) {
            Swal.fire({
                icon: "error",
                title: "Error!",
                text: err.message || "Terjadi kesalahan.",
                confirmButtonText: "OK",
                confirmButtonColor: "#ef4444",
            });
        }
    };

    // Handle booking expired
    const handleExpired = () => {
        Swal.fire({
            icon: "warning",
            title: "PR Time Expired!",
            text: "PR time Anda telah habis. Silakan PR ulang.",
            confirmButtonText: "OK",
            confirmButtonColor: "#eab308",
        });
        setItems([]);
        setExpiredAt(null);
        sessionStorage.removeItem("pr_session_id");
    };

    // Clear all items
    const clearItems = () => {
        setItems([]);
        setExpiredAt(null);
        sessionStorage.removeItem("pr_session_id");
    };

    const loadExistingReservation = async () => {
        const sessionId = sessionStorage.getItem("pr_session_id");
        if (!sessionId) return;

        try {
            const res = await fetch(
                `/purchase-requesition/my-reservations?session_id=${sessionId}`,
                {
                    method: "GET",
                    credentials: "same-origin",
                }
            );

            if (!res.ok) return;

            const data = await res.json();

            if (!data.items || data.items.length === 0) return;

            setItems(
                data.items.map((item) => ({
                    mid: item.mid_barang,
                    nama_barang: item.barang.nama_barang ?? "",
                    qty: item.qty,
                    keterangan: item.keterangan ?? "",
                    uom: item.barang?.uom ?? "",
                    reservation_id: item.id,
                }))
            );

            setExpiredAt(data.expired_at);
        } catch (err) {
            console.error("Gagal load reservasi:", err);
        }
    };

    // Cleanup on unmount
    // useEffect(() => {
    //     return () => {
    //         const sessionId = sessionStorage.getItem("pr_session_id");
    //         if (sessionId && items.length > 0) {
    //             fetch(`/purchase-requesition/release-session/${sessionId}`, {
    //                 method: "DELETE",
    //                 headers: {
    //                     "X-CSRF-TOKEN": document
    //                         .querySelector('meta[name="csrf-token"]')
    //                         .getAttribute("content"),
    //                 },
    //             });
    //         }
    //     };
    // }, [items]);

    useEffect(() => {
        loadExistingReservation();
    }, []);

    return {
        items,
        expiredAt,
        addItem,
        removeItem,
        handleExpired,
        clearItems,
        getSessionId: getOrCreateSessionId,
    };
}