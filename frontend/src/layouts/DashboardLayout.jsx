import React, { useEffect, useState } from 'react';
import { Outlet, Link, useLocation, useNavigate } from 'react-router-dom';
import { useAuthStore } from '../store/useAuthStore';
import { useBranchStore } from '../store/useBranchStore';
import {
  LayoutDashboard,
  ShoppingCart,
  Package,
  Truck,
  Users,
  Building2,
  UserCheck,
  LogOut,
  ExternalLink,
  ChevronDown,
  Menu,
  X,
  ShieldAlert,
  ShieldCheck,
} from 'lucide-react';

export default function DashboardLayout() {
  const location = useLocation();
  const navigate = useNavigate();
  const { user, logout, hasPermission } = useAuthStore();
  const { branches, activeBranch, activeBranchData, fetchBranches, setActiveBranch } = useBranchStore();
  const [mobileMenuOpen, setMobileMenuOpen] = useState(false);

  useEffect(() => {
    fetchBranches();
  }, []);

  const handleLogout = () => {
    logout();
    navigate('/login');
  };

  const navItems = [
    { label: 'Dashboard', path: '/', icon: LayoutDashboard },
    { label: 'POS Terminal', path: '/pos', icon: ShoppingCart },
    { label: 'Stock & Inventory', path: '/stock', icon: Package },
    { label: 'Shipments & Transfers', path: '/shipments', icon: Truck },
    { label: 'Customers & Debts', path: '/customers', icon: Users },
  ];

  if (user?.isGlobalAdmin || user?.role === 'Admin') {
    navItems.push({ label: 'Management', path: '/management', icon: ShieldCheck });
    navItems.push({ label: 'Staff & Roles', path: '/staff', icon: UserCheck });
    navItems.push({ label: 'Branch Management', path: '/branches', icon: Building2 });
  }

  return (
    <div className="min-h-screen bg-slate-50 flex flex-col md:flex-row">
      {/* Sidebar for Desktop */}
      <aside className="hidden md:flex flex-col w-64 bg-slate-900 text-slate-100 p-4 border-r border-slate-800 shrink-0">
        {/* Brand */}
        <div className="flex items-center gap-3 px-2 py-3 border-b border-slate-800 mb-6">
          <div className="w-10 h-10 rounded-lg bg-indigo-600 flex items-center justify-center font-bold text-white text-xl shadow-md">
            M
          </div>
          <div>
            <h1 className="text-base font-bold tracking-tight text-white m-0">MURG TEXTILE</h1>
            <p className="text-xs text-slate-400 m-0">Retail & Wholesale</p>
          </div>
        </div>

        {/* Navigation Links */}
        <nav className="flex-1 space-y-1">
          {navItems.map((item) => {
            const Icon = item.icon;
            const active = location.pathname === item.path;
            return (
              <Link
                key={item.path}
                to={item.path}
                className={`flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all ${
                  active
                    ? 'bg-indigo-600 text-white shadow-sm'
                    : 'text-slate-300 hover:bg-slate-800 hover:text-white'
                }`}
              >
                <Icon className="w-4 h-4 shrink-0" />
                <span>{item.label}</span>
              </Link>
            );
          })}
        </nav>

        {/* Legacy Portal Link for zero-disruption coexistence */}
        <div className="pt-4 border-t border-slate-800 mt-auto space-y-2">
          <a
            href="/system"
            target="_blank"
            rel="noreferrer"
            className="flex items-center justify-between px-3 py-2 rounded-lg text-xs font-medium text-slate-400 hover:bg-slate-800 hover:text-slate-200 transition-colors"
          >
            <span>Legacy PHP System</span>
            <ExternalLink className="w-3.5 h-3.5" />
          </a>

          <button
            onClick={handleLogout}
            className="flex items-center gap-2 w-full px-3 py-2 rounded-lg text-xs font-medium text-rose-400 hover:bg-rose-950/40 hover:text-rose-300 transition-colors"
          >
            <LogOut className="w-3.5 h-3.5" />
            <span>Sign Out</span>
          </button>
        </div>
      </aside>

      {/* Main Content Area */}
      <div className="flex-1 flex flex-col min-w-0">
        {/* Top Navbar */}
        <header className="bg-white border-b border-slate-200 px-4 py-3 flex items-center justify-between sticky top-0 z-20 shadow-xs">
          {/* Mobile menu button */}
          <button
            onClick={() => setMobileMenuOpen(!mobileMenuOpen)}
            className="md:hidden p-2 rounded-lg text-slate-600 hover:bg-slate-100"
          >
            {mobileMenuOpen ? <X className="w-6 h-6" /> : <Menu className="w-6 h-6" />}
          </button>

          {/* Branch Context Selector / Indicator */}
          <div className="flex items-center gap-3">
            <span className="text-xs font-semibold uppercase tracking-wider text-slate-400 hidden sm:inline">
              Branch:
            </span>
            {user?.isGlobalAdmin ? (
              <div className="relative">
                <select
                  value={activeBranch || ''}
                  onChange={(e) => setActiveBranch(e.target.value)}
                  className="bg-slate-100 hover:bg-slate-200 text-slate-900 text-sm font-semibold py-1.5 px-3 pr-8 rounded-md border border-slate-300 appearance-none focus:outline-hidden focus:ring-2 focus:ring-indigo-500 cursor-pointer"
                >
                  {branches.map((b) => (
                    <option key={b.facilityID} value={b.facilityID}>
                      {b.name} ({b.facilityID})
                    </option>
                  ))}
                </select>
                <ChevronDown className="w-4 h-4 text-slate-500 absolute right-2.5 top-2.5 pointer-events-none" />
              </div>
            ) : (
              <div className="inline-flex items-center gap-1.5 bg-indigo-50 text-indigo-700 font-semibold px-2.5 py-1 rounded-md text-xs border border-indigo-200">
                <Building2 className="w-3.5 h-3.5" />
                <span>{activeBranchData?.name || user?.facilityID}</span>
              </div>
            )}
          </div>

          {/* User Profile & Badges */}
          <div className="flex items-center gap-3">
            <div className="text-right hidden sm:block">
              <p className="text-sm font-semibold text-slate-800 leading-tight m-0">{user?.name}</p>
              <span className={`inline-block text-[11px] font-bold px-1.5 py-0.2 rounded ${
                user?.role === 'Admin' ? 'bg-purple-100 text-purple-700' :
                user?.role === 'Sub-admin' ? 'bg-blue-100 text-blue-700' :
                'bg-emerald-100 text-emerald-700'
              }`}>
                {user?.role === 'Admin' ? 'Global Admin' : user?.role === 'Sub-admin' ? 'Branch Manager' : 'Cashier'}
              </span>
            </div>

            <div className="w-9 h-9 rounded-full bg-slate-200 flex items-center justify-center font-bold text-slate-700 text-sm">
              {user?.name?.charAt(0) || 'U'}
            </div>
          </div>
        </header>

        {/* Mobile Dropdown Menu */}
        {mobileMenuOpen && (
          <div className="md:hidden bg-slate-900 text-slate-100 p-4 border-b border-slate-800 space-y-2">
            {navItems.map((item) => (
              <Link
                key={item.path}
                to={item.path}
                onClick={() => setMobileMenuOpen(false)}
                className="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium hover:bg-slate-800"
              >
                <item.icon className="w-4 h-4" />
                <span>{item.label}</span>
              </Link>
            ))}
            <button
              onClick={handleLogout}
              className="flex items-center gap-2 w-full px-3 py-2 text-sm text-rose-400 hover:bg-slate-800"
            >
              <LogOut className="w-4 h-4" />
              <span>Sign Out</span>
            </button>
          </div>
        )}

        {/* Page Content */}
        <main className="flex-1 p-4 md:p-6 overflow-auto">
          <Outlet />
        </main>
      </div>
    </div>
  );
}
