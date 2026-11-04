import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { useForm } from '@inertiajs/react';
import { useState } from 'react';

export type ExamResultRecord = { result: string; exam_date: string; record_reference: string; version: number; updated_at: string };

export default function ComprehensiveExamResult({
    studentId,
    record,
    canEdit,
}: {
    studentId: number;
    record: ExamResultRecord | null;
    canEdit: boolean;
}) {
    const [editing, setEditing] = useState(false);
    const form = useForm({
        result: record?.result ?? 'not-recorded',
        exam_date: record?.exam_date ?? '',
        record_reference: record?.record_reference ?? '',
        version: record?.version ?? 0,
        verified_against_school_records: false as boolean,
    });
    return (
        <section className="bg-card text-card-foreground space-y-3 rounded-xl border p-5">
            <h2 className="font-semibold">Comprehensive exam result</h2>
            <p className="capitalize">
                {record?.result.replaceAll('-', ' ') ?? 'Not recorded'}
                {record ? ` · Exam date: ${record.exam_date}` : ''}
            </p>
            <p className="text-muted-foreground text-sm">
                A verified Passed result for this program is required before requesting a defense. Application approval and payment verification do
                not mean the student passed the exam.
            </p>
            {record && <p className="text-sm">Record reference: {record.record_reference}</p>}
            {canEdit && (
                <Button variant="outline" onClick={() => setEditing(!editing)}>
                    {editing ? 'Cancel editing' : 'Record verified exam result'}
                </Button>
            )}
            {canEdit && editing && (
                <form
                    className="space-y-3"
                    onSubmit={(event) => {
                        event.preventDefault();
                        form.patch(`/academic-progress/${studentId}/comprehensive-result`, {
                            preserveScroll: true,
                            onSuccess: () => {
                                setEditing(false);
                                form.setData('verified_against_school_records', false);
                            },
                        });
                    }}
                >
                    <label className="block">
                        Result
                        <select
                            className="bg-background mt-1 block w-full rounded border p-2"
                            value={form.data.result}
                            onChange={(event) => form.setData('result', event.target.value)}
                        >
                            <option value="not-recorded">Not recorded</option>
                            <option value="passed">Passed</option>
                            <option value="failed">Failed</option>
                        </select>
                    </label>
                    <label className="block">
                        Actual exam date
                        <Input type="date" required value={form.data.exam_date} onChange={(event) => form.setData('exam_date', event.target.value)} />
                    </label>
                    <label className="block">
                        School record reference / reason for revision
                        <Input
                            required
                            minLength={5}
                            value={form.data.record_reference}
                            onChange={(event) => form.setData('record_reference', event.target.value)}
                        />
                    </label>
                    <label className="flex items-center gap-2">
                        <input
                            type="checkbox"
                            checked={form.data.verified_against_school_records}
                            onChange={(event) => form.setData('verified_against_school_records', event.target.checked)}
                        />
                        I verified this result against official school records.
                    </label>
                    {Object.entries(form.errors).map(([key, message]) => (
                        <p key={key} role="alert" className="text-destructive text-sm">
                            {message}
                        </p>
                    ))}
                    <Button disabled={form.processing || !form.data.verified_against_school_records}>Save exam result</Button>
                </form>
            )}
        </section>
    );
}
