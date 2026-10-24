import { Badge } from '@/components/ui/badge';
import { CheckCircle2, ClipboardList, GraduationCap, Mail, User2, XCircle } from 'lucide-react';
import type { CompreExamApplicationSummary } from './Index';

export default function Details({ application }: { application: CompreExamApplicationSummary }) {
    const elig = application.eligible;

    const statusBadge = (v: CompreExamApplicationSummary['application_status']) => {
        const s = v || 'not_yet_applied';
        const label = s === 'not_yet_applied' ? 'Not yet applied' : s[0].toUpperCase() + s.slice(1);
        const cls =
            s === 'approved'
                ? 'bg-emerald-100 text-emerald-700 border-emerald-200 dark:bg-emerald-950 dark:text-emerald-300 dark:border-emerald-900'
                : s === 'rejected'
                  ? 'bg-rose-100 text-rose-700 border-rose-200 dark:bg-rose-950 dark:text-rose-300 dark:border-rose-900'
                  : s === 'pending'
                    ? 'bg-amber-100 text-amber-700 border-amber-200 dark:bg-amber-950 dark:text-amber-300 dark:border-amber-900'
                    : 'bg-zinc-100 text-zinc-700 border-zinc-200 dark:bg-zinc-900 dark:text-zinc-300 dark:border-zinc-800';
        return (
            <Badge variant="outline" className={`rounded-full ${cls}`}>
                {label}
            </Badge>
        );
    };

    const permitBadge = (v: CompreExamApplicationSummary['permit_status']) => {
        if (!v)
            return (
                <Badge variant="outline" className="rounded-full">
                    —
                </Badge>
            );
        const s = v;
        const cls =
            s === 'approved'
                ? 'bg-emerald-100 text-emerald-700 border-emerald-200 dark:bg-emerald-950 dark:text-emerald-300 dark:border-emerald-900'
                : s === 'rejected'
                  ? 'bg-rose-100 text-rose-700 border-rose-200 dark:bg-rose-950 dark:text-rose-300 dark:border-rose-900'
                  : 'bg-amber-100 text-amber-700 border-amber-200 dark:bg-amber-950 dark:text-amber-300 dark:border-amber-900';
        return (
            <Badge variant="outline" className={`rounded-full ${cls}`}>
                {s[0].toUpperCase() + s.slice(1)}
            </Badge>
        );
    };

    return (
        <div className="p-3 text-sm">
            {/* Header */}
            <div className="mb-3 flex items-center gap-2">
                <div className="bg-primary/10 text-primary inline-flex h-8 w-8 items-center justify-center rounded-md">
                    <ClipboardList className="h-5 w-5" />
                </div>
                <h2 className="text-lg font-semibold tracking-tight">Details</h2>
            </div>

            {/* Top summary */}
            <div className="dark:bg-background rounded-lg border bg-white/60 p-3">
                <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <div className="flex items-center gap-2 text-[15px] font-medium">
                            <User2 className="text-muted-foreground h-4 w-4" />
                            <span className="text-base font-semibold md:text-[17px]">
                                {application.last_name}, {application.first_name} {application.middle_name ? `${application.middle_name[0]}.` : ''}
                            </span>
                        </div>
                        <div className="text-muted-foreground mt-1 flex flex-wrap items-center gap-3 text-[13px]">
                            <span className="inline-flex items-center gap-1">
                                <Mail className="h-3.5 w-3.5" />
                                {application.email || '—'}
                            </span>
                            <span className="inline-flex items-center gap-1">
                                ID:
                                <span className="text-foreground font-medium">{application.school_id || '—'}</span>
                            </span>
                        </div>
                    </div>

                    <div>
                        <div className="flex items-center gap-2 text-[15px]">
                            <GraduationCap className="text-muted-foreground h-4 w-4" />
                            <span className="text-foreground">{application.program || '—'}</span>
                        </div>

                        <div className="mt-2 flex flex-wrap items-center gap-2">
                            {/* Eligibility */}
                            <Badge
                                variant="outline"
                                className={
                                    'inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 ' +
                                    (elig
                                        ? 'border-emerald-200 bg-emerald-100 text-emerald-700 dark:border-emerald-900 dark:bg-emerald-950 dark:text-emerald-300'
                                        : 'border-amber-200 bg-amber-100 text-amber-700 dark:border-amber-900 dark:bg-amber-950 dark:text-amber-300')
                                }
                            >
                                {elig ? <CheckCircle2 className="h-3.5 w-3.5" /> : <XCircle className="h-3.5 w-3.5" />}
                                {elig ? 'Eligible' : 'Not eligible'}
                            </Badge>

                            {/* Applied */}
                            <Badge variant="outline" className="rounded-full px-2.5 py-1">
                                {application.applied ? 'Applied' : 'Not applied'}
                            </Badge>

                            {/* Application Status */}
                            {statusBadge(application.application_status)}

                            {/* Permit Status */}
                            {permitBadge(application.permit_status)}
                        </div>
                    </div>
                </div>
            </div>

            {/* Lacking requirements */}
            <div className="mt-4">
                <div className="text-muted-foreground mb-2 text-xs tracking-wide uppercase">Lacking requirements</div>
                {application.eligible || application.lacking.length === 0 ? (
                    <div className="text-foreground text-[13px]">None</div>
                ) : (
                    <div className="flex flex-wrap gap-2">
                        {application.lacking.map((x, i) => (
                            <Badge
                                key={i}
                                variant="outline"
                                className="rounded-full border-rose-200 bg-rose-50 text-rose-700 dark:border-rose-900 dark:bg-rose-950/40 dark:text-rose-300"
                            >
                                <XCircle className="mr-1 h-3.5 w-3.5" />
                                {x}
                            </Badge>
                        ))}
                    </div>
                )}
            </div>
        </div>
    );
}
