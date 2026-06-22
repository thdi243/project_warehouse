import { useEffect, useState } from "react";
import { Bell, CheckCheck, Circle } from "lucide-react";

import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";

import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
    DropdownMenuItem,
} from "@/components/ui/dropdown-menu";
import { useAuth } from "@/context/AuthContext";
import { useToast } from "@/hooks/use-toast";

export default function NotificationBell() {
    const { user } = useAuth();
    const { toast } = useToast();
    const [notifications, setNotifications] = useState([]);

    const unreadCount = notifications.filter((n) => !n.is_read).length;

    const fetchNotifications = async () => {
        try {
            const res = await fetch("/notifications/notif");
            const data = await res.json();

            setNotifications(data);
        } catch (error) {
            console.error(error);
        }
    };

    useEffect(() => {
        fetchNotifications();

        const interval = setInterval(() => {
            fetchNotifications();
        }, 180000);

        return () => clearInterval(interval);
    }, []);

    useEffect(() => {
        if (window.Echo && user) {
            console.log("Setting up Reverb channel listener for user:", user.id);
            const channel = window.Echo.channel("portal-notifications");

            channel.listen(".new-notification", (data) => {
                console.log("Real-time notification received (React):", data);
                if (data && parseInt(data.user_id) === parseInt(user.id)) {
                    setNotifications((prev) => {
                        if (prev.some((n) => n.id === data.id)) return prev;
                        return [data, ...prev];
                    });

                    toast({
                        title: data.title,
                        description: data.message,
                    });
                }
            });

            return () => {
                channel.stopListening(".new-notification");
            };
        }
    }, [user, toast]);

    const handleOpenNotification = async (notif) => {
        try {
            if (!notif.is_read) {
                await fetch(`/api/notifications/read/${notif.id}`, {
                    method: "POST",
                });
            }

            window.location.href = notif.url;
        } catch (error) {
            console.error(error);
        }
    };

    return (
        <DropdownMenu>
            <DropdownMenuTrigger asChild>
                <Button variant="ghost" size="icon" className="relative">
                    <Bell className="h-5 w-5" />

                    {unreadCount > 0 && (
                        <span className="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full px-1.5">
                            {unreadCount}
                        </span>
                    )}
                </Button>
            </DropdownMenuTrigger>

            <DropdownMenuContent
                align="end"
                className="w-80 max-h-[400px] overflow-y-auto"
            >
                <DropdownMenuLabel>Notifications</DropdownMenuLabel>
                <DropdownMenuSeparator />

                {notifications.length === 0 && (
                    <div className="p-4 text-sm text-muted-foreground text-center">
                        Tidak ada notifikasi
                    </div>
                )}

                {notifications.map((notif) => (
                    <DropdownMenuItem
                        key={notif.id}
                        className="relative flex flex-col items-start cursor-pointer py-3"
                        onClick={() => handleOpenNotification(notif)}
                    >
                        {/* STATUS ICON */}
                        <div className="absolute right-2 top-2">
                            {notif.is_read ? (
                                <CheckCheck className="h-4 w-4 text-green-500" />
                            ) : (
                                <Circle className="h-3 w-3 fill-red-500 text-red-500" />
                            )}
                        </div>

                        {/* TITLE */}
                        <span
                            className={`text-sm ${
                                notif.is_read ? "font-normal" : "font-semibold"
                            }`}
                        >
                            {notif.title}
                        </span>

                        {/* MESSAGE */}
                        <span className="text-xs text-muted-foreground">
                            {notif.message}
                        </span>

                        {/* TIME */}
                        <span className="text-xs text-muted-foreground">
                            {notif.created_at}
                        </span>
                    </DropdownMenuItem>
                ))}
            </DropdownMenuContent>
        </DropdownMenu>
    );
}
