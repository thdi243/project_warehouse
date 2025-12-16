import "../css/app.css";
import React from "react";
import ReactDOM from "react-dom/client";
import { BrowserRouter } from "react-router-dom";
import { AuthProvider } from "@/context/AuthContext";
import AppRouter from "./AppRouter";
import Layout from "@/components/layout/AppLayout";

ReactDOM.createRoot(document.getElementById("root")).render(
    <React.StrictMode>
        <AuthProvider>
            <BrowserRouter>
                <Layout>
                    <AppRouter />
                </Layout>
            </BrowserRouter>
        </AuthProvider>
    </React.StrictMode>
);
