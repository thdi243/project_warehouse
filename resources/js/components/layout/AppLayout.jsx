import Topbar from "./Topbar";
import { ToastProvider, ToastViewport } from "@/components/ui/toast";

import { ToastRenderer } from "@/components/ToastRendered";

export default function AppLayout({ title, children }) {
    return (
        <ToastProvider>
            <div className="flex h-screen bg-muted/40">
                <div className="flex flex-col flex-1 min-w-0">
                    <Topbar />
                    <main className="flex-1 p-4 md:p-6 overflow-auto">
                        {children}
                    </main>
                </div>
            </div>

            <ToastRenderer />
            <ToastViewport />
        </ToastProvider>
    );
}
