import HonorariumRecordFilter from '@/components/honorarium-record-filter';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router, usePage } from '@inertiajs/react';
import { CalendarDays, CircleDollarSign, FileCheck2, FileText, Search, Send } from 'lucide-react';
import { useEffect, useState } from 'react';

type FinanceDocument = {
    type: string;
    status: string;
    submitted_at?: string | null;
    submitted_by?: string | null;
    reviewed_at?: string | null;
    reviewed_by?: string | null;
    reviewer_role?: string | null;
    finance_notes?: string | null;
};
type HonorariumRecipient = {
    name: string;
    role: string;
    status: string;
    school_year: string;
    semester: string;
    amount: number;
    updated_at?: string | null;
};
type Item = {
    archived: boolean;
    id: number;
    student_name: string;
    student_number?: string | null;
    program?: string | null;
    defense_type: string;
    workflow_state: string;
    scheduled_date?: string | null;
    scheduled_time?: string | null;
    receipt_url?: string | null;
    panel_names: string[];
    faculty_names: string[];
    finance_status: string;
    finance_documents: FinanceDocument[];
    honorarium_recipients: HonorariumRecipient[];
    school_year?: string | null;
    semester?: string | null;
    progress_stage: number;
    progress_percent: number;
    follow_ups: { id: number; recipient_role: string; notes: string; actor: string; occurred_at?: string | null }[];
    submitted_at?: string | null;
<<<<<<< Updated upstream
    last_updated_at?: string | null;
=======
    finance_submission_id?: number | null;
    follow_ups: { id: number; recipient_role: string; notes: string; actor: string; occurred_at?: string | null }[];
>>>>>>> Stashed changes
};
type Rate = { id: number; program_level: string; type: string; defense_type?: string | null; amount: number };
type ProgramReport = {
    program: string;
    school_year: string;
    semester: string;
    recipients_count: number;
    total_amount: number;
    prepared_amount: number;
    approved_amount: number;
    disbursed_amount: number;
    claimed_amount: number;
};
type Props = {
    canManageDocuments: boolean;
    role: string;
    canSubmitToFinance: boolean;
<<<<<<< Updated upstream
    items: Item[];
    rates: Rate[];
    facultyOptions: string[];
    terms: { school_year: string; semester: string; value: string }[];
    canFollowUp: boolean;
    canViewReports: boolean;
=======
    canVerifyFinance: boolean;
    canFollowUp: boolean;
    canViewReports: boolean;
    items: Item[];
    rates: Rate[];
>>>>>>> Stashed changes
    programReports: ProgramReport[];
    flash?: { success?: string };
};

const statusLabel: Record<string, string> = {
    not_submitted: 'Not sent to Finance',
    submitted: 'Awaiting Finance',
    verified: 'Finance verified',
    rejected: 'Finance rejected',
};
<<<<<<< Updated upstream
const progressSteps = ['Receipt', 'Panel breakdown', 'Prepare', 'Approve', 'Disburse', 'Claim', 'Complete'];
const formatDate = (value?: string | null) =>
    value ? new Intl.DateTimeFormat('en-PH', { dateStyle: 'medium', timeStyle: 'short', timeZone: 'Asia/Manila' }).format(new Date(value)) : '—';

function currentAction(item: Item): { owner: string; action: string } {
    const receipt = item.finance_documents.find((document) => document.type === 'payment_receipt');
    const breakdown = item.finance_documents.find((document) => document.type === 'panel_breakdown');

    if (!receipt || ['ready', 'rejected'].includes(receipt.status)) return { owner: 'AA', action: 'Send receipt to Finance' };
    if (receipt.status === 'submitted') return { owner: 'Finance', action: 'Verify receipt' };
    if (!breakdown || ['locked', 'ready', 'rejected'].includes(breakdown.status)) return { owner: 'AA', action: 'Send panel breakdown' };
    if (breakdown.status === 'submitted') return { owner: 'Finance', action: 'Verify panel breakdown' };
    if (!item.honorarium_recipients.length) return { owner: 'AA', action: 'Prepare honorarium records' };
    if (item.progress_stage === 5) return { owner: 'Dean / Finance', action: 'Approve honorarium records' };
    if (item.progress_stage === 6) return { owner: 'Finance', action: 'Record disbursement' };
    if (item.progress_stage === 7) return { owner: 'Recipients', action: 'Record claims' };

    return { owner: 'Complete', action: 'All honorarium steps are complete' };
}

