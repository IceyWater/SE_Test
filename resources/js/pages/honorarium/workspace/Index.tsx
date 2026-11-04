import HonorariumRecordFilter from '@/components/honorarium-record-filter';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router, usePage } from '@inertiajs/react';
import { CalendarDays, CircleDollarSign, FileCheck2, FileText } from 'lucide-react';
import { useEffect, useState } from 'react';

type Item = {
    archived: boolean;
    faculty: string[];
    periods: { school_year: string; semester: string }[];
    requested_at?: string | null;
    progress: { percent: number; label: string; steps: { label: string; done: boolean }[] };
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
    finance_status: string;
    finance_notes?: string | null;
    submitted_at?: string | null;
    finance_submission_id?: number | null;
};
type Rate = { id: number; program_level: string; type: string; defense_type?: string | null; amount: number };
type Props = {
    canManageDocuments: boolean;
    role: string;
    canSubmitToFinance: boolean;
    canVerifyFinance: boolean;
    items: Item[];
    rates: Rate[];
    flash?: { success?: string };
};

const statusLabel: Record<string, string> = {
    not_submitted: 'Not sent to Finance',
    submitted: 'Awaiting Finance',
    verified: 'Finance verified',
    rejected: 'Finance rejected',
};

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Honorarium Tracking', href: '/honorarium-workspace' }];

