export type DefenseEligibility = {
    comprehensive_passed: boolean;
    stages: Record<string, { allowed: boolean; reason: string | null }>;
};
