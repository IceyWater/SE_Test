import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/app-layout';
import { Head, router } from '@inertiajs/react';
import { useState } from 'react';

type Check = { key: string; status: string; remarks?: string | null };
type Application = {
    id: number;
    application_no: string;
    student_name: string;
    program: string;
    school_year: string;
    status: string;
    aa_remarks?: string | null;
    checks: Check[];
};

export default function RegistrarComprehensiveExam({ applications }: { applications: Application[] }) {
    const [reasons, setReasons] = useState<Record<number, string>>({});

    return (
        <AppLayout breadcrumbs={[{ title: 'Registrar Exam Verification', href: '/exam/registrar/reviews' }]}>
            <Head title="Registrar Exam Verification" />
            <main className="mx-auto flex w-full max-w-5xl flex-col gap-4 p-4 md:p-6">
                <header>
                    <h1 className="text-2xl font-semibold">Final Verification</h1>
                    <p className="text-muted-foreground text-sm">Approve evaluated applications before students can submit an exam payment.</p>
                </header>
                {applications.map((application) => (
                    <Card key={application.id}>
                        <CardHeader className="flex flex-row items-start justify-between gap-3">
                            <div>
                                <CardTitle>{application.student_name}</CardTitle>
                                <p className="text-muted-foreground mt-1 text-sm">
                                    {application.application_no} · {application.program} · {application.school_year}
                                </p>
                            </div>
                            <Badge variant="secondary">{application.status}</Badge>
                        </CardHeader>
                        <CardContent className="space-y-3">
                            <div className="grid gap-2 sm:grid-cols-2">
                                {application.checks.map((check) => (
                                    <div className="flex justify-between gap-2 rounded-md border p-2 text-sm" key={check.key}>
                                        <span className="capitalize">{check.key.replaceAll('_', ' ')}</span>
                                        <Badge variant="outline">{check.status}</Badge>
                                    </div>
                                ))}
                            </div>
                            {application.aa_remarks && <p className="text-muted-foreground text-sm">AA note: {application.aa_remarks}</p>}
                            {application.status === 'aa_evaluated' ? (
                                <>
                                    <label className="block space-y-1 text-sm font-medium">
                                        Rejection reason (required to reject)
                                        <textarea
                                            className="bg-background min-h-20 w-full rounded-md border p-3 font-normal"
                                            value={reasons[application.id] ?? ''}
                                            onChange={(event) => setReasons((current) => ({ ...current, [application.id]: event.target.value }))}
                                        />
                                    </label>
                                    <div className="flex flex-wrap gap-2">
                                        <Button onClick={() => router.post(`/exam/registrar/reviews/${application.id}/approve`, {}, { preserveScroll: true })}>
                                            Approve — Ready for Payment
                                        </Button>
                                        <Button
                                            variant="destructive"
                                            disabled={(reasons[application.id] ?? '').trim().length < 10}
                                            onClick={() =>
                                                router.post(
                                                    `/exam/registrar/reviews/${application.id}/reject`,
                                                    { reason: reasons[application.id] },
                                                    { preserveScroll: true },
                                                )
                                            }
                                        >
                                            Reject
                                        </Button>
                                    </div>
                                </>
                            ) : (
                                <p className="text-muted-foreground text-sm">This application has been processed and is shown for reference.</p>
                            )}
                        </CardContent>
                    </Card>
                ))}
                {applications.length === 0 && <p className="text-muted-foreground rounded-lg border p-8 text-center">No evaluated applications are waiting for review.</p>}
            </main>
        </AppLayout>
    );
}
