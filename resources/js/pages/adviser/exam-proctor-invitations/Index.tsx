import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/app-layout';
import { Head, useForm, usePage } from '@inertiajs/react';
import { format } from 'date-fns';

type Invitation = {
    id: number;
    status: string;
    decline_reason: string | null;
    application_no: string;
    subject: string;
    room: string | null;
    starts_at: string;
    ends_at: string;
};
type PageProps = { invitations: Invitation[] };

function InvitationActions({ invitation }: { invitation: Invitation }) {
    const { data, setData, post, processing, errors } = useForm<{ decision: 'accepted' | 'declined'; reason: string }>({
        decision: 'accepted',
        reason: '',
    });

    function respond(event: React.FormEvent) {
        event.preventDefault();
        post(route('exam.proctor-invitations.respond', invitation.id), { preserveScroll: true });
    }

    return (
        <form onSubmit={respond} className="flex flex-col gap-2 sm:flex-row sm:items-end">
            <div>
                <Label htmlFor={`decision-${invitation.id}`}>Response</Label>
                <select
                    id={`decision-${invitation.id}`}
                    className="border-input bg-background h-10 rounded-md border px-3 text-sm"
                    value={data.decision}
                    onChange={(event) => setData('decision', event.target.value as 'accepted' | 'declined')}
                >
                    <option value="accepted">Accept</option>
                    <option value="declined">Decline</option>
                </select>
            </div>
            <Textarea
                value={data.reason}
                onChange={(event) => setData('reason', event.target.value)}
                placeholder="Reason required if declining"
                maxLength={500}
                className="sm:max-w-sm"
            />
            {errors.reason && <p className="text-destructive text-xs">{errors.reason}</p>}
            <Button
                type="submit"
                variant={data.decision === 'declined' ? 'outline' : 'default'}
                disabled={processing || (data.decision === 'declined' && data.reason.trim().length < 5)}
            >
                {processing ? 'Saving…' : `Submit ${data.decision}`}
            </Button>
        </form>
    );
}

export default function Index() {
    const { invitations } = usePage<PageProps>().props;
    return (
        <AppLayout>
            <Head title="Exam Proctor Invitations" />
            <main className="space-y-4 p-6">
                <h1 className="text-xl font-semibold">Exam Proctor Invitations</h1>
                {invitations.length === 0 ? (
                    <p className="text-muted-foreground rounded-lg border p-6 text-center text-sm">No proctor invitations.</p>
                ) : (
                    invitations.map((invitation) => (
                        <article key={invitation.id} className="space-y-3 rounded-lg border p-4">
                            <h2 className="font-medium">
                                {invitation.application_no} · {invitation.subject}
                            </h2>
                            <p className="text-muted-foreground text-sm">
                                {format(new Date(invitation.starts_at), 'PPp')} – {format(new Date(invitation.ends_at), 'p')}
                                {invitation.room ? ` · ${invitation.room}` : ''}
                            </p>
                            <p className="text-sm">Status: {invitation.status}</p>
                            {invitation.decline_reason && <p className="text-sm">Reason: {invitation.decline_reason}</p>}
                            {invitation.status === 'invited' && <InvitationActions invitation={invitation} />}
                        </article>
                    ))
                )}
            </main>
        </AppLayout>
    );
}
