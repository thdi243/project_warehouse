import { Alert, AlertDescription } from "@/components/ui/alert";
import { Clock, AlertTriangle } from "lucide-react";
import { useCountdown } from "@/hooks/use-countdown";

export default function CountdownTimer({ expiredAt, onExpired }) {
    const time = useCountdown(expiredAt, onExpired);

    if (!time) return null;

    return (
        <Alert
            variant={time.isExpiringSoon ? "warning" : "info"}
            className="transition-all duration-300"
        >
            <div className="flex items-center gap-3">
                {time.isExpiringSoon ? (
                    <AlertTriangle className="h-5 w-5 text-destructive animate-pulse" />
                ) : (
                    <Clock className="h-5 w-5 text-muted-foreground" />
                )}

                <div className="flex-1">
                    <div className="font-semibold text-base">
                        {time.isExpiringSoon
                            ? "⚠️ Booking Hampir Habis!"
                            : "⏰ Waktu Booking Tersisa"}
                    </div>
                    <AlertDescription className="text-sm mt-1 text-muted-foreground">
                        {time.isExpiringSoon
                            ? "Segera submit PR Anda sebelum booking expired!"
                            : "Selesaikan PR Anda sebelum waktu habis"}
                    </AlertDescription>
                </div>

                <div
                    className={`text-4xl font-bold tabular-nums ${
                        time.isExpiringSoon
                            ? "text-destructive animate-pulse"
                            : "text-foreground"
                    }`}
                >
                    {String(time.minutes).padStart(2, "0")}:
                    {String(time.seconds).padStart(2, "0")}
                </div>
            </div>
        </Alert>
    );
}
