import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { useForm } from '@inertiajs/react';
import React, { useEffect, useState } from 'react';
import SuccessPrompt from './success-prompt';

interface Payment {
    id: number;
    school_year: string;
    payment_date: string;
    defense_status: string;
    amount: string;
}

export interface StudentRecord {
    id: number;
    first_name: string;
    middle_name: string | null;
    last_name: string;
    gender: string | null;
    school_year: string | null;
    student_id: string | null;
    course_section: string | null;
    birthdate: string | null;
    academic_status: string | null;
    program: string;
    or_number: string;
    payment_date: string;
    payments?: Payment[];
}

interface EditStudentModalProps {
    isOpen: boolean;
    onClose: () => void;
    record: StudentRecord;
}

const EditStudentModal: React.FC<EditStudentModalProps> = ({ isOpen, onClose, record }) => {
    const { data, setData, put, processing, errors, wasSuccessful, reset } = useForm({
        first_name: record.first_name,
        middle_name: record.middle_name ?? '',
        last_name: record.last_name,
        program: record.program,
        or_number: record.or_number ?? '',
        payment_date: record.payment_date?.slice(0, 10) ?? '',
    });

    const [showPrompt, setShowPrompt] = useState(false);

    useEffect(() => {
        reset();
        setData({
            first_name: record.first_name,
            middle_name: record.middle_name ?? '',
            last_name: record.last_name,
            program: record.program,
            or_number: record.or_number ?? '',
            payment_date: record.payment_date?.slice(0, 10) ?? '',
        });
    }, [record, reset, setData]);

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        put(route('student-records.update', record.id), {
            onSuccess: () => setShowPrompt(true),
        });
    };

    const closePrompt = () => {
        setShowPrompt(false);
        onClose();
    };

    if (!isOpen) return null;

    return (
        <>
            <Dialog
                open={isOpen && !showPrompt}
                onOpenChange={(open) => {
                    if (!open && !processing) onClose();
                }}
            >
                <DialogContent className="text-foreground max-h-[90vh] overflow-y-auto sm:max-w-lg">
                    <DialogHeader>
                        <DialogTitle>Edit Student Record</DialogTitle>
                        <DialogDescription>
                            Update the student�s details. Leave receipt information blank if no payment has been recorded.
                        </DialogDescription>
                    </DialogHeader>
                    <form onSubmit={handleSubmit} className="space-y-4">
                        {(
                            [
                                ['first_name', 'First name', 'text', true],
                                ['middle_name', 'Middle name (optional)', 'text', false],
                                ['last_name', 'Last name', 'text', true],
                                ['program', 'Program', 'text', true],
                                ['or_number', 'O.R. number (optional)', 'text', false],
                                ['payment_date', 'Payment date (optional)', 'date', false],
                            ] as const
                        ).map(([field, label, type, required]) => (
                            <div key={field} className="space-y-1.5">
                                <label htmlFor={`student-record-${field}`} className="text-sm font-medium">
                                    {label}
                                </label>
                                <Input
                                    id={`student-record-${field}`}
                                    name={field}
                                    type={type}
                                    required={required}
                                    value={data[field]}
                                    onChange={(event) => setData(field, event.target.value)}
                                    disabled={processing}
                                    aria-invalid={!!errors[field]}
                                    aria-describedby={errors[field] ? `student-record-${field}-error` : undefined}
                                    className="text-foreground [color-scheme:light] dark:[color-scheme:dark]"
                                />
                                {errors[field] && (
                                    <p id={`student-record-${field}-error`} role="alert" className="text-destructive text-sm">
                                        {errors[field]}
                                    </p>
                                )}
                            </div>
                        ))}
                        <div className="flex justify-end gap-2 border-t pt-4">
                            <Button type="button" variant="outline" onClick={onClose} disabled={processing}>
                                Cancel
                            </Button>
                            <Button type="submit" disabled={processing}>
                                {processing ? 'Saving�' : 'Save'}
                            </Button>
                        </div>
                    </form>
                </DialogContent>
            </Dialog>

            {/* Success Prompt */}
            {showPrompt && wasSuccessful && <SuccessPrompt message="Student record successfully updated." onClose={closePrompt} />}
        </>
    );
};

export default EditStudentModal;
