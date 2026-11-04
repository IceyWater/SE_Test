type HistoryDisplayEntry = {
    action?: string | null;
    event_type?: string | null;
    to_state?: string | null;
    comment?: string | null;
    student_response?: string | null;
    notes?: string | null;
    description?: string | null;
    details?: string | null;
    note?: string | null;
    remarks?: string | null;
};

function classificationDetails(entry: HistoryDisplayEntry): { current?: Record<string, unknown>; notes?: unknown } | null {
    const raw = entry.comment || entry.notes || entry.description || entry.details || '';
    try {
        const parsed: unknown = JSON.parse(raw);
        if (parsed && typeof parsed === 'object' && 'current' in parsed && 'previous' in parsed) {
            const current = (parsed as { current: unknown }).current;
            if (current && typeof current === 'object' && !Array.isArray(current)) {
                return parsed as { current: Record<string, unknown>; notes?: unknown };
            }
        }
    } catch {
        // Ordinary reviewer feedback is plain text.
    }
    return null;
}

export function defenseHistoryLabel(entry: HistoryDisplayEntry, fallback?: string): string {
    const action = entry.action || entry.event_type;
    if (action === 'panel-classified' || classificationDetails(entry)) return 'Panel classifications updated';
    if (action === 'service-confirmed') return 'Defense service confirmed';
    if (action === 'resubmitted') return 'Corrections resubmitted';
    if (fallback) return fallback;
    return (entry.to_state || action || 'Request updated').replace(/[-_]/g, ' ').replace(/^\w/, (letter) => letter.toUpperCase());
}

export function defenseHistoryDescription(entry: HistoryDisplayEntry): string {
    const raw = entry.comment || entry.student_response || entry.notes || entry.description || entry.details || entry.note || entry.remarks || '';
    const details = classificationDetails(entry);
    if (details) {
        const values = Object.values(details.current || {});
        const internal = values.filter((value) => value === 'internal').length;
        const external = values.filter((value) => value === 'external').length;
        const summary = `Panel classifications saved: ${internal} internal, ${external} external.`;
        return typeof details.notes === 'string' && details.notes.trim() ? `${summary}\n${details.notes.trim()}` : summary;
    }
    // Never expose malformed classification audit data as a student-facing message.
    if ((entry.action || entry.event_type) === 'panel-classified') return 'Panel classifications saved.';
    return raw;
}
