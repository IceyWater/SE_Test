import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/app-layout';
import { Head, router } from '@inertiajs/react';
import { useState } from 'react';

type Requirement = { key: string; label: string };
type Check = { key: string; status: string; remarks?: string | null };
type Application = {
    id: number;
    application_no: string;
    student_name: string;
    student_email?: string | null;
    program: string;
    school_year: string;
    status: string;
    submitted_at?: string | null;
    aa_remarks?: string | null;
    checks: Check[];
};

const dateTime = (value?: string | null) =>
    value ? new Intl.DateTimeFormat('en-PH', { dateStyle: 'medium', timeStyle: 'short' }).format(new Date(value)) : '—';

export default function AaComprehensiveExam({ applications, requirements }: { applications: Application[]; requirements: Requirement[] }) {
    const [remarks, setRemarks] = useState<Record<number, string>>({});

    return (
        <AppLayout breadcrumbs={[{ title: 'Comprehensive Exam Review', href: '/exam/aa/reviews' }]}>
            <Head title="Comprehensive Exam Review" />
            <main className="mx-auto flex w-full max-w-6xl flex-col gap-4 p-4 md:p-6">
                <header>
                    <h1 className="text-2xl font-semibold">Comprehensive Exam Review</h1>
                    <p className="text-muted-foreground text-sm">Verify each requirement before forwarding an application to the Registrar.</p>
                </header>
                {applications.map((application) => (
                    <Card key={application.id}>
                        <CardHeader className="flex flex-row items-start justify-between gap-3">
                            <div>
                                <CardTitle>{application.student_name}</CardTitle>
                                <p className="text-muted-foreground mt-1 text-sm">
                                    {application.application_no} · {application.program} · {application.school_year}
                                </p>
                                <p className="text-muted-foreground mt-1 text-xs">Submitted {dateTime(application.submitted_at)}</p>
                            </div>
                            <Badge variant={application.status === 'aa_lacking' ? 'destructive' : 'secondary'}>{application.status}</Badge>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="grid gap-3 md:grid-cols-2">
                                {requirements.map((requirement) => {
                                    const check = application.checks.find((entry) => entry.key === requirement.key);
                                    return (
                                        <div className="rounded-lg border p-3" key={requirement.key}>
                                            <div className="mb-2 flex items-center justify-between gap-2">
                                                <span className="text-sm font-medium">{requirement.label}</span>
                                                <Badge variant="outline">{check?.status ?? 'pending'}</Badge>
                                            </div>
                                            <input
                                                aria-label={`${requirement.label} remarks`}
                                                className="bg-background h-9 w-full rounded-md border px-3 text-sm"
                                                defaultValue={check?.remarks ?? ''}
                                                placeholder="Optional note"
                                                onBlur={(event) => {
                                                    const status = check?.status ?? 'pending';
                                                    if (!event.target.value && status !== 'lacking') return;
                                                    router.post(
                                                        `/exam/aa/reviews/${application.id}/requirements`,
                                                        { requirement_key: requirement.key, status, remarks: event.target.value },
                                                        { preserveScroll: true },
                                                    );
                                                }}
                                            />
                                            <div className="mt-2 flex gap-2">
                                                {(['passed', 'lacking', 'waived'] as const).map((status) => (
                                                    <Button
                                                        key={status}
                                                        size="sm"
                                                        variant={check?.status === status ? 'default' : 'outline'}
                                                        onClick={() =>
                                                            router.post(
                                                                `/exam/aa/reviews/${application.id}/requirements`,
                                                                { requirement_key: requirement.key, status, remarks: check?.remarks ?? '' },
                                                                { preserveScroll: true },
                                                            )
                                                        }
                                                    >
                                                        {status}
                                                    </Button>
                                                ))}
                                            </div>
                                        </div>
                                    );
                                })}
                            </div>
                            <label className="block space-y-1 text-sm font-medium">
                                Student-facing remarks (required for Lacking)
                                <textarea
                                    className="bg-background min-h-20 w-full rounded-md border p-3 font-normal"
                                    value={remarks[application.id] ?? application.aa_remarks ?? ''}
                                    onChange={(event) => setRemarks((current) => ({ ...current, [application.id]: event.target.value }))}
                                />
                            </label>
                            <div className="flex flex-wrap gap-2">
                                <Button
                                    variant="destructive"
                                    disabled={(remarks[application.id] ?? application.aa_remarks ?? '').trim().length < 10}
                                    onClick={() =>
                                        router.post(
                                            `/exam/aa/reviews/${application.id}/lacking`,
                                            { remarks: remarks[application.id] ?? application.aa_remarks },
                                            { preserveScroll: true },
                                        )
                                    }
                                >
                                    Mark Lacking
                                </Button>
                                <Button
                                    disabled={application.checks.length < requirements.length || application.checks.some((check) => !['passed', 'waived'].includes(check.status))}
                                    onClick={() => router.post(`/exam/aa/reviews/${application.id}/evaluated`, {}, { preserveScroll: true })}
                                >
                                    Mark Evaluated
                                </Button>
                            </div>
                        </CardContent>
                    </Card>
                ))}
                {applications.length === 0 && <p className="text-muted-foreground rounded-lg border p-8 text-center">No applications need AA review.</p>}
            </main>
        </AppLayout>
    );
}
