import HeadingSmall from '@/components/heading-small';
import {
    AlertDialog,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
} from '@/components/ui/alert-dialog';
import { Button } from '@/components/ui/button';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import AppLayout from '@/layouts/app-layout';
import SettingsLayout from '@/layouts/settings/layout';
import { csrfHeaders } from '@/lib/csrf';
import { Head } from '@inertiajs/react';
import React, { useEffect, useRef, useState } from 'react';
import { toast } from 'sonner';

type BreadcrumbItem = { title: string; href: string };
type Template = { id: number; name: string; code: string; version: number; file_type: string; download_url: string };

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Document Templates', href: '/settings/documents' }];

const templateOptions = [
    { label: 'Endorsement Form (Proposal)', value: 'Endorsement Form (Proposal)' },
    { label: 'Endorsement Form (Prefinal)', value: 'Endorsement Form (Prefinal)' },
    { label: 'Endorsement (Final)', value: 'Endorsement (Final)' },
];

export default function DocumentTemplatesIndex() {
    const [list, setList] = useState<Template[]>([]);
    const fileInput = useRef<HTMLInputElement>(null);
    const [file, setFile] = useState<File | null>(null);
    const [form, setForm] = useState({ name: '' });
    const [busy, setBusy] = useState(false);
    const [uploadMessage, setUploadMessage] = useState('');
    const [deleteTarget, setDeleteTarget] = useState<Template | null>(null);
    const [deleting, setDeleting] = useState(false);
    const [deleteError, setDeleteError] = useState('');

    async function removeTemplate() {
        if (!deleteTarget || deleting) return;
        setDeleting(true);
        setDeleteError('');
        try {
            const response = await fetch(`/api/document-templates/${deleteTarget.id}`, {
                method: 'DELETE',
                headers: csrfHeaders(),
                credentials: 'include',
            });
            const result = await response.json().catch(() => ({}));
            if (!response.ok) throw new Error(result.message || 'Unable to delete this template. Please try again.');
            setList((items) => items.filter((item) => item.id !== deleteTarget.id));
            setDeleteTarget(null);
            toast.success('Template deleted.');
        } catch (error) {
            setDeleteError(error instanceof Error ? error.message : 'Connection interrupted. Please try again.');
        } finally {
            setDeleting(false);
        }
    }

    async function load() {
        const r = await fetch('/api/document-templates', {
            headers: csrfHeaders(),
            credentials: 'include',
        });
        if (r.ok) setList(await r.json());
    }

    useEffect(() => {
        load();
    }, []);

    async function submit(e: React.FormEvent) {
        e.preventDefault();
        if (!file) return;
        setBusy(true);
        setUploadMessage('');
        try {
            const fd = new FormData();
            fd.append('name', form.name);
            fd.append('file', file);
            const response = await fetch('/api/document-templates', { method: 'POST', body: fd, headers: csrfHeaders(), credentials: 'include' });
            const data = await response.json().catch(() => ({}));
            if (!response.ok) throw new Error(data.errors?.file?.[0] || data.message || 'Unable to upload template. Please try again.');
            setList((items) => [data as Template, ...items]);
            setForm({ name: '' });
            setFile(null);
            if (fileInput.current) fileInput.current.value = '';
            setUploadMessage('Template saved. Students can download it from My Documents.');
            toast.success('Template uploaded.');
        } catch (error) {
            setUploadMessage(error instanceof Error ? error.message : 'Connection interrupted. Please try again.');
        } finally {
            setBusy(false);
        }
    }

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Document Templates" />
            <SettingsLayout>
                <div className="space-y-10">
                    <div className="space-y-6">
                        <HeadingSmall
                            title="Upload template"
                            description="Upload a PDF or Word form for students to download. PDFs also support field and signature mapping."
                        />
                        <form onSubmit={submit} className="mt-2 grid max-w-sm gap-3">
                            <Select value={form.name} onValueChange={(value) => setForm((f) => ({ ...f, name: value }))}>
                                <SelectTrigger className="rounded border px-2 py-1">
                                    <SelectValue placeholder="Select template name" />
                                </SelectTrigger>
                                <SelectContent>
                                    {templateOptions.map((opt) => (
                                        <SelectItem key={opt.value} value={opt.value}>
                                            {opt.label}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                            <div className="space-y-2">
                                <input
                                    ref={fileInput}
                                    type="file"
                                    accept=".pdf,.doc,.docx,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document"
                                    aria-label="PDF or Word template file"
                                    className="hidden"
                                    disabled={busy}
                                    onChange={(event) => setFile(event.target.files?.[0] ?? null)}
                                />
                                <Button
                                    type="button"
                                    variant="outline"
                                    className="w-full"
                                    disabled={busy}
                                    aria-describedby="template-file-name template-file-help"
                                    onClick={() => fileInput.current?.click()}
                                >
                                    Add File
                                </Button>
                                <p id="template-file-name" role="status" className="text-muted-foreground text-sm break-all">
                                    {file?.name ?? 'No file chosen'}
                                </p>
                                <p id="template-file-help" className="text-muted-foreground text-xs">
                                    PDF or Word (.doc, .docx), up to 10 MB.
                                </p>
                            </div>
                            <Button disabled={!file || !form.name || busy} type="submit">
                                {busy ? 'Uploading...' : 'Upload'}
                            </Button>
                            {uploadMessage && (
                                <p role="status" className="text-sm">
                                    {uploadMessage}
                                </p>
                            )}
                        </form>
                    </div>

                    <div className="space-y-4">
                        <HeadingSmall title="Existing templates" description="Manage and edit mapped fields." />
                        <div className="overflow-x-auto">
                            <table className="w-full border text-sm">
                                <thead className="bg-muted text-foreground">
                                    <tr>
                                        <th className="p-2 text-left">Name</th>
                                        <th className="p-2">Code</th>
                                        <th className="p-2">Version</th>
                                        <th className="p-2">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {list.map((t) => (
                                        <tr key={t.id} className="border-t">
                                            <td className="p-2">
                                                {t.name} <span className="text-muted-foreground text-xs uppercase">({t.file_type})</span>
                                            </td>
                                            <td className="p-2">{t.code}</td>
                                            <td className="p-2 text-center">{t.version}</td>
                                            <td className="p-2">
                                                <div className="flex items-center gap-3">
                                                    <a href={t.download_url} className="underline">
                                                        Download
                                                    </a>
                                                    {t.file_type === 'pdf' ? (
                                                        <a
                                                            href={`/settings/documents/${t.id}/edit`}
                                                            target="_blank"
                                                            rel="noopener noreferrer"
                                                            className="underline"
                                                        >
                                                            Fields
                                                        </a>
                                                    ) : (
                                                        <span className="text-muted-foreground text-xs">Edit in Word / WPS</span>
                                                    )}
                                                    <Button
                                                        variant="destructive"
                                                        size="sm"
                                                        onClick={() => {
                                                            setDeleteError('');
                                                            setDeleteTarget(t);
                                                        }}
                                                        aria-label={`Delete ${t.name}`}
                                                    >
                                                        Delete
                                                    </Button>
                                                </div>
                                            </td>
                                        </tr>
                                    ))}
                                    {!list.length && (
                                        <tr>
                                            <td className="p-4" colSpan={4}>
                                                No templates.
                                            </td>
                                        </tr>
                                    )}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <AlertDialog
                    open={!!deleteTarget}
                    onOpenChange={(open) => {
                        if (!open && !deleting) setDeleteTarget(null);
                    }}
                >
                    <AlertDialogContent>
                        <AlertDialogHeader>
                            <AlertDialogTitle>Delete template?</AlertDialogTitle>
                            <AlertDialogDescription>
                                This removes �{deleteTarget?.name}�, its uploaded PDF, and its saved field mappings. This cannot be undone.
                            </AlertDialogDescription>
                        </AlertDialogHeader>
                        {deleteError && (
                            <p role="alert" className="text-destructive text-sm">
                                {deleteError}
                            </p>
                        )}
                        <AlertDialogFooter>
                            <AlertDialogCancel disabled={deleting}>Cancel</AlertDialogCancel>
                            <Button variant="destructive" disabled={deleting} onClick={removeTemplate}>
                                {deleting ? 'Deleting�' : 'Delete template'}
                            </Button>
                        </AlertDialogFooter>
                    </AlertDialogContent>
                </AlertDialog>
            </SettingsLayout>
        </AppLayout>
    );
}
