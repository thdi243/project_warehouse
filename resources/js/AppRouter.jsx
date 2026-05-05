import { Routes, Route, Navigate } from "react-router-dom";
import PurchaseRequisition from "./pages/PurchaseRequesitionForm";
import Stock from "./pages/StockOnHand";
import RiwayatPR from "./pages/RiwayatPR";
import ApprovalPR from "./pages/ApprovalPR";

export default function App() {
    return (
        <Routes>
            <Route
                path="/app/purchase-requesition/form"
                element={<PurchaseRequisition />}
            />
            <Route path="/app/stock-on-hand" element={<Stock />} />
            <Route path="/app/riwayat-pr" element={<RiwayatPR />} />
            <Route path="/app/approval-pr/:id" element={<ApprovalPR />} />
            <Route path="*" element={<Navigate to="/dashboard" />} />
        </Routes>
    );
}
