import { useEffect, useState } from "react";

export function useCountdown(expiredAt, onExpired) {
    const [time, setTime] = useState(null);

    useEffect(() => {
        if (!expiredAt) {
            setTime(null);
            return;
        }

        let expiredCalled = false;

        const update = () => {
            const now = Date.now();
            const end = new Date(expiredAt).getTime();
            const diff = end - now;

            if (diff <= 0) {
                setTime(null);

                if (onExpired && !expiredCalled) {
                    expiredCalled = true;
                    onExpired();
                }
                return;
            }

            setTime({
                minutes: Math.floor((diff % 3600000) / 60000),
                seconds: Math.floor((diff % 60000) / 1000),
                isExpiringSoon: diff <= 3 * 60 * 1000,
            });
        };

        update(); // ⬅ sync pertama (PENTING)
        const timer = setInterval(update, 1000);

        return () => clearInterval(timer);
    }, [expiredAt, onExpired]);

    return time;
}
