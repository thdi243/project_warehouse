import {
    Toast,
    ToastTitle,
    ToastDescription,
    ToastClose,
} from "@/components/ui/toast";
import { useToast } from "@/hooks/use-toast";

export function ToastRenderer() {
    const { toasts } = useToast();

    return (
        <>
            {toasts.map((toast) => (
                <Toast
                    key={toast.id}
                    variant={toast.variant}
                    open={toast.open}
                    onOpenChange={toast.onOpenChange}
                >
                    <div>
                        {toast.title && <ToastTitle>{toast.title}</ToastTitle>}
                        {toast.description && (
                            <ToastDescription>
                                {toast.description}
                            </ToastDescription>
                        )}
                    </div>
                    <ToastClose />
                </Toast>
            ))}
        </>
    );
}
