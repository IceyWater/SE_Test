import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/app-layout';
import { Head, useForm, usePage } from '@inertiajs/react';
import { CalendarClock, Plus } from 'lucide-react';
import type { FormEvent } from 'react';

type Room = { id: number; name: string; code: string; capacity: number };
type Proctor = { id: number; name: string };
type Schedule = {
    id: number;
    room_id: number;
    starts_at: string;
    ends_at: string;
    room: string | null;
    proctors: { id: number; user_id: number; name: string; status: string }[];
};
type Subject = { id: number; name: string; schedule: Schedule | null };
type Application = {
    id: number;
    application_no: string;
    student_id: string;
    program: string;
    school_year: string;
    status: string;
    subjects: Subject[];
};
type PageProps = { applications: Application[]; rooms: Room[]; proctors: Proctor[] };

function RoomForm() {
    const { data, setData, post, processing, errors, reset } = useForm({
        code: '',
        name: '',
        building: '',
        capacity: '30',
    });

    function submit(event: FormEvent) {
        event.preventDefault();
        post(route('coordinator.exam-schedules.rooms.store'), {
            preserveScroll: true,
            onSuccess: () => reset(),
        });
    }

    return (
        <form onSubmit={submit} className="grid gap-3 rounded-lg border p-4 md:grid-cols-5">
            <div>
                <Label htmlFor="room-code">Room code</Label>
                <Input id="room-code" value={data.code} onChange={(event) => setData('code', event.target.value)} required maxLength={30} />
                {errors.code && <p className="text-destructive text-xs">{errors.code}</p>}
            </div>
            <div>
                <Label htmlFor="room-name">Room name</Label>
                <Input id="room-name" value={data.name} onChange={(event) => setData('name', event.target.value)} required maxLength={120} />
                {errors.name && <p className="text-destructive text-xs">{errors.name}</p>}
            </div>
            <div>
                <Label htmlFor="room-building">Building</Label>
                <Input id="room-building" value={data.building} onChange={(event) => setData('building', event.target.value)} maxLength={120} />
            </div>
            <div>
                <Label htmlFor="room-capacity">Capacity</Label>
                <Input
                    id="room-capacity"
                    type="number"
                    min={1}
                    max={500}
                    value={data.capacity}
                    onChange={(event) => setData('capacity', event.target.value)}
                    required
                />
            </div>
            <div className="flex items-end">
                <Button type="submit" disabled={processing}>
                    <Plus className="mr-2 size-4" /> Add room
                </Button>
            </div>
        </form>
    );
}

function ScheduleForm({ application, subject, rooms, proctors }: { application: Application; subject: Subject; rooms: Room[]; proctors: Proctor[] }) {
    const { data, setData, post, processing, errors } = useForm<{
        room_id: string;
        starts_at: string;
        ends_at: string;
        proctor_ids: number[];
    }>({
        room_id: subject.schedule ? String(subject.schedule.room_id) : '',
        starts_at: subject.schedule?.starts_at ?? '',
        ends_at: subject.schedule?.ends_at ?? '',
        proctor_ids: subject.schedule?.proctors.map((proctor) => proctor.user_id) ?? [],
    });

    function submit(event: FormEvent) {
        event.preventDefault();
        post(route('coordinator.exam-schedules.assign', [application.id, subject.id]), {
            preserveScroll: true,
        });
    }

    return (
        <form onSubmit={submit} className="bg-muted/40 grid gap-3 rounded-md p-3 lg:grid-cols-5">
            <div className="font-medium lg:col-span-5">{subject.name}</div>
            <div>
                <Label>Room</Label>
                <select
                    className="border-input bg-background h-9 w-full rounded-md border px-3 text-sm"
                    value={data.room_id}
                    onChange={(event) => setData('room_id', event.target.value)}
                    required
                >
                    <option value="">Select room</option>
                    {rooms.map((room) => (
                        <option key={room.id} value={room.id}>
                            {room.code} · {room.name} (capacity {room.capacity})
                        </option>
                    ))}
                </select>
                {errors.room_id && <p className="text-destructive text-xs">{errors.room_id}</p>}
            </div>
            <div>
                <Label>Starts</Label>
                <Input type="datetime-local" value={data.starts_at} onChange={(event) => setData('starts_at', event.target.value)} required />
                {errors.starts_at && <p className="text-destructive text-xs">{errors.starts_at}</p>}
            </div>
            <div>
                <Label>Ends</Label>
                <Input type="datetime-local" value={data.ends_at} onChange={(event) => setData('ends_at', event.target.value)} required />
                {errors.ends_at && <p className="text-destructive text-xs">{errors.ends_at}</p>}
            </div>
            <fieldset className="lg:col-span-2">
                <legend className="mb-1 text-sm font-medium">Proctors (select 1–2)</legend>
                <div className="flex max-h-24 flex-wrap gap-x-4 gap-y-1 overflow-auto">
                    {proctors.map((proctor) => (
                        <label key={proctor.id} className="flex items-center gap-2 text-sm">
                            <input
                                type="checkbox"
                                checked={data.proctor_ids.includes(proctor.id)}
                                onChange={(event) =>
                                    setData(
                                        'proctor_ids',
                                        event.target.checked ? [...data.proctor_ids, proctor.id] : data.proctor_ids.filter((id) => id !== proctor.id),
                                    )
                                }
                            />
                            {proctor.name}
                        </label>
                    ))}
                </div>
                {errors.proctor_ids && <p className="text-destructive text-xs">{errors.proctor_ids}</p>}
            </fieldset>
            <div className="lg:col-span-5">
                <Button type="submit" size="sm" disabled={processing || rooms.length === 0 || proctors.length === 0}>
                    {subject.schedule ? 'Update schedule' : 'Publish schedule'}
                </Button>
            </div>
        </form>
    );
}

export default function Index() {
    const { applications, rooms, proctors } = usePage<PageProps>().props;

    return (
        <AppLayout>
            <Head title="Comprehensive Exam Scheduling" />
            <main className="space-y-5 p-6">
                <header className="flex items-center gap-3">
                    <CalendarClock className="text-primary size-7" />
                    <div>
                        <h1 className="text-xl font-semibold">Comprehensive Exam Scheduling</h1>
                        <p className="text-muted-foreground text-sm">
                            Schedule payment-verified applications and invite available Advisers to proctor.
                        </p>
                    </div>
                </header>

                <section className="space-y-3 rounded-lg border p-4">
                    <h2 className="font-semibold">Add an exam room</h2>
                    <RoomForm />
                    <p className="text-muted-foreground text-xs">{rooms.length} active rooms</p>
                </section>

                <section className="space-y-4">
                    <h2 className="font-semibold">Applications awaiting or receiving schedules</h2>
                    {applications.length === 0 ? (
                        <p className="text-muted-foreground rounded-lg border p-6 text-center text-sm">
                            No payment-verified applications are available for your programs.
                        </p>
                    ) : (
                        applications.map((application) => (
                            <article key={application.id} className="space-y-3 rounded-lg border p-4">
                                <header>
                                    <h3 className="font-medium">
                                        {application.application_no} · {application.student_id}
                                    </h3>
                                    <p className="text-muted-foreground text-sm">
                                        {application.program} · {application.school_year} · {application.status.replaceAll('_', ' ')}
                                    </p>
                                </header>
                                {application.subjects.map((subject) => (
                                    <ScheduleForm key={subject.id} application={application} subject={subject} rooms={rooms} proctors={proctors} />
                                ))}
                            </article>
                        ))
                    )}
                </section>
            </main>
        </AppLayout>
    );
}
