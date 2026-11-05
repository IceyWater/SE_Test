import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, usePage } from '@inertiajs/react';
import { CalendarX, Check, GraduationCap, Info, Plus, RefreshCcw, ShieldAlert, ShieldCheck } from 'lucide-react';
import { useCallback, useEffect, useMemo, useState } from 'react';
import CompreExamForm from './compre-exam-form';
import DisplayApplication from './display-applications';

import { Badge } from '@/components/ui/badge';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Comprehensive Exam', href: '/comprehensive-exam' },
];
const workflowSteps = ['Submitted', 'AA Review', 'Registrar Verification', 'Payment', 'Scheduling'];

function workflowStepIndex(status: string): number {
    if (['submitted', 'aa_lacking'].includes(status)) return 1;
    if (status === 'aa_evaluated') return 2;
    if (['registrar_approved', 'registrar_rejected'].includes(status)) return 3;
    if (['payment_pending', 'payment_rejected'].includes(status)) return 3;
    if (status === 'payment_verified') return 4;
    if (['scheduled', 'completed'].includes(status)) return 5;

    return 0;
}

const formatDateTime = (value?: string | null) =>
    value ? new Intl.DateTimeFormat('en-PH', { dateStyle: 'medium', timeStyle: 'short', timeZone: 'Asia/Manila' }).format(new Date(value)) : '—';

type Subject = {
    subject: string;
    date?: string | null;
    startTime?: string | null;
    endTime?: string | null;
    room?: string | null;
    proctors?: { name: string; status: string }[];
};
type ExamEvent = { action: string; status: string | null; remarks?: string | null; actor?: string | null; created_at?: string | null };

type ApplicationVM = {
    application_id: number;
    application_no: string;
    school_year: string;
    workflow_status: string;
    permit_status: string;
    final_approval_status: string;
    payment_status: string;
    aa_remarks?: string | null;
    registrar_remarks?: string | null;
    submitted_at?: string | null;
    events: ExamEvent[];
    contact_number?: string | null;
    telephone_number?: string | null;
    office_address?: string | null;
    program: string;
    created_at?: string;
    subjects: Subject[];
    first_name: string;
    middle_name?: string | null;
    last_name: string;
    email: string;
    student_id: string | number;
};

type PageProps = {
    application?: ApplicationVM | null;
    eligibility?: Omit<Eligibility, 'loading' | 'error'>;
};

type Eligibility = {
    eligible: boolean;
    exam_open: boolean | null;
    stale: boolean;
    source?: string | null;
    as_of?: string | null;
    requirements: { key: string; name: string; completed: boolean | null }[];
    loading: boolean;
    error?: string | null;
};