function stepIndex(stage: number): number {
    if (stage <= 1) return 0;
    if (stage <= 3) return 1;
    if (stage === 4) return 2;
    if (stage === 5) return 3;
    if (stage === 6) return 4;
    if (stage === 7) return 5;

    return 6;
=======
function currentAction(item: Item): { owner: string; action: string } {
    if (item.finance_status === 'rejected') return { owner: 'AA', action: 'Update and resubmit documents' };

    const activeStep = item.progress.steps.findIndex((step) => !step.done);
    const actions = [
        { owner: 'AA', action: 'Send receipt to Finance' },
        { owner: 'Finance', action: 'Verify receipt' },
        { owner: 'AA', action: 'Send panel breakdown' },
        { owner: 'Finance', action: 'Verify panel breakdown' },
        { owner: 'AA', action: 'Prepare payment records' },
        { owner: 'Dean / Finance', action: 'Approve payments' },
        { owner: 'Finance', action: 'Record disbursement' },
        { owner: 'Finance', action: 'Record recipient claim' },
    ];

    return activeStep < 0 || activeStep >= actions.length ? { owner: 'Complete', action: 'All payments claimed' } : actions[activeStep];
>>>>>>> Stashed changes
}

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Honorarium Tracking', href: '/honorarium-workspace' }];

export default function HonorariumWorkspace() {
<<<<<<< Updated upstream
    const {
        canManageDocuments,
        role,
        canSubmitToFinance,
        items = [],
        rates = [],
        facultyOptions = [],
        terms = [],
        canFollowUp,
        canViewReports,
        programReports = [],
        flash,
    } = usePage<Props>().props;
=======
    const { canManageDocuments, role, canFollowUp, canViewReports, items = [], rates = [], programReports = [], flash } = usePage<Props>().props;
>>>>>>> Stashed changes
    const [tab, setTab] = useState<'workflow' | 'schedule' | 'rates' | 'records' | 'reports'>('workflow');
    const [archived, setArchived] = useState(false);
    const active = items.filter((item) => !item.archived);
    const categoryItems = items.filter((item) => item.archived === archived);
    const [search, setSearch] = useState('');
<<<<<<< Updated upstream
    const [statusFilter, setStatusFilter] = useState('all');
    const [facultyFilter, setFacultyFilter] = useState('all');
    const [termFilter, setTermFilter] = useState('all');
    useEffect(() => {
        const interval = window.setInterval(() => {
            router.reload({ only: ['items', 'facultyOptions', 'terms', 'programReports'] });
        }, 30_000);

        return () => window.clearInterval(interval);
    }, []);
=======
    const [faculty, setFaculty] = useState('');
    const [semester, setSemester] = useState('');
    const [year, setYear] = useState('');
    const [status, setStatus] = useState('');
    const [program, setProgram] = useState('');
    const [followUpErrors, setFollowUpErrors] = useState<Record<number, string>>({});
    const facultyOptions = [...new Set(categoryItems.flatMap((item) => item.faculty))].sort();
    const yearOptions = [...new Set(categoryItems.flatMap((item) => item.periods.map((period) => period.school_year)))].sort().reverse();
    const programOptions = [...new Set(categoryItems.map((item) => item.program).filter(Boolean))] as string[];
>>>>>>> Stashed changes
    const filtered = categoryItems.filter(
        (item) =>
            `${item.student_name} ${item.student_number ?? ''} ${item.program ?? ''} ${item.defense_type}`
                .toLowerCase()
                .includes(search.toLowerCase()) &&
            (facultyFilter === 'all' || item.faculty_names.includes(facultyFilter)) &&
            (termFilter === 'all' || `${item.school_year ?? ''}|${item.semester ?? ''}` === termFilter) &&
            (statusFilter === 'all' ||
                (statusFilter === 'awaiting' && item.finance_status === 'submitted') ||
                (statusFilter === 'verified' && item.finance_status === 'verified') ||
                (statusFilter === 'receipts' && Boolean(item.receipt_url))),
    );
<<<<<<< Updated upstream
    const submit = (item: Item) => router.post(`/honorarium-workspace/${item.id}/submit-to-finance`, {}, { preserveScroll: true });
=======
    useEffect(() => {
        const timer = window.setInterval(() => {
            if (document.visibilityState === 'visible') router.reload({ only: ['items', 'programReports'] });
        }, 60000);
        return () => window.clearInterval(timer);
    }, []);
    const displayDate = (value?: string | null) =>
        value ? new Date(value).toLocaleString('en-PH', { timeZone: 'Asia/Manila', dateStyle: 'medium', timeStyle: 'short' }) : 'Not sent';
    const formatAmount = (value: number) => Number(value).toLocaleString('en-PH', { style: 'currency', currency: 'PHP', minimumFractionDigits: 2 });
>>>>>>> Stashed changes
    const followUp = (item: Item, form: HTMLFormElement) => {
        const values = new FormData(form);
        router.post(
            `/honorarium-workspace/${item.id}/follow-ups`,
            { recipient_role: String(values.get('recipient_role') ?? ''), notes: String(values.get('notes') ?? '') },
<<<<<<< Updated upstream
            { preserveScroll: true, onSuccess: () => form.reset() },
=======
            {
                preserveScroll: true,
                onSuccess: () => {
                    form.reset();
                    setFollowUpErrors((current) => {
                        const next = { ...current };
                        delete next[item.id];

                        return next;
                    });
                },
                onError: (errors) => {
                    const message = errors.recipient_role ?? errors.notes;
                    if (message) setFollowUpErrors((current) => ({ ...current, [item.id]: message }));
                },
            },
        );
    };
    function exportTracking() {
        const cell = (value: string) => '"' + (/^[=+\-@]/.test(value) ? "'" + value : value).replaceAll('"', '""') + '"';
        const rows = [
            [
                'Request',
                'Student',
                'Program',
                'Defense',
                'Faculty',
                'School year / semester',
                'Defense submitted (PH)',
                'Latest Finance handoff (PH)',
                'Finance status',
                'Progress',
                'Next action',
            ],
            ...filtered.map((item) => [
                String(item.id),
                item.student_name,
                item.program ?? '',
                item.defense_type,
                item.faculty.join('; '),
                item.periods.map((period) => `${period.school_year} / ${period.semester}`).join('; ') || 'Not recorded',
                displayDate(item.requested_at),
                displayDate(item.submitted_at),
                statusLabel[item.finance_status],
                `${item.progress.percent}%`,
                item.progress.label,
            ]),
        ];
        const url = URL.createObjectURL(
            new Blob(['\uFEFF' + rows.map((row) => row.map(cell).join(',')).join('\r\n')], { type: 'text/csv;charset=utf-8;' }),
>>>>>>> Stashed changes
        );
    };
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Honorarium Tracking" />
            <main className="mx-auto flex w-full max-w-[1600px] flex-col gap-4 p-4 md:p-6">
                <div className="flex flex-wrap items-center justify-between gap-3">
                    <div className="flex items-center gap-3">
                        <div>
                            <h1 className="text-2xl font-semibold tracking-tight">Honorarium</h1>
                            <p className="text-muted-foreground text-sm">Defense request tracking</p>
                        </div>
                        <Badge variant="outline" className="capitalize">
                            {role}
                        </Badge>
                    </div>
                    <div className="flex flex-wrap gap-2">
                        {canManageDocuments && (
                            <Button asChild variant="outline" size="sm">
                                <Link href="/honorarium-documents">Finance documents</Link>
                            </Button>
                        )}
                        <Button asChild variant="outline" size="sm">
                            <Link href="/honorarium-ledger">Payment ledger</Link>
                        </Button>
                    </div>
                </div>
                {flash?.success && (
                    <div role="status" className="rounded-lg border border-emerald-200 bg-emerald-50 p-3 text-sm text-emerald-800">
                        {flash.success}
                    </div>
                )}
                <div className="flex flex-wrap gap-2 border-b pb-3">
                    {(
                        [
                            { id: 'workflow', label: 'Workflow', icon: FileCheck2 },
                            { id: 'schedule', label: 'Schedule board', icon: CalendarDays },
                            { id: 'rates', label: 'Payment rates', icon: CircleDollarSign },
                            { id: 'records', label: 'Records', icon: FileText },
                            ...(canViewReports ? [{ id: 'reports' as const, label: 'Program reports', icon: FileCheck2 }] : []),
                        ] as const
                    ).map((t) => (
                        <Button key={t.id} variant={tab === t.id ? 'default' : 'outline'} onClick={() => setTab(t.id)} className="gap-2">
                            <t.icon className="size-4" />
                            {t.label}
                        </Button>
                    ))}
                </div>
                <HonorariumRecordFilter
                    archived={archived}
                    onChange={setArchived}
                    activeCount={active.length}
                    archivedCount={items.length - active.length}
                />
<<<<<<< Updated upstream
=======
                {tab !== 'rates' && tab !== 'reports' && (
                    <section aria-label="Tracking filters" className="bg-card space-y-3 rounded-lg border p-3">
                        <div className="grid gap-2 sm:grid-cols-2 lg:grid-cols-4">
                            <Input
                                aria-label="Search tracking records"
                                className="h-9"
                                placeholder="Search student, program, or faculty"
                                value={search}
                                onChange={(e) => setSearch(e.target.value)}
                            />
                            <select
                                aria-label="Filter by faculty"
                                className="bg-background h-9 rounded-md border px-3 text-sm"
                                value={faculty}
                                onChange={(e) => setFaculty(e.target.value)}
                            >
                                <option value="">All faculty</option>
                                {facultyOptions.map((name) => (
                                    <option key={name}>{name}</option>
                                ))}
                            </select>
                            <select
                                aria-label="Filter by program"
                                className="bg-background h-9 rounded-md border px-3 text-sm"
                                value={program}
                                onChange={(e) => setProgram(e.target.value)}
                            >
                                <option value="">All programs</option>
                                {programOptions.sort().map((name) => (
                                    <option key={name}>{name}</option>
                                ))}
                            </select>
                            <select
                                aria-label="Filter by school year"
                                className="bg-background h-9 rounded-md border px-3 text-sm"
                                value={year}
                                onChange={(e) => setYear(e.target.value)}
                            >
                                <option value="">All school years</option>
                                {yearOptions.map((name) => (
                                    <option key={name}>{name}</option>
                                ))}
                            </select>
                            <select
                                aria-label="Filter by semester"
                                className="bg-background h-9 rounded-md border px-3 text-sm"
                                value={semester}
                                onChange={(e) => setSemester(e.target.value)}
                            >
                                <option value="">All semesters</option>
                                {['1st', '2nd', 'Summer'].map((name) => (
                                    <option key={name}>{name}</option>
                                ))}
                            </select>
                            <select
                                aria-label="Filter by Finance status"
                                className="bg-background h-9 rounded-md border px-3 text-sm"
                                value={status}
                                onChange={(e) => setStatus(e.target.value)}
                            >
                                <option value="">All Finance statuses</option>
                                {Object.entries(statusLabel).map(([value, label]) => (
                                    <option key={value} value={value}>
                                        {label}
                                    </option>
                                ))}
                                <option value="receipts">Receipts available</option>
                            </select>
                            <Button
                                variant="outline"
                                size="sm"
                                onClick={() => {
                                    setSearch('');
                                    setFaculty('');
                                    setProgram('');
                                    setSemester('');
                                    setYear('');
                                    setStatus('');
                                }}
                            >
                                Reset filters
                            </Button>
                            <Button variant="outline" size="sm" onClick={exportTracking}>
                                Export filtered CSV
                            </Button>
                        </div>
                        <p className="text-muted-foreground text-xs">{filtered.length} matching records · Updated every minute</p>
                    </section>
                )}
>>>>>>> Stashed changes
                {tab === 'workflow' && (
                    <Card className="overflow-hidden">
                        <CardHeader className="pb-4">
                            <div className="flex flex-wrap items-center justify-between gap-3">
                                <CardTitle className="text-base">Defense requests</CardTitle>
                                <span className="text-muted-foreground text-sm">{filtered.length} requests</span>
                            </div>
<<<<<<< Updated upstream
                            <div className="grid gap-2 pt-2 md:grid-cols-2 xl:grid-cols-4">
                                <div className="relative">
                                    <Search className="text-muted-foreground absolute top-2.5 left-3 size-4" />
                                    <Input
                                        className="h-9 pl-9"
                                        placeholder="Search student or program"
                                        value={search}
                                        onChange={(e) => setSearch(e.target.value)}
                                    />
                                </div>
                                <select
                                    className="bg-background h-9 rounded-md border px-3 text-sm"
                                    aria-label="Filter by faculty"
                                    value={facultyFilter}
                                    onChange={(e) => setFacultyFilter(e.target.value)}
                                >
                                    <option value="all">All faculty</option>
                                    {facultyOptions.map((faculty) => (
                                        <option key={faculty} value={faculty}>
                                            {faculty}
                                        </option>
                                    ))}
                                </select>
                                <select
                                    className="bg-background h-9 rounded-md border px-3 text-sm"
                                    aria-label="Filter by semester"
                                    value={termFilter}
                                    onChange={(e) => setTermFilter(e.target.value)}
                                >
                                    <option value="all">All semesters</option>
                                    {terms.map((term) => (
                                        <option key={term.value} value={term.value}>
                                            {term.school_year} · {term.semester}
                                        </option>
                                    ))}
                                </select>
                                <select
                                    className="bg-background h-9 rounded-md border px-3 text-sm"
                                    aria-label="Filter by honorarium status"
                                    value={statusFilter}
                                    onChange={(e) => setStatusFilter(e.target.value)}
                                >
                                    <option value="all">All statuses</option>
                                    <option value="awaiting">Awaiting Finance</option>
                                    <option value="verified">Verified</option>
                                    <option value="receipts">Receipts Available</option>
                                </select>
                            </div>
                        </CardHeader>
                        <CardContent className="p-0">
                            <div className="overflow-x-auto">
                                <table className="w-full min-w-[980px] table-fixed text-left text-sm">
                                    <thead>
                                        <tr className="text-muted-foreground bg-muted/30 border-y text-xs tracking-wide uppercase">
                                            <th className="w-[19%] px-4 py-3 font-medium">Request</th>
                                            <th className="w-[16%] px-3 py-3 font-medium">Faculty & panel</th>
                                            <th className="w-[11%] px-3 py-3 font-medium">Semester</th>
                                            <th className="w-[20%] px-3 py-3 font-medium">Progress</th>
                                            <th className="w-[18%] px-3 py-3 font-medium">Finance & payments</th>
                                            <th className="w-[16%] px-3 py-3 font-medium">Follow-up</th>
=======
                        </CardHeader>
                        <CardContent className="p-0">
                            <div className="overflow-x-auto">
                                <table className="w-full min-w-[760px] table-fixed text-left text-sm">
                                    <thead>
                                        <tr className="text-muted-foreground bg-muted/30 border-y text-xs tracking-wide uppercase">
                                            <th className="w-[21%] px-3 py-3 font-medium">Request</th>
                                            <th className="w-[15%] px-2 py-3 font-medium">Faculty & panel</th>
                                            <th className="w-[10%] px-2 py-3 font-medium">Semester</th>
                                            <th className="w-[28%] px-2 py-3 font-medium">Progress</th>
                                            <th className="w-[18%] px-2 py-3 font-medium">Finance</th>
                                            <th className="w-[16%] px-2 py-3 font-medium">Follow-up</th>
>>>>>>> Stashed changes
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {filtered.map((item) => {
<<<<<<< Updated upstream
                                            const latestFollowUp = item.follow_ups[0];
                                            const activeStep = stepIndex(item.progress_stage);
                                            const nextAction = currentAction(item);
                                            return (
                                                <tr className="hover:bg-muted/20 border-b align-top transition-colors last:border-0" key={item.id}>
                                                    <td className="px-4 py-4">
                                                        <div className="truncate font-medium" title={item.student_name}>
                                                            {item.student_name}
                                                        </div>
                                                        <div className="text-muted-foreground mt-1 truncate text-xs" title={item.program ?? ''}>
                                                            {item.program || 'Program not set'}
                                                        </div>
                                                        <div className="text-muted-foreground mt-1 flex flex-wrap items-center gap-x-2 text-xs">
                                                            <span>#{item.id}</span>
                                                            <span>{item.defense_type}</span>
                                                            {item.receipt_url && (
                                                                <a
                                                                    className="text-primary underline-offset-2 hover:underline"
                                                                    href={item.receipt_url}
                                                                    target="_blank"
                                                                    rel="noreferrer"
                                                                >
                                                                    Receipt
                                                                </a>
                                                            )}
                                                        </div>
                                                    </td>
                                                    <td className="px-3 py-4">
                                                        <div className="truncate text-sm" title={item.faculty_names[0] ?? ''}>
                                                            {item.faculty_names[0] || 'Not assigned'}
                                                        </div>
                                                        {item.panel_names.length > 0 ? (
                                                            <details className="text-muted-foreground mt-1 text-xs">
                                                                <summary className="cursor-pointer">Panel · {item.panel_names.length}</summary>
                                                                <ul className="mt-1 space-y-0.5">
                                                                    {item.panel_names.map((name) => (
=======
                                            const activeStep = item.progress.steps.findIndex((step) => !step.done);
                                            const nextAction = currentAction(item);

                                            return (
                                                <tr key={item.id} className="hover:bg-muted/20 border-b align-top transition-colors last:border-0">
                                                    <td className="px-3 py-4">
                                                        <p className="truncate font-medium" title={item.student_name}>
                                                            {item.student_name}
                                                        </p>
                                                        <p className="text-muted-foreground mt-1 truncate text-xs" title={item.program ?? ''}>
                                                            {item.program || 'Program not set'} · {item.defense_type}
                                                        </p>
                                                        <p className="text-muted-foreground mt-1 text-xs">Request #{item.id}</p>
                                                        <p className="text-muted-foreground text-[11px]">
                                                            Requested {displayDate(item.requested_at)}
                                                        </p>
                                                        {item.receipt_url && (
                                                            <a
                                                                className="text-primary mt-1 inline-block text-xs underline-offset-2 hover:underline"
                                                                href={item.receipt_url}
                                                                target="_blank"
                                                                rel="noreferrer"
                                                            >
                                                                View receipt
                                                            </a>
                                                        )}
                                                    </td>
                                                    <td className="px-2 py-4">
                                                        <p className="truncate text-sm" title={item.faculty[0] ?? ''}>
                                                            {item.faculty[0] || 'Not assigned'}
                                                        </p>
                                                        {item.faculty.length > 1 && (
                                                            <details className="text-muted-foreground mt-1 text-xs">
                                                                <summary className="cursor-pointer">View all · {item.faculty.length}</summary>
                                                                <ul className="mt-1 space-y-0.5">
                                                                    {item.faculty.map((name) => (
>>>>>>> Stashed changes
                                                                        <li className="truncate" key={name}>
                                                                            {name}
                                                                        </li>
                                                                    ))}
                                                                </ul>
                                                            </details>
<<<<<<< Updated upstream
                                                        ) : (
                                                            <div className="text-muted-foreground mt-1 text-xs">No panel assigned</div>
                                                        )}
                                                    </td>
                                                    <td className="text-muted-foreground px-3 py-4 text-xs">
                                                        {item.school_year && item.semester ? (
                                                            <>
                                                                <div className="text-foreground font-medium">{item.semester}</div>
                                                                <div>{item.school_year}</div>
                                                            </>
                                                        ) : (
                                                            '—'
                                                        )}
                                                    </td>
                                                    <td className="px-3 py-4">
                                                        <div className="mb-2 text-xs font-medium">{progressSteps[activeStep]}</div>
                                                        <ol className="flex items-center" aria-label="Honorarium progress">
                                                            {progressSteps.map((step, index) => {
                                                                const complete = index < activeStep;
=======
                                                        )}
                                                    </td>
                                                    <td className="text-muted-foreground px-2 py-4 text-xs">
                                                        {item.periods.length
                                                            ? item.periods.map((period) => (
                                                                  <div className="mb-1 last:mb-0" key={`${period.school_year}-${period.semester}`}>
                                                                      <span className="text-foreground font-medium">{period.semester}</span>
                                                                      <div>{period.school_year}</div>
                                                                  </div>
                                                              ))
                                                            : '—'}
                                                    </td>
                                                    <td className="px-2 py-4">
                                                        <div className="mb-2 flex items-center justify-between gap-2 text-xs">
                                                            <span className="truncate font-medium" title={item.progress.label}>
                                                                {item.progress.label}
                                                            </span>
                                                            <span className="text-muted-foreground shrink-0">{item.progress.percent}%</span>
                                                        </div>
                                                        <ol className="flex items-center" aria-label="Honorarium progress">
                                                            {item.progress.steps.map((step, index) => {
>>>>>>> Stashed changes
                                                                const current = index === activeStep;

                                                                return (
                                                                    <li
<<<<<<< Updated upstream
                                                                        className={`flex min-w-0 items-center ${index < progressSteps.length - 1 ? 'flex-1' : 'shrink-0'}`}
                                                                        key={step}
                                                                        aria-current={current ? 'step' : undefined}
                                                                        aria-label={`${step}${complete ? ', complete' : current ? ', current step' : ', upcoming'}`}
                                                                    >
                                                                        <span
                                                                            title={step}
                                                                            className={`flex size-5 shrink-0 items-center justify-center rounded-full border text-[10px] font-semibold ${
                                                                                complete
=======
                                                                        className={`flex min-w-0 items-center ${index < item.progress.steps.length - 1 ? 'flex-1' : 'shrink-0'}`}
                                                                        key={step.label}
                                                                        aria-current={current ? 'step' : undefined}
                                                                        aria-label={`${step.label}${step.done ? ', complete' : current ? ', current step' : ', upcoming'}`}
                                                                    >
                                                                        <span
                                                                            className={`flex size-4 shrink-0 items-center justify-center rounded-full border text-[9px] font-semibold ${
                                                                                step.done
>>>>>>> Stashed changes
                                                                                    ? 'border-emerald-600 bg-emerald-600 text-white'
                                                                                    : current
                                                                                      ? 'border-primary bg-primary text-primary-foreground'
                                                                                      : 'border-muted-foreground/40 text-muted-foreground'
                                                                            }`}
<<<<<<< Updated upstream
                                                                        >
                                                                            {complete ? '✓' : index + 1}
                                                                        </span>
                                                                        {index < progressSteps.length - 1 && (
                                                                            <span className="bg-border mx-1 h-px flex-1" aria-hidden="true" />
=======
                                                                            title={step.label}
                                                                        >
                                                                            {step.done ? '✓' : index + 1}
                                                                        </span>
                                                                        {index < item.progress.steps.length - 1 && (
                                                                            <span className="bg-border mx-0.5 h-px flex-1" aria-hidden="true" />
>>>>>>> Stashed changes
                                                                        )}
                                                                    </li>
                                                                );
                                                            })}
                                                        </ol>
<<<<<<< Updated upstream
                                                        <div
                                                            className="text-muted-foreground mt-2 truncate text-xs"
                                                            role="status"
=======
                                                        <p
                                                            className="text-muted-foreground mt-2 truncate text-xs"
>>>>>>> Stashed changes
                                                            title={`${nextAction.owner}: ${nextAction.action}`}
                                                        >
                                                            Next: <span className="text-foreground font-medium">{nextAction.owner}</span> ·{' '}
                                                            {nextAction.action}
<<<<<<< Updated upstream
                                                        </div>
                                                    </td>
                                                    <td className="px-3 py-4">
                                                        <div className="space-y-1.5">
                                                            {(['payment_receipt', 'panel_breakdown'] as const).map((type) => {
                                                                const doc = item.finance_documents.find((document) => document.type === type);
                                                                const label = type === 'payment_receipt' ? 'Receipt' : 'Breakdown';

                                                                return (
                                                                    <div className="flex items-center justify-between gap-2 text-xs" key={type}>
                                                                        <span className="text-muted-foreground">{label}</span>
                                                                        <Badge
                                                                            variant={doc?.status === 'verified' ? 'default' : 'secondary'}
                                                                            className="h-5 px-1.5 text-[10px]"
                                                                        >
                                                                            {doc?.status ?? 'Not sent'}
                                                                        </Badge>
                                                                    </div>
                                                                );
                                                            })}
                                                            <details className="text-muted-foreground border-t pt-1.5 text-[11px]">
                                                                <summary className="cursor-pointer">Activity & panel payments</summary>
                                                                <div className="mt-1 space-y-2">
                                                                    {item.finance_documents.map((doc) => (
                                                                        <div key={doc.type}>
                                                                            <div className="text-foreground font-medium">
                                                                                {doc.type === 'payment_receipt' ? 'Receipt' : 'Panel breakdown'}
                                                                            </div>
                                                                            <div>Sent {formatDate(doc.submitted_at)}</div>
                                                                            <div>
                                                                                Reviewed {formatDate(doc.reviewed_at)}
                                                                                {doc.reviewer_role ? ` · ${doc.reviewer_role}` : ''}
                                                                            </div>
                                                                            {doc.finance_notes && <div>Note: {doc.finance_notes}</div>}
                                                                        </div>
                                                                    ))}
                                                                    <div className="text-foreground font-medium">Panel honoraria</div>
                                                                    {item.honorarium_recipients.length ? (
                                                                        item.honorarium_recipients.map((recipient) => (
                                                                            <div
                                                                                className="flex justify-between gap-2"
                                                                                key={`${recipient.role}-${recipient.name}`}
                                                                            >
                                                                                <span className="truncate">{recipient.name}</span>
                                                                                <span>{recipient.status}</span>
                                                                            </div>
                                                                        ))
                                                                    ) : (
                                                                        <div>No payment records</div>
                                                                    )}
                                                                </div>
                                                            </details>
                                                        </div>
                                                    </td>
                                                    <td className="px-3 py-4">
                                                        {latestFollowUp && (
                                                            <details className="text-muted-foreground mb-2 text-[11px]">
                                                                <summary className="cursor-pointer">
                                                                    Follow-up · {formatDate(latestFollowUp.occurred_at)}
                                                                </summary>
                                                                <p className="mt-1">
                                                                    {latestFollowUp.actor} → {latestFollowUp.recipient_role}
                                                                </p>
                                                                <p>{latestFollowUp.notes}</p>
                                                            </details>
=======
                                                        </p>
                                                    </td>
                                                    <td className="px-2 py-4">
                                                        <Badge
                                                            variant={
                                                                item.finance_status === 'verified'
                                                                    ? 'default'
                                                                    : item.finance_status === 'rejected'
                                                                      ? 'destructive'
                                                                      : 'secondary'
                                                            }
                                                        >
                                                            {statusLabel[item.finance_status] ?? item.finance_status}
                                                        </Badge>
                                                        <p className="text-muted-foreground mt-1 text-[11px]">
                                                            Handoff {displayDate(item.submitted_at)}
                                                        </p>
                                                        {item.finance_notes && (
                                                            <details className="text-muted-foreground mt-1 text-xs">
                                                                <summary className="cursor-pointer">Finance note</summary>
                                                                <p className="mt-1 break-words">{item.finance_notes}</p>
                                                            </details>
                                                        )}
                                                    </td>
                                                    <td className="px-2 py-4">
                                                        {item.follow_ups.length > 0 && (
                                                            <details className="text-muted-foreground mb-2 text-[11px]">
                                                                <summary className="cursor-pointer">History · {item.follow_ups.length}</summary>
                                                                <div className="mt-1 space-y-2">
                                                                    {item.follow_ups.map((entry) => (
                                                                        <div key={entry.id}>
                                                                            <div className="text-foreground font-medium">
                                                                                {entry.recipient_role} · {displayDate(entry.occurred_at)}
                                                                            </div>
                                                                            <div>
                                                                                {entry.actor}: {entry.notes}
                                                                            </div>
                                                                        </div>
                                                                    ))}
                                                                </div>
                                                            </details>
>>>>>>> Stashed changes
                                                        )}
                                                        {canFollowUp && !item.archived && (
                                                            <details>
                                                                <summary className="text-primary cursor-pointer text-xs font-medium">
                                                                    + Follow up
                                                                </summary>
                                                                <form
<<<<<<< Updated upstream
                                                                    className="bg-background mt-2 w-full space-y-2 rounded-lg border p-3"
=======
                                                                    className="bg-background mt-2 w-full min-w-36 space-y-2 rounded-md border p-2"
>>>>>>> Stashed changes
                                                                    onSubmit={(event) => {
                                                                        event.preventDefault();
                                                                        followUp(item, event.currentTarget);
                                                                    }}
                                                                >
                                                                    <select
                                                                        className="bg-background h-8 w-full rounded-md border px-2 text-xs"
                                                                        name="recipient_role"
                                                                        defaultValue="Finance"
                                                                        aria-label={`Follow-up recipient for request ${item.id}`}
                                                                    >
                                                                        <option>Finance</option>
                                                                        <option>Administrative Assistant</option>
                                                                        <option>Dean</option>
                                                                    </select>
                                                                    <Textarea
                                                                        className="min-h-16 text-xs"
                                                                        name="notes"
                                                                        required
                                                                        minLength={3}
                                                                        maxLength={2000}
                                                                        placeholder="Add a short note"
                                                                    />
<<<<<<< Updated upstream
=======
                                                                    {followUpErrors[item.id] && (
                                                                        <p className="text-destructive text-xs" role="alert">
                                                                            {followUpErrors[item.id]}
                                                                        </p>
                                                                    )}
>>>>>>> Stashed changes
                                                                    <Button type="submit" size="sm" className="w-full">
                                                                        Send follow-up
                                                                    </Button>
                                                                </form>
                                                            </details>
                                                        )}
<<<<<<< Updated upstream
                                                        {!item.archived && canSubmitToFinance && item.receipt_url && (
                                                            <Button className="mt-2 h-7 px-2 text-xs" size="sm" onClick={() => submit(item)}>
                                                                <Send className="mr-1 size-3" />
                                                                Send receipt
                                                            </Button>
                                                        )}
=======
>>>>>>> Stashed changes
                                                    </td>
                                                </tr>
                                            );
                                        })}
                                        {filtered.length === 0 && (
                                            <tr>
<<<<<<< Updated upstream
                                                <td className="text-muted-foreground p-10 text-center" colSpan={6}>
                                                    No matching defense requests.
=======
                                                <td colSpan={6} className="text-muted-foreground p-10 text-center">
                                                    No matching honorarium records.
                                                </td>
                                            </tr>
                                        )}
                                    </tbody>
                                </table>
                            </div>
                        </CardContent>
                    </Card>
                )}
                {tab === 'reports' && canViewReports && (
                    <Card className="overflow-hidden">
                        <CardHeader>
                            <div className="flex flex-wrap items-center justify-between gap-3">
                                <div>
                                    <CardTitle className="text-base">Program honorarium report</CardTitle>
                                    <p className="text-muted-foreground mt-1 text-sm">Amounts grouped by program, school year, and semester.</p>
                                </div>
                                <Button asChild size="sm" variant="outline">
                                    <a href="/honorarium-workspace/reports.csv">Export CSV</a>
                                </Button>
                            </div>
                        </CardHeader>
                        <CardContent className="p-0">
                            <div className="overflow-x-auto">
                                <table className="w-full min-w-[900px] text-left text-sm">
                                    <thead>
                                        <tr className="text-muted-foreground bg-muted/30 border-y text-xs tracking-wide uppercase">
                                            <th className="px-4 py-3 font-medium">Program</th>
                                            <th className="px-3 py-3 font-medium">School year</th>
                                            <th className="px-3 py-3 font-medium">Semester</th>
                                            <th className="px-3 py-3 text-right font-medium">Recipients</th>
                                            <th className="px-3 py-3 text-right font-medium">Total</th>
                                            <th className="px-3 py-3 text-right font-medium">Prepared</th>
                                            <th className="px-3 py-3 text-right font-medium">Approved</th>
                                            <th className="px-3 py-3 text-right font-medium">Disbursed</th>
                                            <th className="px-3 py-3 text-right font-medium">Claimed</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {programReports.map((report) => (
                                            <tr className="border-b last:border-0" key={`${report.program}-${report.school_year}-${report.semester}`}>
                                                <td className="px-4 py-3 font-medium">{report.program}</td>
                                                <td className="text-muted-foreground px-3 py-3">{report.school_year}</td>
                                                <td className="text-muted-foreground px-3 py-3">{report.semester}</td>
                                                <td className="px-3 py-3 text-right">{report.recipients_count}</td>
                                                <td className="px-3 py-3 text-right font-medium">{formatAmount(report.total_amount)}</td>
                                                <td className="px-3 py-3 text-right">{formatAmount(report.prepared_amount)}</td>
                                                <td className="px-3 py-3 text-right">{formatAmount(report.approved_amount)}</td>
                                                <td className="px-3 py-3 text-right">{formatAmount(report.disbursed_amount)}</td>
                                                <td className="px-3 py-3 text-right">{formatAmount(report.claimed_amount)}</td>
                                            </tr>
                                        ))}
                                        {programReports.length === 0 && (
                                            <tr>
                                                <td className="text-muted-foreground p-10 text-center" colSpan={9}>
                                                    No honorarium payments have been prepared.
>>>>>>> Stashed changes
                                                </td>
                                            </tr>
                                        )}
                                    </tbody>
                                </table>
                            </div>
                        </CardContent>
                    </Card>
                )}
                {tab === 'schedule' && (
                    <Card>
                        <CardHeader>
                            <CardTitle>Schedule board</CardTitle>
                            <p className="text-muted-foreground text-sm">
                                Existing defense schedules are shown here. Scheduling conflict prevention remains managed by the current defense
                                scheduling module.
                            </p>
                        </CardHeader>
                        <CardContent>
                            <div className="overflow-x-auto">
                                <table className="w-full text-left text-sm">
                                    <thead>
                                        <tr className="text-muted-foreground border-b">
                                            <th className="p-3">Date</th>
                                            <th className="p-3">Time</th>
                                            <th className="p-3">Student</th>
                                            <th className="p-3">Panel</th>
                                            <th className="p-3">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {categoryItems
                                            .filter((i) => i.scheduled_date)
                                            .sort((a, b) => (a.scheduled_date ?? '').localeCompare(b.scheduled_date ?? ''))
                                            .map((i) => (
                                                <tr className="border-b last:border-0" key={i.id}>
                                                    <td className="p-3">{i.scheduled_date}</td>
                                                    <td className="p-3">{i.scheduled_time ?? '—'}</td>
                                                    <td className="p-3">{i.student_name}</td>
                                                    <td className="p-3">{i.panel_names.join(', ') || '—'}</td>
                                                    <td className="p-3">{i.workflow_state}</td>
                                                </tr>
                                            ))}
                                    </tbody>
                                </table>
                                {!categoryItems.some((i) => i.scheduled_date) && (
                                    <p className="text-muted-foreground p-6 text-center text-sm">No scheduled defenses with receipt records yet.</p>
                                )}
                            </div>
                        </CardContent>
                    </Card>
                )}
                {tab === 'rates' && (
                    <Card>
                        <CardHeader>
                            <CardTitle>Payment rates</CardTitle>
                            <p className="text-muted-foreground text-sm">
                                Read-only rate reference. Rate editing remains in the existing Dean payment-rates module.
                            </p>
                        </CardHeader>
                        <CardContent>
                            <div className="overflow-x-auto">
                                <table className="w-full text-left text-sm">
                                    <thead>
                                        <tr className="text-muted-foreground border-b">
                                            <th className="p-3">Program level</th>
                                            <th className="p-3">Type</th>
                                            <th className="p-3">Defense</th>
                                            <th className="p-3 text-right">Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {rates.map((rate) => (
                                            <tr className="border-b last:border-0" key={rate.id}>
                                                <td className="p-3">{rate.program_level}</td>
                                                <td className="p-3">{rate.type}</td>
                                                <td className="p-3">{rate.defense_type ?? '—'}</td>
                                                <td className="p-3 text-right">
                                                    ₱
                                                    {Number(rate.amount).toLocaleString(undefined, {
                                                        minimumFractionDigits: 2,
                                                        maximumFractionDigits: 2,
                                                    })}
                                                </td>
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                                {rates.length === 0 && <p className="text-muted-foreground p-6 text-center text-sm">No payment rates configured.</p>}
                            </div>
                        </CardContent>
                    </Card>
                )}
                {tab === 'records' && (
                    <Card>
                        <CardHeader>
                            <CardTitle>Honorarium records</CardTitle>
                            <p className="text-muted-foreground text-sm">
                                Current status history snapshot for the receipt-backed records available to your role.
                            </p>
                        </CardHeader>
                        <CardContent>
                            <div className="overflow-x-auto">
                                <table className="w-full text-left text-sm">
                                    <thead>
                                        <tr className="text-muted-foreground border-b">
                                            <th className="p-3">Request</th>
                                            <th className="p-3">Student</th>
                                            <th className="p-3">Workflow</th>
                                            <th className="p-3">Finance</th>
                                            <th className="p-3">Submitted</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {categoryItems.map((i) => (
                                            <tr className="border-b last:border-0" key={i.id}>
                                                <td className="p-3">#{i.id}</td>
                                                <td className="p-3">{i.student_name}</td>
                                                <td className="p-3">{i.workflow_state}</td>
                                                <td className="p-3">{statusLabel[i.finance_status] ?? i.finance_status}</td>
                                                <td className="p-3">{i.submitted_at ?? '—'}</td>
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                            </div>
                        </CardContent>
                    </Card>
                )}
                {tab === 'reports' && canViewReports && (
                    <Card>
                        <CardHeader>
                            <CardTitle>Program honorarium by semester</CardTitle>
                            <p className="text-muted-foreground text-sm">
                                Live totals from prepared honorarium recipient records. Voided records are excluded.
                            </p>
                            <Button asChild variant="outline" className="w-fit">
                                <a href="/honorarium-workspace/reports.csv">Download CSV report</a>
                            </Button>
                        </CardHeader>
                        <CardContent>
                            <div className="overflow-x-auto">
                                <table className="w-full min-w-[900px] text-left text-sm">
                                    <thead>
                                        <tr className="text-muted-foreground border-b">
                                            <th className="p-3">School year / semester</th>
                                            <th className="p-3">Program</th>
                                            <th className="p-3">Recipients</th>
                                            <th className="p-3 text-right">Prepared</th>
                                            <th className="p-3 text-right">Approved</th>
                                            <th className="p-3 text-right">Disbursed</th>
                                            <th className="p-3 text-right">Claimed</th>
                                            <th className="p-3 text-right">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {programReports.map((report) => (
                                            <tr className="border-b last:border-0" key={`${report.school_year}-${report.semester}-${report.program}`}>
                                                <td className="p-3">
                                                    {report.school_year} · {report.semester}
                                                </td>
                                                <td className="p-3">{report.program || 'Unspecified program'}</td>
                                                <td className="p-3">{report.recipients_count}</td>
                                                {[
                                                    report.prepared_amount,
                                                    report.approved_amount,
                                                    report.disbursed_amount,
                                                    report.claimed_amount,
                                                    report.total_amount,
                                                ].map((amount, index) => (
                                                    <td className="p-3 text-right" key={index}>
                                                        ₱
                                                        {Number(amount).toLocaleString('en-PH', {
                                                            minimumFractionDigits: 2,
                                                            maximumFractionDigits: 2,
                                                        })}
                                                    </td>
                                                ))}
                                            </tr>
                                        ))}
                                        {programReports.length === 0 && (
                                            <tr>
                                                <td className="text-muted-foreground p-8 text-center" colSpan={8}>
                                                    No honorarium records are available to report.
                                                </td>
                                            </tr>
                                        )}
                                    </tbody>
                                </table>
                            </div>
                        </CardContent>
                    </Card>
                )}
            </main>
        </AppLayout>
    );
}
