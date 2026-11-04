type ReportRecord = {
    amount: string;
    school_year: string;
    semester: string;
    status: string;
    requires_review: boolean;
    defense_request: { program: string };
};

export function summarizeHonorarium(records: ReportRecord[]) {
    return Object.values(
        records.reduce<
            Record<
                string,
                {
                    program: string;
                    year: string;
                    semester: string;
                    records: number;
                    prepared: number;
                    approved: number;
                    disbursed: number;
                    claimed: number;
                    review: number;
                    voided: number;
                }
            >
        >((groups, row) => {
            const key = JSON.stringify([row.defense_request.program, row.school_year, row.semester]);
            const group = (groups[key] ??= {
                program: row.defense_request.program || 'Not recorded',
                year: row.school_year,
                semester: row.semester,
                records: 0,
                prepared: 0,
                approved: 0,
                disbursed: 0,
                claimed: 0,
                review: 0,
                voided: 0,
            });
            group.records++;
            const bucket = row.status === 'voided' ? 'voided' : row.requires_review ? 'review' : row.status;
            if (['prepared', 'approved', 'disbursed', 'claimed', 'review', 'voided'].includes(bucket))
                group[bucket as 'prepared' | 'approved' | 'disbursed' | 'claimed' | 'review' | 'voided'] += Math.round(Number(row.amount) * 100);
            return groups;
        }, {}),
    );
}