export default function ComprehensiveExamIndex() {
    const { props } = usePage<PageProps>();
    const { application, eligibility: initialEligibility } = props;

    const [open, setOpen] = useState(false);

    const [elig, setElig] = useState<Eligibility>({
        eligible: initialEligibility?.eligible ?? false,
        exam_open: initialEligibility?.exam_open ?? null,
        stale: initialEligibility?.stale ?? false,
        source: initialEligibility?.source,
        as_of: initialEligibility?.as_of,
        requirements: initialEligibility?.requirements ?? [],
        loading: true,
        error: null,
    });

    const [showEligDialog, setShowEligDialog] = useState(false);

    const fetchEligibility = useCallback(async function checkEligibility() {
        setElig((e) => ({ ...e, loading: true, error: null }));

        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const response = await fetch('/api/comprehensive-exam/eligibility', {
                credentials: 'include',
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfToken || '',
                },
            });

            if (!response.ok) {
                throw new Error(`API error: ${response.status}`);
            }

            const data = await response.json();
            setElig({
                eligible: data.eligible === true,
                exam_open: data.exam_open ?? null,
                stale: data.stale === true,
                source: data.source ?? null,
                as_of: data.as_of ?? null,
                requirements: data.requirements ?? [],
                loading: false,
                error: null,
            });
        } catch (error) {
            setElig({
                eligible: false,
                exam_open: null,
                stale: false,
                requirements: [],
                loading: false,
                error: error instanceof Error ? error.message : 'API call failed',
            });
        }
    }, []);

    useEffect(() => {
        fetchEligibility();
    }, [fetchEligibility]);

    const canApply = useMemo(() => {
        return elig.eligible;
    }, [elig]);

    const examClosed = elig.exam_open === false;
    const needsResubmission = !!application && ['aa_lacking', 'registrar_rejected'].includes(application.workflow_status);
    const canStartApplication = needsResubmission || (!application && canApply && !examClosed);

    const blockers = useMemo(() => {
        return elig.requirements.map((requirement) => ({ label: requirement.name, ok: requirement.completed }));
    }, [elig.requirements]);

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Comprehensive Exam" />
            <div className="flex w-full flex-col px-7 pt-5 pb-5">
                <div className="bg-card text-card-foreground border-border w-full overflow-hidden rounded-lg border">
                    {/* --- Comprehensive Exam Content --- */}
                    <div className="border-border flex w-full flex-col items-start justify-between gap-4 border-b p-5 sm:flex-row sm:items-center">
                        <div className="flex items-center gap-2">
                            <div className="flex h-10 w-10 items-center justify-center rounded-full border border-rose-500 bg-rose-500/10">
                                <GraduationCap className="h-5 w-5 text-rose-400" />
                            </div>
                            <div>
                                <span className="text-base font-semibold">Comprehensive Exam</span>
                                <p className="text-muted-foreground block text-xs">Submit and track your comprehensive exam application.</p>
                                <div className="mt-1 flex flex-wrap items-center gap-2">
                                    <Badge
                                        variant={canApply ? 'secondary' : 'outline'}
                                        className={
                                            examClosed
                                                ? 'border-rose-300 bg-rose-50 text-rose-700 dark:border-rose-800 dark:bg-rose-950/40 dark:text-rose-300'
                                                : canApply
                                                  ? 'border-green-300 bg-green-50 text-green-700 dark:border-green-800 dark:bg-green-950/40 dark:text-green-300'
                                                  : ''
                                        }
                                    >
                                        {examClosed ? (
                                            <span className="flex items-center gap-1">
                                                <CalendarX className="h-3.5 w-3.5" />
                                                Closed
                                            </span>
                                        ) : canApply ? (
                                            <span className="flex items-center gap-1">
                                                <ShieldCheck className="h-3.5 w-3.5" />
                                                Eligible
                                            </span>
                                        ) : (
                                            <span className="flex items-center gap-1">
                                                <ShieldAlert className="h-3.5 w-3.5" />
                                                Not eligible
                                            </span>
                                        )}
                                    </Badge>
                                    <Button
                                        size="sm"
                                        variant="outline"
                                        className="h-7 px-2 text-xs"
                                        onClick={() => fetchEligibility()}
                                        disabled={elig.loading}
                                    >
                                        <RefreshCcw className={`mr-1 h-3.5 w-3.5 ${elig.loading ? 'animate-spin' : ''}`} />
                                        Refresh
                                    </Button>
                                    <Button size="sm" variant="ghost" className="h-7 px-2 text-xs" onClick={() => setShowEligDialog(true)}>
                                        <Info className="mr-1 h-3.5 w-3.5" />
                                        Details
                                    </Button>
                                </div>
                            </div>
                        </div>
                        <Button
                            className="w-full shrink-0 rounded-md bg-rose-500 px-5 text-sm text-white hover:bg-rose-600 sm:w-auto"
                            onClick={() => setOpen(true)}
                            disabled={!canStartApplication}
                            title={
                                needsResubmission
                                    ? 'Update your application and resubmit it for review'
                                    : examClosed
                                      ? 'Exam is closed'
                                      : !canApply
                                        ? 'You are not eligible yet'
                                        : application
                                          ? 'An application already exists for this school year'
                                          : 'Submit application'
                            }
                        >
                            {needsResubmission ? <RefreshCcw className="mr-1 h-4 w-4" /> : <Plus className="mr-1 h-4 w-4" />}
                            {needsResubmission ? 'Resubmit corrections' : 'Submit application'}
                        </Button>
                    </div>
                    <div className="flex flex-col gap-4">
                        <CompreExamForm open={open} onOpenChange={setOpen} resubmission={needsResubmission ? application : null} />
                        <div>
                            {application ? (
                                <>
                                    <DisplayApplication
                                        application={{
                                            id: application.application_id,
                                            first_name: application.first_name,
                                            middle_initial: application.middle_name ? application.middle_name[0] : null,
                                            last_name: application.last_name,
                                            program: application.program,
                                            school_year: application.school_year,
                                            office_address: application.office_address ?? null,
                                            mobile_no: application.contact_number ?? null,
                                            telephone_no: application.telephone_number ?? null,
                                            email: application.email,
                                            status: application.workflow_status,
                                            subjects: application.subjects.map((subject) => ({
                                                subject: subject.subject,
                                                date: subject.date ?? '',
                                                startTime: subject.startTime ?? '',
                                                endTime: subject.endTime ?? '',
                                                room: subject.room,
                                                proctors: subject.proctors,
                                            })),
                                            created_at: application.created_at ?? null,
                                        }}
                                    />
                                    <section className="mx-5 mb-5 rounded-xl border p-5" aria-label="Application progress">
                                        <div className="flex flex-wrap items-start justify-between gap-3">
                                            <div>
                                                <h2 className="font-semibold">Application progress</h2>
                                                <p className="text-muted-foreground mt-1 text-xs">
                                                    Reference {application.application_no} · Submitted {formatDateTime(application.submitted_at)}
                                                </p>
                                            </div>
                                            <Badge variant={application.workflow_status === 'registrar_rejected' ? 'destructive' : 'outline'}>
                                                {application.workflow_status.replaceAll('_', ' ')}
                                            </Badge>
                                        </div>
                                        <ol className="mt-5 grid gap-3 sm:grid-cols-5">
                                            {workflowSteps.map((step, index) => {
                                                const current = workflowStepIndex(application.workflow_status);
                                                const rejected = ['aa_lacking', 'registrar_rejected', 'payment_rejected'].includes(
                                                    application.workflow_status,
                                                );
                                                const complete = index < current || (application.workflow_status === 'completed' && index === 4);
                                                const isCurrent = index === current && application.workflow_status !== 'completed';
                                                return (
                                                    <li className="flex items-start gap-2 text-sm" key={step}>
                                                        <span
                                                            className={`flex size-6 shrink-0 items-center justify-center rounded-full border text-xs ${
                                                                complete
                                                                    ? 'border-emerald-600 bg-emerald-600 text-white'
                                                                    : isCurrent && rejected
                                                                      ? 'border-destructive bg-destructive text-white'
                                                                      : isCurrent
                                                                        ? 'border-primary bg-primary text-primary-foreground'
                                                                        : 'text-muted-foreground'
                                                            }`}
                                                        >
                                                            {complete ? <Check className="size-3.5" /> : index + 1}
                                                        </span>
                                                        <span className={isCurrent ? 'font-medium' : 'text-muted-foreground'}>{step}</span>
                                                    </li>
                                                );
                                            })}
                                        </ol>
                                        {application.aa_remarks && <p className="mt-4 text-sm">AA note: {application.aa_remarks}</p>}
                                        {application.registrar_remarks && (
                                            <p className="mt-2 text-sm">Registrar note: {application.registrar_remarks}</p>
                                        )}
                                        {['registrar_approved', 'payment_rejected'].includes(application.workflow_status) && (
                                            <Button asChild size="sm" className="mt-4">
                                                <Link href="/payment">Continue to exam payment</Link>
                                            </Button>
                                        )}
                                        {application.events.length > 0 && (
                                            <details className="mt-4">
                                                <summary className="text-muted-foreground cursor-pointer text-sm">View application history</summary>
                                                <ul className="mt-2 space-y-2 border-l pl-4">
                                                    {application.events.map((event, index) => (
                                                        <li className="text-sm" key={`${event.action}-${index}`}>
                                                            <span className="font-medium">{event.action.replaceAll('_', ' ')}</span>
                                                            <span className="text-muted-foreground"> · {formatDateTime(event.created_at)}</span>
                                                            {event.actor && <p className="text-muted-foreground text-xs">By {event.actor}</p>}
                                                            {event.remarks && <p className="text-muted-foreground text-xs">{event.remarks}</p>}
                                                        </li>
                                                    ))}
                                                </ul>
                                            </details>
                                        )}
                                    </section>
                                </>
                            ) : (
                                <div className="text-muted-foreground p-6 text-center text-sm">No application submitted yet.</div>
                            )}
                        </div>
                    </div>
                    {/* Eligibility details dialog */}
                    <Dialog open={showEligDialog} onOpenChange={setShowEligDialog}>
                        <DialogContent className="sm:max-w-md">
                            <DialogHeader>
                                <DialogTitle>Comprehensive Exam Eligibility</DialogTitle>
                                <DialogDescription>You must meet all the requirements below before you can submit an application.</DialogDescription>
                            </DialogHeader>

                            <div className="space-y-2">
                                {blockers.map((b, i) => (
                                    <div key={i} className="flex items-center justify-between rounded border px-3 py-2">
                                        <span className="text-sm">{b.label}</span>
                                        <Badge
                                            variant="outline"
                                            className={
                                                b.ok === true
                                                    ? 'border-green-300 bg-green-50 text-green-700 dark:border-green-800 dark:bg-green-950/40 dark:text-green-300'
                                                    : b.ok === false
                                                      ? 'border-rose-300 bg-rose-50 text-rose-700 dark:border-rose-800 dark:bg-rose-950/40 dark:text-rose-300'
                                                      : 'text-muted-foreground'
                                            }
                                        >
                                            {b.ok === true ? 'OK' : b.ok === false ? 'Missing' : 'Unknown'}
                                        </Badge>
                                    </div>
                                ))}
                            </div>

                            <DialogFooter className="mt-2">
                                <Button variant="outline" onClick={() => setShowEligDialog(false)}>
                                    Close
                                </Button>
                                <Button
                                    onClick={() => {
                                        setShowEligDialog(false);
                                        if (canStartApplication) setOpen(true);
                                    }}
                                    disabled={!canStartApplication}
                                >
                                    {needsResubmission ? 'Resubmit corrections' : 'Apply Now'}
                                </Button>
                            </DialogFooter>
                        </DialogContent>
                    </Dialog>
                </div>
            </div>
        </AppLayout>
    );
}