export default function HonorariumWorkspace() {
    const { canManageDocuments, role, items = [], rates = [], flash } = usePage<Props>().props;
    const [tab, setTab] = useState<'workflow' | 'schedule' | 'rates' | 'records'>('workflow');
    const [archived, setArchived] = useState(false);
    const active = items.filter((item) => !item.archived);
    const categoryItems = items.filter((item) => item.archived === archived);
    const [search, setSearch] = useState('');
    const [faculty, setFaculty] = useState('');
    const [semester, setSemester] = useState('');
    const [year, setYear] = useState('');
    const [status, setStatus] = useState('');
    const [program, setProgram] = useState('');
    const facultyOptions = [...new Set(categoryItems.flatMap((item) => item.faculty))].sort();
    const yearOptions = [...new Set(categoryItems.flatMap((item) => item.periods.map((period) => period.school_year)))].sort().reverse();
    const programOptions = [...new Set(categoryItems.map((item) => item.program).filter(Boolean))] as string[];
    const filtered = categoryItems.filter(
        (item) =>
            `${item.student_name} ${item.student_number ?? ''} ${item.program ?? ''} ${item.defense_type} ${item.faculty.join(' ')}`
                .toLowerCase()
                .includes(search.toLowerCase()) &&
            (!faculty || item.faculty.includes(faculty)) &&
            (!program || item.program === program) &&
            ((!semester && !year) ||
                item.periods.some((period) => (!semester || period.semester === semester) && (!year || period.school_year === year))) &&
            (!status || (status === 'receipts' ? !!item.receipt_url : item.finance_status === status)),
    );
    useEffect(() => {
        const timer = window.setInterval(() => {
            if (document.visibilityState === 'visible') router.reload({ only: ['items'] });
        }, 60000);
        return () => window.clearInterval(timer);
    }, []);
    const displayDate = (value?: string | null) =>
        value ? new Date(value).toLocaleString('en-PH', { timeZone: 'Asia/Manila', dateStyle: 'medium', timeStyle: 'short' }) : 'Not sent';
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
        );
        const link = document.createElement('a');
        link.href = url;
        link.download = 'honorarium-tracking.csv';
        link.click();
        URL.revokeObjectURL(url);
    }
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Honorarium Tracking" />
            <main className="mx-auto flex w-full max-w-7xl flex-col gap-6 p-4 md:p-8">
                <div className="flex flex-col justify-between gap-3 sm:flex-row sm:items-end">
                    <div>
                        <p className="text-muted-foreground text-sm">Graduate School · Shared workspace</p>
                        <h1 className="text-3xl font-bold tracking-tight">Honorarium Tracking</h1>
                        <p className="text-muted-foreground mt-1 text-sm">
                            Receipts, panel assignments, scheduling, rates, and verification in one place.
                        </p>
                    </div>
                    <Badge variant="outline" className="w-fit capitalize">
                        {role}
                    </Badge>
                </div>
                {canManageDocuments && (
                    <Button asChild className="w-fit">
                        <Link href="/honorarium-documents">Manage Finance documents</Link>
                    </Button>
                )}
                <Button asChild variant="outline" className="w-fit">
                    <Link href="/honorarium-ledger">View honorarium payments</Link>
                </Button>
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
                {tab !== 'rates' && (
                    <section aria-label="Tracking filters" className="space-y-3 rounded-lg border p-4">
                        <div className="flex flex-wrap gap-3">
                            <Input
                                aria-label="Search tracking records"
                                className="max-w-sm"
                                placeholder="Search student, program, or faculty"
                                value={search}
                                onChange={(e) => setSearch(e.target.value)}
                            />
                            <select
                                aria-label="Filter by faculty"
                                className="bg-background rounded border p-2"
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
                                className="bg-background rounded border p-2"
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
                                className="bg-background rounded border p-2"
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
                                className="bg-background rounded border p-2"
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
                                className="bg-background rounded border p-2"
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
                            <Button variant="outline" onClick={exportTracking}>
                                Export filtered CSV
                            </Button>
                        </div>
                        <p className="text-muted-foreground text-sm">
                            {filtered.length} matching records from up to 500 recent requests. School year and semester come from prepared payment
                            records; unrecorded terms are not guessed. Updates refresh every minute while this page is visible.
                        </p>
                    </section>
                )}
                {tab === 'workflow' && (
                    <Card>
                        <CardHeader>
                            <CardTitle>Receipt and honorarium workflow</CardTitle>
                            <p className="text-muted-foreground text-sm">
                                Progress includes Finance verification and all current recipient payments. Open Finance Documents for review actions.
                            </p>
                        </CardHeader>
                        <CardContent className="overflow-x-auto">
                            <table className="w-full min-w-[1050px] text-left text-sm">
                                <thead>
                                    <tr className="bg-muted border-b">
                                        {[
                                            'Student / request',
                                            'Program / defense',
                                            'Faculty',
                                            'Defense submitted / Finance handoff (PH)',
                                            'Finance status',
                                            'Honorarium progress',
                                            'Receipt',
                                        ].map((label) => (
                                            <th key={label} className="p-3">
                                                {label}
                                            </th>
                                        ))}
                                    </tr>
                                </thead>
                                <tbody>
                                    {filtered.map((item) => (
                                        <tr key={item.id} className="border-b align-top last:border-0">
                                            <td className="p-3">
                                                <p className="font-medium">{item.student_name}</p>
                                                <p className="text-muted-foreground">
                                                    #{item.id} · {item.student_number}
                                                </p>
                                            </td>
                                            <td className="p-3">
                                                {item.program}
                                                <p className="text-muted-foreground">{item.defense_type}</p>
                                                <p className="text-muted-foreground">
                                                    {item.periods.map((period) => `${period.school_year} / ${period.semester}`).join(', ') ||
                                                        'Term not recorded'}
                                                </p>
                                            </td>
                                            <td className="max-w-56 p-3">{item.faculty.join(', ') || 'Not assigned'}</td>
                                            <td className="p-3">
                                                <p>Defense submitted: {displayDate(item.requested_at)}</p>
                                                <p className="text-muted-foreground">Latest handoff: {displayDate(item.submitted_at)}</p>
                                            </td>
                                            <td className="p-3">
                                                <Badge variant="secondary">{statusLabel[item.finance_status]}</Badge>
                                                {item.finance_notes && <p className="mt-2">{item.finance_notes}</p>}
                                            </td>
                                            <td className="min-w-60 p-3">
                                                <p>{item.progress.label}</p>
                                                <div
                                                    role="progressbar"
                                                    aria-label={`Honorarium progress for ${item.student_name}`}
                                                    aria-valuemin={0}
                                                    aria-valuemax={100}
                                                    aria-valuenow={item.progress.percent}
                                                    className="bg-muted my-2 h-2 overflow-hidden rounded"
                                                >
                                                    <div className="bg-primary h-full" style={{ width: `${item.progress.percent}%` }} />
                                                </div>
                                                <details>
                                                    <summary className="cursor-pointer">{item.progress.percent}% · View steps</summary>
                                                    <ul className="mt-2 space-y-1">
                                                        {item.progress.steps.map((step) => (
                                                            <li key={step.label}>
                                                                {step.done ? '✓' : '○'} {step.label}
                                                            </li>
                                                        ))}
                                                    </ul>
                                                </details>
                                            </td>
                                            <td className="p-3">
                                                {item.receipt_url ? (
                                                    <Button variant="outline" asChild>
                                                        <a href={item.receipt_url} target="_blank" rel="noreferrer">
                                                            View receipt
                                                        </a>
                                                    </Button>
                                                ) : (
                                                    'No receipt'
                                                )}
                                            </td>
                                        </tr>
                                    ))}
                                    {filtered.length === 0 && (
                                        <tr>
                                            <td colSpan={7} className="text-muted-foreground p-8 text-center">
                                                No matching honorarium records.
                                            </td>
                                        </tr>
                                    )}
                                </tbody>
                            </table>
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
                                        {filtered
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
                                {!filtered.some((i) => i.scheduled_date) && (
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
                                        {filtered.map((i) => (
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
            </main>
        </AppLayout>
    );
}
