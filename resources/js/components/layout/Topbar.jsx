import { useEffect, useState } from "react";
import { Sun, Moon, User, LogOut, Menu } from "lucide-react";
import { Avatar, AvatarFallback, AvatarImage } from "@/components/ui/avatar";
import { useLocation } from "react-router-dom";
import { Link } from "react-router-dom";
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu";
import {
    NavigationMenu,
    NavigationMenuItem,
    NavigationMenuList,
    NavigationMenuLink,
} from "@/components/ui/navigation-menu";
import { Button } from "@/components/ui/button";
import { Sheet, SheetContent, SheetTrigger } from "@/components/ui/sheet";
import { cn } from "@/lib/utils";
import { getTheme, setTheme } from "@/lib/theme";
import { useAuth } from "@/context/AuthContext";

const navItems = [
    { label: "Dashboard", href: "/dashboard" },
    { label: "Purchase Requesition", href: "/app/purchase-requesition/form" },
    { label: "Stock On Hand", href: "/app/stock-on-hand" },
];

export default function Topbar() {
    const { user, loading, setUser } = useAuth();
    const location = useLocation();
    const [theme, setThemeState] = useState("light");

    const isActive = (link) => {
        return location.pathname.startsWith(link);
    };

    useEffect(() => {
        const currentTheme = getTheme();
        setThemeState(currentTheme);
        setTheme(currentTheme);
    }, []);

    const toggleTheme = () => {
        const next = theme === "dark" ? "light" : "dark";
        setThemeState(next);
        setTheme(next);
    };

    const handleLogout = async () => {
        await fetch("/logout", {
            method: "POST",
            credentials: "include",
            headers: {
                "X-CSRF-TOKEN": document
                    .querySelector('meta[name="csrf-token"]')
                    .getAttribute("content"),
                Accept: "application/json",
            },
        });

        setUser(null);
        window.location.href = "/login";
    };

    if (loading) return null;

    return (
        <header className="sticky top-0 z-50 border-b bg-background">
            <div className="flex h-14 items-center justify-between px-4 sm:px-6">
                {/* LEFT */}
                <div className="flex items-center gap-6">
                    {/* LOGO */}
                    <a to="/dashboard" className="flex items-center gap-2">
                        <img
                            src="/assets/images/logo/kecap.png"
                            alt="Logo"
                            className="h-12 w-auto"
                        />
                        <span className="font-semibold text-lg">WMS</span>
                    </a>
                </div>
                <div className="flex items-center gap-1">
                    <NavigationMenu className="hidden md:flex">
                        <NavigationMenuList>
                            {navItems.map((item) => {
                                const active = isActive(item.href);

                                return (
                                    <NavigationMenuItem key={item.href}>
                                        <NavigationMenuLink asChild>
                                            <Link
                                                to={item.href}
                                                className={cn(
                                                    "px-3 py-2 text-sm rounded-md transition",
                                                    active
                                                        ? "bg-accent text-foreground font-semibold"
                                                        : "text-muted-foreground hover:text-foreground hover:bg-accent"
                                                )}
                                            >
                                                {item.label}
                                            </Link>
                                        </NavigationMenuLink>
                                    </NavigationMenuItem>
                                );
                            })}
                        </NavigationMenuList>
                    </NavigationMenu>
                </div>

                {/* RIGHT */}
                <div className="flex items-center gap-1">
                    {/* THEME TOGGLE */}
                    <Button
                        variant="ghost"
                        size="icon"
                        onClick={toggleTheme}
                        className="rounded-full me-2"
                    >
                        {theme === "dark" ? (
                            <Sun className="h-8 w-8" />
                        ) : (
                            <Moon className="h-8 w-8" />
                        )}
                    </Button>

                    {/* USER */}
                    <DropdownMenu>
                        <DropdownMenuTrigger asChild>
                            <button className="rounded-full focus:outline-none focus:ring-2 focus:ring-ring">
                                <Avatar className="h-8 w-8">
                                    <AvatarImage src={user.image} />
                                    <AvatarFallback>
                                        {user.nama_lengkap?.[0]}
                                    </AvatarFallback>
                                </Avatar>
                            </button>
                        </DropdownMenuTrigger>

                        <DropdownMenuContent align="end" className="w-48">
                            <DropdownMenuLabel>
                                {user.nama_lengkap}
                            </DropdownMenuLabel>
                            <DropdownMenuSeparator />
                            <DropdownMenuItem>
                                <User className="mr-2 h-4 w-4" />
                                Profile
                            </DropdownMenuItem>
                            <DropdownMenuItem
                                className="text-red-600"
                                onClick={handleLogout}
                            >
                                <LogOut className="mr-2 h-4 w-4" />
                                Logout
                            </DropdownMenuItem>
                        </DropdownMenuContent>
                    </DropdownMenu>

                    {/* MOBILE NAV */}
                    <Sheet>
                        <SheetTrigger asChild>
                            <Button
                                variant="ghost"
                                size="icon"
                                className="md:hidden"
                            >
                                <Menu className="h-5 w-5" />
                            </Button>
                        </SheetTrigger>

                        <SheetContent side="left" className="w-64">
                            <nav className="mt-6 flex flex-col gap-1">
                                {navItems.map((item) => {
                                    const active = isActive(item.href);

                                    return (
                                        <a
                                            key={item.href}
                                            href={item.href}
                                            className={cn(
                                                "rounded-md px-3 py-2 text-sm transition",
                                                active
                                                    ? "bg-accent font-semibold"
                                                    : "hover:bg-accent"
                                            )}
                                        >
                                            {item.label}
                                        </a>
                                    );
                                })}
                            </nav>
                        </SheetContent>
                    </Sheet>
                </div>
            </div>
        </header>
    );
}
