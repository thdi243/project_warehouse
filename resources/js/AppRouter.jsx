import { Routes, Route, Navigate } from "react-router-dom";
import PurchaseRequisition from "./pages/PurchaseRequesitionForm";
import Stock from "./pages/StockOnHand";

export default function App() {
    return (
        <Routes>
            <Route
                path="/app/purchase-requesition/form"
                element={<PurchaseRequisition />}
            />
            <Route path="/app/stock-on-hand" element={<Stock />} />
            <Route path="*" element={<Navigate to="/" />} />
        </Routes>
    );
}
