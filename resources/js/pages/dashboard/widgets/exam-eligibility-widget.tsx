import React from 'react';

const ExamEligibilityWidget: React.FC<{ simple?: boolean }> = ({ simple }) => {
    // Replace this with your real eligibility logic:
    const eligible = true; // or false

    if (simple) {
        return (
            <span className={`text-2xl font-bold ${eligible ? 'text-zinc-800 dark:text-zinc-100' : 'text-zinc-400 dark:text-zinc-500'}`}>
                {eligible ? 'Eligible' : 'Not Eligible'}
            </span>
        );
    }

    // (Optional: keep the full widget for other uses)
    return (
        <div className="border-sidebar-border/70 dark:border-sidebar-border flex aspect-video flex-col justify-between rounded-xl border bg-white p-5 dark:bg-zinc-900">
            <h3 className="mb-2 text-[13px] font-medium">Exam Eligibility</h3>
        </div>
    );
};

export default ExamEligibilityWidget;
