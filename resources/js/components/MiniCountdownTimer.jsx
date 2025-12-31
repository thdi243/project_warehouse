import { Clock } from "lucide-react";
import { useCountdown } from "@/hooks/use-countdown";

export function MiniCountdownTimer({ expiredAt }) {
    const time = useCountdown(expiredAt);

    if (!time) return null;

    return (
        <div
            className={`text-lg tabular-nums px-3 py-1 rounded-md font-semibold flex items-center gap-1
            ${
                time.isExpiringSoon
                    ? "border-destructive bg-destructive/10 text-destructive"
                    : "border-default bg-blue-100 text-muted-foreground"
            }`}
        >
            <Clock className="h-4 w-4" />
            {String(time.minutes).padStart(2, "0")}:
            {String(time.seconds).padStart(2, "0")}
        </div>
    );
}
