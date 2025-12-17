import "../css/app.css";
import "@/bootstrap";
import React from "react";
import ReactDOM from "react-dom/client";
import { BrowserRouter } from "react-router-dom";
import { AuthProvider } from "@/context/AuthContext";
import AppRouter from "./AppRouter";
import Layout from "@/components/layout/AppLayout";

ReactDOM.createRoot(document.getElementById("root")).render(
    <React.StrictMode>
        <AuthProvider>
            <BrowserRouter
                basename={new URL(import.meta.env.VITE_APP_URL).pathname}
            >
                <Layout>
                    <AppRouter />
                </Layout>
            </BrowserRouter>
        </AuthProvider>
    </React.StrictMode>
);
