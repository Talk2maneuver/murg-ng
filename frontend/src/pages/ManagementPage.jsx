import React, { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';
import api from '../services/api';
import { useAuthStore } from '../store/useAuthStore';
import {
  ShieldCheck,
  Building2,
  Users,
  Package,
  Truck,
  DollarSign,
  ShoppingCart,
  Receipt,
  FileText,
  Warehouse,
  RotateCcw,
  Activity,
  ExternalLink,
  RefreshCw,
  Search,
  Filter,
  CheckCircle2,
  AlertCircle,
  Clock,
  ChevronRight,
  Database,
  Server,
  Layers,
} from 'lucide-react';

export default function ManagementPage() {
  const { user } = useAuthStore();
  const [activeTab, setActiveTab] = useState('hub'); // 'hub', 'legacy', 'audit'
  const [overview, setOverview] = useState(null);
  const [auditLogs, setAuditLogs] = useState([]);
  const [auditTotal, setAuditTotal] = useState(0);
  const [loading, setLoading] = useState(true);
  const [launchingModule, setLaunchingModule] = useState(null);
  const [bridgeError, setBridgeError] = useState(null);
  const [auditActionFilter, setAuditActionFilter] = useState('');

  const fetchOverview = async () => {
    try {
      setLoading(true);
      const res = await api.get('/management/overview');
      setOverview(res.data.data);
    } catch (err) {
      console.error('Failed to load management overview:', err);
    } finally {
      setLoading(false);
    }
  };

  const fetchAuditLogs = async (action = '') => {
    try {
      const url = action ? `/management/audit-logs?action=${action}` : '/management/audit-logs';
      const res = await api.get(url);
      setAuditLogs(res.data.data.logs || []);
      setAuditTotal(res.data.data.total || 0);
    } catch (err) {
      console.error('Failed to load audit logs:', err);
    }
  };

  useEffect(() => {
    fetchOverview();
    fetchAuditLogs();
  }, []);

  const handleLaunchLegacy = async (targetPath, title) => {
    setLaunchingModule(title);
    setBridgeError(null);
    try {
      const res = await api.post('/management/bridge-ticket', { targetPath });
      const { bridgeUrl } = res.data.data;
      // Open in new tab or window through the reverse proxy
      window.open(bridgeUrl, '_blank', 'noopener,noreferrer');
    } catch (err) {
      console.error('Bridge ticket failed:', err);
      const msg = err.response?.data?.message || 'Failed to authenticate legacy session.';
      setBridgeError(`Error launching ${title}: ${msg}`);
    } finally {
      setLaunchingModule(null);
    }
  };

  const legacyModules = [
    {
      title: 'Expense Management',
      path: '/system/expense.php',
      desc: 'Record branch overheads, utilities, logistics and daily operational expenses.',
      icon: DollarSign,
      color: 'bg-emerald-500',
    },
    {
      title: 'Bank Deposits & Accounts',
      path: '/system/deposit.php',
      desc: 'Manage bank lodgments, customer account balances and deposit slips.',
      icon: Receipt,
      color: 'bg-blue-500',
    },
    {
      title: 'Supplier Purchases & Orders',
      path: '/system/purchase.php',
      desc: 'Purchase order processing, invoice attachments and supplier payables.',
      icon: ShoppingCart,
      color: 'bg-purple-500',
    },
    {
      title: 'Store & Warehouse Setup',
      path: '/system/store.php',
      desc: 'Configure physical storage sections, aisles and bulk storage racks.',
      icon: Warehouse,
      color: 'bg-amber-500',
    },
    {
      title: 'Product Returns',
      path: '/system/return.php',
      desc: 'Process damaged fabric claims, customer returns and stock reversals.',
      icon: RotateCcw,
      color: 'bg-rose-500',
    },
    {
      title: 'Legacy Comprehensive Reports',
      path: '/system/report.php',
      desc: 'Audited monthly financial summaries, sales history and ledger reconciliation.',
      icon: FileText,
      color: 'bg-indigo-500',
    },
  ];

  const modernModules = [
    {
      title: 'Branch Management',
      path: '/branches',
      desc: 'Create branches, toggle Dealer vs Per-Yard sales mode, assign locations.',
      icon: Building2,
      badge: `${overview?.branches?.active || 0} Active`,
      color: 'text-indigo-600 bg-indigo-50',
    },
    {
      title: 'Staff & Roles',
      path: '/staff',
      desc: 'User account creation, role assignments (Admin / Staff), security status.',
      icon: Users,
      badge: `${overview?.staff?.total || 0} Staff`,
      color: 'text-purple-600 bg-purple-50',
    },
    {
      title: 'Stock & Pricing Control',
      path: '/stock',
      desc: 'Catalog inventory, per-yard pricing overrides, belt-to-yard stock intake.',
      icon: Package,
      badge: `${overview?.inventory?.total_products || 0} SKUs`,
      color: 'text-emerald-600 bg-emerald-50',
    },
    {
      title: 'Shipments & Logistics',
      path: '/shipments',
      desc: 'Inter-branch stock transfers, dispatch locks, receiving confirmations.',
      icon: Truck,
      badge: `${overview?.shipments?.in_transit || 0} In Transit`,
      color: 'text-amber-600 bg-amber-50',
    },
    {
      title: 'Customers & Debts',
      path: '/customers',
      desc: 'Customer credit limits, outstanding ledger balances, debt collections.',
      icon: DollarSign,
      badge: `₦${(overview?.debts?.total_balance || 0).toLocaleString()}`,
      color: 'text-rose-600 bg-rose-50',
    },
    {
      title: 'POS Terminal',
      path: '/pos',
      desc: 'Point of sale terminal for retail per-yard and wholesale belt checkouts.',
      icon: ShoppingCart,
      badge: 'Active Terminal',
      color: 'text-cyan-600 bg-cyan-50',
    },
  ];

  return (
    <div className="space-y-6">
      {/* Top Banner / Header */}
      <div className="bg-white rounded-xl shadow-xs border border-slate-200 p-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div className="flex items-center gap-4">
          <div className="w-12 h-12 rounded-xl bg-indigo-600 text-white flex items-center justify-center shadow-md">
            <ShieldCheck className="w-6 h-6" />
          </div>
          <div>
            <div className="flex items-center gap-2">
              <h1 className="text-xl font-bold text-slate-900 m-0">Admin Management Center</h1>
              <span className="px-2 py-0.5 rounded text-xs font-semibold bg-purple-100 text-purple-700">
                Global Administrator
              </span>
            </div>
            <p className="text-xs text-slate-500 m-0 mt-0.5">
              Unified administrative command center for multi-branch operations, security, and legacy migration.
            </p>
          </div>
        </div>

        <button
          onClick={() => {
            fetchOverview();
            fetchAuditLogs(auditActionFilter);
          }}
          disabled={loading}
          className="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg border border-slate-300 text-slate-700 text-xs font-medium hover:bg-slate-50 transition-colors cursor-pointer"
        >
          <RefreshCw className={`w-3.5 h-3.5 ${loading ? 'animate-spin' : ''}`} />
          <span>Refresh Overview</span>
        </button>
      </div>

      {bridgeError && (
        <div className="bg-rose-50 border border-rose-200 text-rose-700 px-4 py-3 rounded-lg text-sm flex items-center gap-2">
          <AlertCircle className="w-4 h-4 shrink-0" />
          <span>{bridgeError}</span>
        </div>
      )}

      {/* KPI Overview Strip */}
      <div className="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
        <div className="bg-white p-4 rounded-xl border border-slate-200 shadow-xs">
          <p className="text-xs font-medium text-slate-500 m-0">Total Branches</p>
          <p className="text-lg font-bold text-slate-900 m-0 mt-1">
            {overview?.branches?.total ?? '—'}
          </p>
          <p className="text-[11px] text-emerald-600 font-medium m-0 mt-0.5">
            {overview?.branches?.active || 0} active
          </p>
        </div>

        <div className="bg-white p-4 rounded-xl border border-slate-200 shadow-xs">
          <p className="text-xs font-medium text-slate-500 m-0">Staff Members</p>
          <p className="text-lg font-bold text-slate-900 m-0 mt-1">
            {overview?.staff?.total ?? '—'}
          </p>
          <p className="text-[11px] text-slate-500 m-0 mt-0.5">
            {overview?.staff?.admins || 0} admins, {overview?.staff?.cashiers || 0} staff
          </p>
        </div>

        <div className="bg-white p-4 rounded-xl border border-slate-200 shadow-xs">
          <p className="text-xs font-medium text-slate-500 m-0">Catalog Products</p>
          <p className="text-lg font-bold text-slate-900 m-0 mt-1">
            {overview?.inventory?.total_products ?? '—'}
          </p>
          <p className="text-[11px] text-slate-500 m-0 mt-0.5">
            {(overview?.inventory?.total_units || 0).toLocaleString()} units
          </p>
        </div>

        <div className="bg-white p-4 rounded-xl border border-slate-200 shadow-xs">
          <p className="text-xs font-medium text-slate-500 m-0">Shipments</p>
          <p className="text-lg font-bold text-slate-900 m-0 mt-1">
            {overview?.shipments?.total ?? '—'}
          </p>
          <p className="text-[11px] text-amber-600 font-medium m-0 mt-0.5">
            {overview?.shipments?.in_transit || 0} in transit
          </p>
        </div>

        <div className="bg-white p-4 rounded-xl border border-slate-200 shadow-xs">
          <p className="text-xs font-medium text-slate-500 m-0">Outstanding Debts</p>
          <p className="text-lg font-bold text-slate-900 m-0 mt-1">
            ₦{(overview?.debts?.total_balance || 0).toLocaleString()}
          </p>
          <p className="text-[11px] text-rose-600 font-medium m-0 mt-0.5">
            {overview?.debts?.debtor_count || 0} debtors
          </p>
        </div>

        <div className="bg-white p-4 rounded-xl border border-slate-200 shadow-xs">
          <p className="text-xs font-medium text-slate-500 m-0">System Status</p>
          <div className="flex items-center gap-1.5 mt-1.5">
            <span className="w-2 h-2 rounded-full bg-emerald-500"></span>
            <span className="text-xs font-bold text-emerald-700">Healthy</span>
          </div>
          <p className="text-[11px] text-slate-500 m-0 mt-0.5">
            {overview?.audit?.total_logs || 0} audit logs
          </p>
        </div>
      </div>

      {/* Tabs Navigation */}
      <div className="flex border-b border-slate-200 bg-white px-4 rounded-t-xl">
        <button
          onClick={() => setActiveTab('hub')}
          className={`py-3 px-4 text-xs font-bold border-b-2 transition-colors cursor-pointer flex items-center gap-2 ${
            activeTab === 'hub'
              ? 'border-indigo-600 text-indigo-600'
              : 'border-transparent text-slate-500 hover:text-slate-800'
          }`}
        >
          <Layers className="w-4 h-4" />
          <span>Core Administration Hub</span>
        </button>

        <button
          onClick={() => setActiveTab('legacy')}
          className={`py-3 px-4 text-xs font-bold border-b-2 transition-colors cursor-pointer flex items-center gap-2 ${
            activeTab === 'legacy'
              ? 'border-indigo-600 text-indigo-600'
              : 'border-transparent text-slate-500 hover:text-slate-800'
          }`}
        >
          <Server className="w-4 h-4" />
          <span>Legacy Operations Gateway</span>
          <span className="px-1.5 py-0.2 rounded text-[10px] bg-slate-100 text-slate-600">
            PHP
          </span>
        </button>

        <button
          onClick={() => setActiveTab('audit')}
          className={`py-3 px-4 text-xs font-bold border-b-2 transition-colors cursor-pointer flex items-center gap-2 ${
            activeTab === 'audit'
              ? 'border-indigo-600 text-indigo-600'
              : 'border-transparent text-slate-500 hover:text-slate-800'
          }`}
        >
          <Activity className="w-4 h-4" />
          <span>Security & Audit Trail</span>
          <span className="px-1.5 py-0.2 rounded text-[10px] bg-purple-100 text-purple-700">
            {auditTotal}
          </span>
        </button>
      </div>

      {/* Tab 1: Core Administration Hub */}
      {activeTab === 'hub' && (
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
          {modernModules.map((m) => {
            const Icon = m.icon;
            return (
              <div
                key={m.title}
                className="bg-white rounded-xl border border-slate-200 p-5 shadow-xs flex flex-col justify-between hover:border-indigo-300 transition-all group"
              >
                <div>
                  <div className="flex items-center justify-between mb-3">
                    <div className={`w-10 h-10 rounded-lg flex items-center justify-center ${m.color}`}>
                      <Icon className="w-5 h-5" />
                    </div>
                    <span className="text-xs font-bold px-2 py-0.5 rounded-full bg-slate-100 text-slate-700">
                      {m.badge}
                    </span>
                  </div>
                  <h3 className="text-sm font-bold text-slate-900 m-0 group-hover:text-indigo-600 transition-colors">
                    {m.title}
                  </h3>
                  <p className="text-xs text-slate-500 m-0 mt-1 line-clamp-2 leading-relaxed">
                    {m.desc}
                  </p>
                </div>

                <div className="pt-4 border-t border-slate-100 mt-4">
                  <Link
                    to={m.path}
                    className="inline-flex items-center gap-1.5 text-xs font-semibold text-indigo-600 hover:text-indigo-700 group-hover:translate-x-0.5 transition-transform"
                  >
                    <span>Manage {m.title}</span>
                    <ChevronRight className="w-3.5 h-3.5" />
                  </Link>
                </div>
              </div>
            );
          })}
        </div>
      )}

      {/* Tab 2: Legacy Operations Gateway */}
      {activeTab === 'legacy' && (
        <div className="space-y-4">
          <div className="bg-amber-50 border border-amber-200 rounded-xl p-4 text-xs text-amber-800">
            <p className="font-bold m-0 flex items-center gap-2">
              <Server className="w-4 h-4 text-amber-600" />
              <span>Strangler-Fig Coexistence Architecture</span>
            </p>
            <p className="m-0 mt-1">
              These modules operate on the legacy PHP 8 / Apache engine. Clicking any module automatically issues a single-use cryptographically signed bridge ticket, initializes your authenticated session, and opens the legacy interface through the reverse proxy without prompting for login.
            </p>
          </div>

          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            {legacyModules.map((m) => {
              const Icon = m.icon;
              const isLaunching = launchingModule === m.title;
              return (
                <div
                  key={m.title}
                  className="bg-white rounded-xl border border-slate-200 p-5 shadow-xs flex flex-col justify-between hover:border-slate-300 transition-all"
                >
                  <div>
                    <div className="flex items-center justify-between mb-3">
                      <div className={`w-10 h-10 rounded-lg text-white flex items-center justify-center ${m.color} shadow-xs`}>
                        <Icon className="w-5 h-5" />
                      </div>
                      <span className="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded bg-slate-100 text-slate-600">
                        Legacy PHP
                      </span>
                    </div>
                    <h3 className="text-sm font-bold text-slate-900 m-0">{m.title}</h3>
                    <p className="text-xs text-slate-500 m-0 mt-1 leading-relaxed">{m.desc}</p>
                  </div>

                  <div className="pt-4 border-t border-slate-100 mt-4">
                    <button
                      onClick={() => handleLaunchLegacy(m.path, m.title)}
                      disabled={isLaunching}
                      className="w-full inline-flex items-center justify-center gap-2 px-3 py-2 rounded-lg bg-slate-900 text-white text-xs font-semibold hover:bg-slate-800 transition-colors cursor-pointer disabled:opacity-50"
                    >
                      {isLaunching ? (
                        <>
                          <RefreshCw className="w-3.5 h-3.5 animate-spin" />
                          <span>Establishing Session...</span>
                        </>
                      ) : (
                        <>
                          <span>Open {m.title}</span>
                          <ExternalLink className="w-3.5 h-3.5" />
                        </>
                      )}
                    </button>
                  </div>
                </div>
              );
            })}
          </div>
        </div>
      )}

      {/* Tab 3: Security & Audit Trail */}
      {activeTab === 'audit' && (
        <div className="bg-white rounded-xl border border-slate-200 shadow-xs overflow-hidden">
          <div className="p-4 border-b border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div className="flex items-center gap-2">
              <Activity className="w-4 h-4 text-purple-600" />
              <h3 className="text-sm font-bold text-slate-900 m-0">Live Audit Trail</h3>
              <span className="text-xs text-slate-500">({auditTotal} events recorded)</span>
            </div>

            <div className="flex items-center gap-2">
              <select
                value={auditActionFilter}
                onChange={(e) => {
                  setAuditActionFilter(e.target.value);
                  fetchAuditLogs(e.target.value);
                }}
                className="text-xs font-medium border border-slate-300 rounded-lg px-2.5 py-1.5 bg-white text-slate-700 cursor-pointer"
              >
                <option value="">All Actions</option>
                <option value="UNAUTHORIZED_PRICE_CHANGE_ATTEMPT">Unauthorized Price Attempts</option>
                <option value="LOGIN">Logins</option>
                <option value="STOCK_OUT_TRANSFER">Shipment Dispatches</option>
                <option value="STOCK_IN_TRANSFER">Shipment Receipts</option>
                <option value="STOCK_IN_SUPPLIER">Stock Intakes</option>
              </select>
            </div>
          </div>

          <div className="overflow-x-auto">
            <table className="w-full text-left text-xs">
              <thead className="bg-slate-50 text-slate-500 uppercase tracking-wider font-semibold border-b border-slate-200">
                <tr>
                  <th className="py-2.5 px-4">Timestamp</th>
                  <th className="py-2.5 px-4">Action</th>
                  <th className="py-2.5 px-4">User</th>
                  <th className="py-2.5 px-4">Branch</th>
                  <th className="py-2.5 px-4">Target Entity</th>
                  <th className="py-2.5 px-4">IP Address</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-slate-100 text-slate-700">
                {auditLogs.length === 0 ? (
                  <tr>
                    <td colSpan="6" className="py-8 text-center text-slate-400">
                      No audit events matching criteria.
                    </td>
                  </tr>
                ) : (
                  auditLogs.map((log) => {
                    const isAlert = log.action.includes('UNAUTHORIZED') || log.action.includes('REJECT');
                    return (
                      <tr key={log.id} className={isAlert ? 'bg-rose-50/50' : 'hover:bg-slate-50/60'}>
                        <td className="py-2.5 px-4 font-mono text-slate-500 whitespace-nowrap">
                          {new Date(log.created_at).toLocaleString()}
                        </td>
                        <td className="py-2.5 px-4">
                          <span
                            className={`px-2 py-0.5 rounded font-bold text-[10px] ${
                              isAlert
                                ? 'bg-rose-100 text-rose-700'
                                : 'bg-slate-100 text-slate-700'
                            }`}
                          >
                            {log.action}
                          </span>
                        </td>
                        <td className="py-2.5 px-4 font-semibold text-slate-900">
                          {log.user_name}
                        </td>
                        <td className="py-2.5 px-4 font-mono text-slate-600">
                          {log.facilityID || 'Global'}
                        </td>
                        <td className="py-2.5 px-4 font-mono text-slate-600">
                          {log.entity_type} #{log.entity_id}
                        </td>
                        <td className="py-2.5 px-4 font-mono text-slate-400">
                          {log.ip_address || '—'}
                        </td>
                      </tr>
                    );
                  })
                )}
              </tbody>
            </table>
          </div>
        </div>
      )}
    </div>
  );
}
